<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAbmSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user()?->fresh();
        $now = now()->timestamp;
        $authenticated = $request->session()->get('abm_authenticated_at');
        $active = $request->session()->get('abm_last_activity');
        if ($user === null || ! $user->is_active || $user->ptj_id === null || $user->google_subject === null ||
            ! $user->penugasanPeranan()->exists() || ! is_int($authenticated) || ! is_int($active) ||
            $now - $authenticated >= 28800 || $now - $active >= 1800) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['login' => 'Sesi telah tamat atau akses tidak lagi dibenarkan. Sila log masuk semula.']);
        }
        Auth::setUser($user);
        // These are server-rendered navigations or explicit renewal, not background polling/autosave.
        $request->session()->put('abm_last_activity', $now);
        return $next($request);
    }
}
