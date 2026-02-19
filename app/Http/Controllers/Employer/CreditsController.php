<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreditsController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $profile = $user->referrerProfile;
        $credits = $profile ? (int) $profile->credits : 0;

        return view('hirevo.employer.credits.index', [
            'credits' => $credits,
        ]);
    }
}
