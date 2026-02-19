<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $profile = $user->referrerProfile;
        $isApproved = $profile && $profile->is_approved;

        $jobs = collect();
        $counts = ['all' => 0, 'active' => 0, 'draft' => 0, 'closed' => 0];
        if ($isApproved) {
            $jobs = $user->employerJobs()->withCount('applications')->orderByDesc('created_at')->get();
            $counts = [
                'all'    => $user->employerJobs()->count(),
                'active' => $user->employerJobs()->where('status', 'active')->count(),
                'draft'  => $user->employerJobs()->where('status', 'draft')->count(),
                'closed' => $user->employerJobs()->where('status', 'closed')->count(),
            ];
        }

        return view('hirevo.employer.dashboard', [
            'profile'    => $profile,
            'isApproved' => $isApproved,
            'jobs'       => $jobs,
            'counts'     => $counts,
        ]);
    }
}
