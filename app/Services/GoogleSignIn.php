<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** OAuth code flow; identity is fetched directly from Google's authenticated back channel. */
class GoogleSignIn
{
    public function configured(): bool
    {
        $url = (string) config('google.redirect_uri');
        $parts = parse_url($url);
        $secure = ($parts['scheme'] ?? '') === 'https' ||
            (($parts['scheme'] ?? '') === 'http' && in_array($parts['host'] ?? '', ['localhost', '127.0.0.1'], true));
        return filled(config('google.client_id')) && filled(config('google.client_secret')) &&
            count(config('google.workspace_domains', [])) > 0 && $secure;
    }

    public function authorizationUrl(string $state, string $verifier): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('google.client_id'), 'redirect_uri' => config('google.redirect_uri'),
            'response_type' => 'code', 'scope' => 'openid email profile', 'prompt' => 'select_account',
            'state' => $state, 'code_challenge_method' => 'S256',
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
        ]);
    }

    public function identity(string $code, string $verifier): array
    {
        // Never accept identity claims/ID tokens supplied by the browser, and never
        // decode an unverified JWT. Consume identity only from Google over TLS.
        $tokens = Http::asForm()->acceptJson()->timeout(15)->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('google.client_id'), 'client_secret' => config('google.client_secret'),
            'redirect_uri' => config('google.redirect_uri'), 'code' => $code,
            'code_verifier' => $verifier, 'grant_type' => 'authorization_code',
        ])->throw()->json();
        if (! is_string($tokens['access_token'] ?? null) || $tokens['access_token'] === '') {
            throw new RuntimeException('Google authentication failed.');
        }
        $identity = Http::withToken($tokens['access_token'])->acceptJson()->timeout(15)
            ->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();
        if (! is_string($identity['sub'] ?? null) || $identity['sub'] === '' || strlen($identity['sub']) > 255 ||
            ($identity['email_verified'] ?? null) !== true || ! filter_var($identity['email'] ?? '', FILTER_VALIDATE_EMAIL) ||
            ! in_array(strtolower($identity['hd'] ?? ''), config('google.workspace_domains', []), true)) {
            throw new RuntimeException('Google identity is not eligible.');
        }
        return ['sub' => $identity['sub'], 'email' => strtolower($identity['email'])];
    }
}
