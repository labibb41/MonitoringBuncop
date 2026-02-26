<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $redirectUri = $this->resolveRedirectUri($request);

        $states = $request->session()->get('sso_states', []);
        $states = is_array($states) ? $states : [];

        // Keep only recent states (10 minutes) to avoid stale growth.
        $now = time();
        $states = array_filter($states, function ($item) use ($now) {
            if (! is_array($item)) {
                return false;
            }

            $createdAt = (int) ($item['created_at'] ?? 0);

            return $createdAt > 0 && ($now - $createdAt) <= 600;
        });

        $states[$state] = [
            'created_at' => $now,
            'redirect_uri' => $redirectUri,
        ];
        $request->session()->put('sso_states', $states);

        $url = $this->oidcBaseUrl().'/auth?'.http_build_query([
            'client_id' => config('services.keycloak.client_id'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $incomingState = (string) $request->query('state', '');
        $states = $request->session()->get('sso_states', []);
        $states = is_array($states) ? $states : [];
        $stateData = $incomingState !== '' ? ($states[$incomingState] ?? null) : null;

        if ($incomingState === '' || ! is_array($stateData)) {
            return redirect()->route('login')->withErrors(['email' => 'SSO gagal: state tidak valid.']);
        }

        unset($states[$incomingState]);
        $request->session()->put('sso_states', $states);

        $redirectUri = (string) ($stateData['redirect_uri'] ?? $this->resolveRedirectUri($request));

        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'SSO ditolak: '.$request->query('error_description', $request->query('error')),
            ]);
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route('login')->withErrors(['email' => 'SSO gagal: authorization code tidak ditemukan.']);
        }

        try {
            $tokenResponse = $this->keycloakHttp()->asForm()->post($this->oidcBaseUrl().'/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.keycloak.client_id'),
                'client_secret' => config('services.keycloak.client_secret'),
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);
        } catch (ConnectionException) {
            return redirect()->route('login')->withErrors([
                'email' => 'SSO gagal: tidak bisa terhubung ke server Keycloak (cek SSL/sertifikat intranet).',
            ]);
        }

        if (! $tokenResponse->successful()) {
            return redirect()->route('login')->withErrors(['email' => 'SSO gagal: token endpoint tidak merespons dengan benar.']);
        }

        $tokenData = $tokenResponse->json();
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        $idToken = (string) ($tokenData['id_token'] ?? '');

        if ($accessToken === '') {
            return redirect()->route('login')->withErrors(['email' => 'SSO gagal: access token kosong.']);
        }

        try {
            $userInfoResponse = $this->keycloakHttp()->withToken($accessToken)->get($this->oidcBaseUrl().'/userinfo');
        } catch (ConnectionException) {
            return redirect()->route('login')->withErrors([
                'email' => 'SSO gagal: koneksi ke userinfo Keycloak terputus (cek SSL/sertifikat intranet).',
            ]);
        }

        if (! $userInfoResponse->successful()) {
            return redirect()->route('login')->withErrors(['email' => 'SSO gagal: tidak bisa mengambil data user.']);
        }

        $profile = $userInfoResponse->json();
        $email = strtolower((string) ($profile['email'] ?? ''));
        $sub = (string) ($profile['sub'] ?? '');
        $name = (string) ($profile['name'] ?? $profile['preferred_username'] ?? 'User SSO');

        if ($email === '') {
            $username = (string) ($profile['preferred_username'] ?? '');
            $email = $username !== '' ? strtolower($username).'@sso.local' : '';
        }

        if ($email === '' || $sub === '') {
            return redirect()->route('login')->withErrors(['email' => 'SSO gagal: profile email/sub tidak valid.']);
        }

        $user = User::query()
            ->where('keycloak_sub', $sub)
            ->orWhere('email', $email)
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Str::random(40),
                'role_id' => Role::query()->firstOrCreate(['name' => 'user'])->id,
                'keycloak_sub' => $sub,
                'sso_provider' => 'keycloak',
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'name' => $name ?: $user->name,
                'email' => $email ?: $user->email,
                'keycloak_sub' => $sub,
                'sso_provider' => 'keycloak',
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('sso_id_token', $idToken);
        $request->session()->put('sso_login', true);
        $request->session()->put('sso_provider', 'keycloak');

        return redirect()->intended(route('home', absolute: false));
    }

    private function oidcBaseUrl(): string
    {
        $baseUrl = rtrim((string) config('services.keycloak.base_url'), '/');
        $realm = trim((string) config('services.keycloak.realm'), '/');

        return $baseUrl.'/realms/'.$realm.'/protocol/openid-connect';
    }

    private function resolveRedirectUri(Request $request): string
    {
        $configured = trim((string) config('services.keycloak.redirect'));

        if ($configured !== '') {
            return $configured;
        }

        return route('sso.callback', absolute: true);
    }

    private function keycloakHttp(): PendingRequest
    {
        $verify = filter_var(config('services.keycloak.verify_ssl', true), FILTER_VALIDATE_BOOL);
        $caBundle = trim((string) config('services.keycloak.ca_bundle', ''));

        if ($caBundle !== '') {
            return Http::withOptions(['verify' => $caBundle])->timeout(20);
        }

        return Http::withOptions(['verify' => $verify])->timeout(20);
    }
}
