<?php

namespace App\Http\Controllers;

use App\Audit\PeristiwaAudit;
use App\Enums\Peranan;
use App\Models\User;
use App\Services\GoogleSignIn;
use App\Services\JejakAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleSignInController extends Controller
{
    public function login(GoogleSignIn $google)
    {
        return view('abm.login', ['configured' => $google->configured()]);
    }

    public function redirect(Request $request, GoogleSignIn $google)
    {
        if (! $google->configured()) {
            return redirect()->route('login')->withErrors(['login' => 'Log masuk Google belum disediakan. Sila hubungi pentadbir.']);
        }
        $state = bin2hex(random_bytes(32));
        $verifier = bin2hex(random_bytes(48));
        $request->session()->put('google_flow', ['state' => $state, 'verifier' => $verifier, 'issued_at' => now()->timestamp]);
        return redirect()->away($google->authorizationUrl($state, $verifier));
    }

    public function callback(Request $request, GoogleSignIn $google, JejakAudit $audit)
    {
        $flow = $request->session()->pull('google_flow');
        if (! $google->configured() || ! is_array($flow) || ! is_string($flow['state'] ?? null) || ! is_string($flow['verifier'] ?? null) ||
            ! is_int($flow['issued_at'] ?? null) || ! is_string($request->query('state')) ||
            ! hash_equals($flow['state'], $request->query('state')) ||
            now()->timestamp - $flow['issued_at'] > 600 || ! is_string($request->query('code')) ||
            $request->query('code') === '' || strlen($request->query('code')) > 4096) {
            Log::notice('abm.google.state_rejected');
            return redirect()->route('login')->withErrors(['login' => 'Sesi log masuk tidak sah atau telah tamat. Sila cuba semula.']);
        }
        try {
            $identity = $google->identity($request->query('code'), $flow['verifier']);
            $matches = User::whereRaw('lower(email) = ?', [$identity['email']])->get();
            $user = $matches->count() === 1 ? $matches->first() : null;
            if ($user === null || ! $user->is_active || $user->ptj_id === null ||
                ($user->google_subject !== null && $user->google_subject !== $identity['sub']) ||
                ! $user->penugasanPeranan()->exists()) {
                throw new \RuntimeException('Registered identity required.');
            }
            Auth::login($user);
            $role = $user->penugasanPeranan()->where('peranan', Peranan::Superadmin->value)->exists()
                ? Peranan::Superadmin : $user->penugasanPeranan()->orderBy('id')->firstOrFail()->peranan;
            $audit->jalankan($role, function () use ($user, $identity) {
                $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (strtolower($current->email) !== $identity['email'] ||
                    ($current->google_subject !== null && $current->google_subject !== $identity['sub'])) {
                    throw new \RuntimeException('Registered identity changed.');
                }
                $linked = $current->google_subject !== null;
                $current->forceFill(['google_subject' => $identity['sub']])->save();
                return new PeristiwaAudit('auth.google.signed_in', User::class, (string) $user->id,
                    ['google_identity_linked' => $linked], ['google_identity_linked' => true]);
            });
            $request->session()->regenerate();
            $request->session()->put(['abm_authenticated_at' => now()->timestamp, 'abm_last_activity' => now()->timestamp]);
            return redirect()->route('home');
        } catch (Throwable $exception) {
            // Provider errors can contain tokens. Do not log/flash the raw exception.
            Log::notice('abm.google.sign_in_failed', ['exception_type' => get_class($exception)]);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['login' => 'Akses tidak dibenarkan atau log masuk tidak dapat diselesaikan. Sila hubungi pentadbir.']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
