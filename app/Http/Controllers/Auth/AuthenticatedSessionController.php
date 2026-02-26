<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $idToken = $request->session()->get('sso_id_token');
        $isSsoLogin = (bool) $request->session()->get('sso_login', false);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $logoutUrl = (string) config('services.keycloak.logout_url');
        $postLogoutRedirect = route('login', absolute: true);
        if ($isSsoLogin && $logoutUrl !== '') {
            $showConfirmPage = filter_var(config('services.keycloak.logout_confirm', true), FILTER_VALIDATE_BOOL);
            $queryParams = [
                'client_id' => config('services.keycloak.client_id'),
                'post_logout_redirect_uri' => $postLogoutRedirect,
                // Compatibility for some Keycloak deployments.
                'redirect_uri' => $postLogoutRedirect,
            ];

            // If confirmation page is disabled, use id_token_hint for direct logout.
            if (! $showConfirmPage && $idToken) {
                $queryParams['id_token_hint'] = $idToken;
            }

            $query = http_build_query($queryParams);

            return redirect()->away(rtrim($logoutUrl, '?').'?'.$query);
        }

        return redirect('/');
    }
}
