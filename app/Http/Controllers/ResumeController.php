<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Lead;
use App\Models\Resume;
use App\Models\SkillAnalysis;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResumeController extends Controller
{
    public function __construct(
        protected ResumeAnalysisService $resumeAnalysis
    ) {}

    public function showUploadForm(): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login', ['redirect' => url('/resume/upload')]);
        }
        if (! auth()->user()->isCandidate()) {
            return redirect()->route('home')->with('info', 'Resume upload is for candidates.');
        }
        return view('hirevo.resume-upload');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'resume.required' => 'Please select a PDF file to upload.',
            'resume.mimes' => 'Only PDF files are supported.',
            'resume.max' => 'The file must not exceed 10 MB.',
        ]);

        $user = auth()->user();
        if (! $user->isCandidate()) {
            return redirect()->route('home');
        }

        $file = $request->file('resume');
        $path = $file->store('resumes', 'local');
        if ($path === false) {
            return back()->withErrors(['resume' => 'Failed to store file.'])->withInput();
        }

        $user->resumes()->update(['is_primary' => false]);

        $resume = $user->resumes()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'is_primary' => true,
        ]);

        $this->resumeAnalysis->analyzeResume($resume);

        return redirect()->route('resume.results', $resume);
    }

    public function results(Resume $resume): View|RedirectResponse
    {
        if ($resume->user_id !== auth()->id()) {
            abort(403);
        }

        $recommended = $this->getRecommendedJobGoals($resume);
        $appliedJobIds = JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all();

        return view('hirevo.resume-results', [
            'resume' => $resume,
            'recommendedJobGoals' => $recommended,
            'appliedJobIds' => $appliedJobIds,
        ]);
    }

    /**
     * Create lead (get help to learn) for a job role from results page.
     */
    public function createLead(Request $request): RedirectResponse
    {
        $request->validate([
            'resume_id' => ['required', 'integer'],
            'job_role_id' => ['required', 'integer', 'exists:job_roles,id'],
        ]);

        $resume = Resume::where('user_id', auth()->id())->findOrFail($request->resume_id);
        $jobRole = JobRole::with('requiredSkills')->findOrFail($request->job_role_id);

        $extracted = $resume->getExtractedSkillsList();
        $required = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();
        $matched = array_values(array_intersect($required, $extracted));
        $missing = array_values(array_diff($required, $extracted));
        $matchPercentage = count($required) > 0
            ? (int) round((count($matched) / count($required)) * 100)
            : 0;

        $skillAnalysis = SkillAnalysis::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'job_role_id' => $jobRole->id,
            ],
            [
                'resume_id' => $resume->id,
                'match_percentage' => $matchPercentage,
                'matched_skills' => $matched,
                'missing_skills' => $missing,
            ]
        );

        Lead::firstOrCreate(
            [
                'candidate_id' => auth()->id(),
                'skill_analysis_id' => $skillAnalysis->id,
            ],
            [
                'job_role_id' => $jobRole->id,
                'match_percentage' => $matchPercentage,
                'missing_skills' => $missing,
                'status' => 'available',
            ]
        );

        return redirect()->route('resume.results', $resume)
            ->with('success', 'You have opted in for learning help. EdTech partners can now bid for your lead.');
    }

    protected function getRecommendedJobGoals(Resume $resume): array
    {
        $extracted = $resume->getExtractedSkillsList();
        $roles = JobRole::where('is_active', true)->with('requiredSkills')->get();
        $scored = [];
        foreach ($roles as $role) {
            $required = $role->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();
            if (count($required) === 0) {
                $scored[] = ['job_role' => $role, 'match_percentage' => 0, 'missing_skills' => []];
                continue;
            }
            $matched = array_values(array_intersect($required, $extracted));
            $missing = array_values(array_diff($required, $extracted));
            $matchPercentage = (int) round((count($matched) / count($required)) * 100);
            $scored[] = [
                'job_role' => $role,
                'match_percentage' => $matchPercentage,
                'missing_skills' => $missing,
            ];
        }
        usort($scored, fn ($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);
        return array_slice($scored, 0, 8);
    }
}
