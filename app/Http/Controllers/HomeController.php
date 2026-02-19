<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Resume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $jobRoles = JobRole::where('is_active', true)->orderBy('title')->limit(8)->get();
        return view('hirevo.index', compact('jobRoles'));
    }

    public function jobList(): View
    {
        $jobRoles = JobRole::where('is_active', true)->orderBy('title')->get();
        $appliedJobIds = auth()->check()
            ? \App\Models\JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all()
            : [];
        return view('hirevo.job-list', compact('jobRoles', 'appliedJobIds'));
    }

    public function skillMatch(JobRole $jobRole): View
    {
        $jobRole->load('requiredSkills');
        $requiredSkills = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();

        $matchPercentage = 0;
        $matchedSkills = [];
        $missingSkills = $requiredSkills;
        $candidateSkills = [];

        if (auth()->check() && auth()->user()->isCandidate()) {
            $profile = auth()->user()->candidateProfile;
            if ($profile && ! empty($profile->skills)) {
                $candidateSkills = array_map(function ($s) {
                    return strtolower(trim($s));
                }, preg_split('/[\s,;|]+/', $profile->skills, -1, PREG_SPLIT_NO_EMPTY));
                $candidateSkills = array_unique($candidateSkills);

                if (count($requiredSkills) > 0) {
                    $matchedSkills = array_values(array_intersect($requiredSkills, $candidateSkills));
                    $missingSkills = array_values(array_diff($requiredSkills, $candidateSkills));
                    $matchPercentage = (int) round((count($matchedSkills) / count($requiredSkills)) * 100);
                }
            } else {
                $missingSkills = $requiredSkills;
            }
        }

        $hasApplied = auth()->check()
            ? JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->exists()
            : false;

        return view('hirevo.skill-match', [
            'jobRole' => $jobRole,
            'requiredSkills' => $jobRole->requiredSkills,
            'matchPercentage' => $matchPercentage,
            'matchedSkills' => $matchedSkills,
            'missingSkills' => $missingSkills,
            'candidateSkills' => $candidateSkills,
            'hasProfile' => auth()->check() && auth()->user()->candidateProfile,
            'hasApplied' => $hasApplied,
        ]);
    }

    public function pricing(): View
    {
        return view('hirevo.pricing');
    }

    public function jobOpenings(): View
    {
        $jobs = EmployerJob::where('status', 'active')
            ->with(['user.referrerProfile'])
            ->orderByDesc('created_at')
            ->paginate(12);
        $appliedIds = auth()->check()
            ? EmployerJobApplication::where('user_id', auth()->id())->pluck('employer_job_id')->all()
            : [];
        return view('hirevo.job-openings', compact('jobs', 'appliedIds'));
    }

    public function showEmployerJobApply(EmployerJob $job): View|RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }
        if (auth()->check() && ! auth()->user()->isCandidate()) {
            return redirect()->route('job-openings')->with('info', 'Only candidates can apply.');
        }
        if (auth()->check()) {
            $exists = EmployerJobApplication::where('employer_job_id', $job->id)->where('user_id', auth()->id())->exists();
            if ($exists) {
                return redirect()->route('job-openings')->with('info', 'You have already applied for this job.');
            }
        } else {
            return redirect()->route('login', ['redirect' => route('job-openings.apply', $job)]);
        }
        $job->load('user.referrerProfile');
        $user = auth()->user();
        $resumes = $user->resumes()->orderByDesc('created_at')->get();
        $profile = $user->candidateProfile;
        return view('hirevo.job-openings-apply', compact('job', 'resumes', 'profile'));
    }

    public function storeEmployerJobApply(Request $request, EmployerJob $job): RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }
        if (! auth()->user()->isCandidate()) {
            return redirect()->route('job-openings');
        }
        $exists = EmployerJobApplication::where('employer_job_id', $job->id)->where('user_id', auth()->id())->exists();
        if ($exists) {
            return redirect()->route('job-openings')->with('info', 'You have already applied for this job.');
        }
        $request->validate([
            'resume_id'       => ['nullable', 'integer', 'exists:resumes,id'],
            'cover_message'  => ['nullable', 'string', 'max:2000'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'headline'        => ['nullable', 'string', 'max:255'],
            'education'       => ['nullable', 'string', 'max:500'],
            'experience_years'=> ['nullable', 'integer', 'min:0', 'max:50'],
            'skills'          => ['nullable', 'string', 'max:2000'],
            'location'        => ['nullable', 'string', 'max:255'],
            'expected_salary' => ['nullable', 'string', 'max:100'],
        ]);
        $user = auth()->user();
        if ($request->resume_id) {
            $resume = Resume::where('user_id', $user->id)->find($request->resume_id);
            if (! $resume) {
                return back()->withErrors(['resume_id' => 'Invalid resume.']);
            }
        }
        if ($request->filled('phone')) {
            $user->update(['phone' => $request->phone]);
        }
        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        $profile->headline = $request->filled('headline') ? $request->headline : $profile->headline;
        $profile->education = $request->filled('education') ? $request->education : $profile->education;
        $profile->experience_years = $request->filled('experience_years') ? (int) $request->experience_years : $profile->experience_years;
        $profile->skills = $request->filled('skills') ? $request->skills : $profile->skills;
        $profile->location = $request->filled('location') ? $request->location : $profile->location;
        $profile->expected_salary = $request->filled('expected_salary') ? $request->expected_salary : $profile->expected_salary;
        $profile->save();

        EmployerJobApplication::create([
            'employer_job_id' => $job->id,
            'user_id'         => $user->id,
            'resume_id'       => $request->resume_id ?: null,
            'cover_message'   => $request->cover_message ? trim($request->cover_message) : null,
            'status'          => 'applied',
        ]);
        return redirect()->route('job-openings')->with('success', 'Your application has been submitted.');
    }
}
