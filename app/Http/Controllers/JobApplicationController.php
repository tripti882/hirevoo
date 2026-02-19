<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Resume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function showApplyForm(JobRole $jobRole): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login', ['redirect' => route('job-goal.apply', $jobRole)]);
        }
        if (! auth()->user()->isCandidate()) {
            return redirect()->route('job-goal.show', $jobRole)->with('info', 'Only candidates can apply.');
        }

        $existing = JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->first();
        if ($existing) {
            return redirect()->route('job-goal.show', $jobRole)->with('info', 'You have already applied for this role.');
        }

        $resumes = auth()->user()->resumes()->orderByDesc('created_at')->get();

        return view('hirevo.job-apply', [
            'jobRole' => $jobRole,
            'resumes' => $resumes,
        ]);
    }

    public function store(Request $request, JobRole $jobRole): RedirectResponse
    {
        $request->validate([
            'resume_id' => ['nullable', 'integer', 'exists:resumes,id'],
            'cover_message' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! auth()->user()->isCandidate()) {
            return redirect()->route('job-goal.show', $jobRole);
        }

        $existing = JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->first();
        if ($existing) {
            return redirect()->route('job-goal.show', $jobRole)->with('info', 'You have already applied for this role.');
        }

        if ($request->resume_id) {
            $resume = Resume::where('user_id', auth()->id())->find($request->resume_id);
            if (! $resume) {
                return back()->withErrors(['resume_id' => 'Invalid resume.']);
            }
        }

        JobApplication::create([
            'user_id' => auth()->id(),
            'job_role_id' => $jobRole->id,
            'resume_id' => $request->resume_id ?: null,
            'cover_message' => $request->cover_message ? trim($request->cover_message) : null,
            'status' => 'applied',
        ]);

        return redirect()->route('job-goal.show', $jobRole)
            ->with('success', 'Your application has been submitted. We will get back to you soon.');
    }
}
