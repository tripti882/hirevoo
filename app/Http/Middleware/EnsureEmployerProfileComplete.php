<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployerProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isReferrer()) {
            return $next($request);
        }

        $profile = $user->referrerProfile;
        if (! $profile || ! $this->isProfileComplete($profile)) {
            return redirect()
                ->route('employer.profile')
                ->with('info', 'Please complete your company profile to continue.');
        }

        return $next($request);
    }

    private function isProfileComplete($profile): bool
    {
        return ! empty($profile->company_name) && ! empty($profile->company_email);
    }
}
