<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->user();
        $response = $next($request);

        if (! $authUser || ! Schema::hasTable('activity_logs')) {
            return $response;
        }

        $routeName = $request->route()?->getName();

        if ($this->shouldSkip($request, $routeName)) {
            return $response;
        }

        try {
            ActivityLog::create([
                'user_id' => $authUser->id,
                'event' => 'activity',
                'action' => $this->resolveAction($request),
                'guard' => 'web',
                'role_name' => $authUser->role?->name,
                'route_name' => $routeName,
                'url' => $request->fullUrl(),
                'http_method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'login_identifier' => $authUser->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'occurred_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never break user flow if activity logging has an error.
        }

        return $response;
    }

    private function shouldSkip(Request $request, ?string $routeName): bool
    {
        if ($request->is('storage/*') || $request->is('build/*') || $request->is('images/*') || $request->is('css/*') || $request->is('js/*')) {
            return true;
        }

        return in_array($routeName, [
            'sanctum.csrf-cookie',
        ], true);
    }

    private function resolveAction(Request $request): string
    {
        return match ($request->method()) {
            'GET', 'HEAD' => 'page_visit',
            'POST' => 'click_or_submit',
            'PUT', 'PATCH' => 'update_data',
            'DELETE' => 'delete_data',
            default => 'activity',
        };
    }
}
