<?php

namespace NyonCode\LaravelModulio\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Middleware pro kontrolu oprávnění modulů
 *
 * Middleware který kontroluje oprávnění uživatele
 * pro přístup k funkcionalitě modulů.
 *
 * @package NyonCode\LaravelModulio\Middleware
 *
 * ---
 *
 * Module Permission Middleware
 *
 * Middleware that checks user permissions
 * for accessing module functionality.
 */
class ModulioPermissionMiddleware
{
    /**
     * Zpracuje požadavek
     * Handle request
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission Vyžadované oprávnění
     * @param string|null $guard Guard pro autentifikaci
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $guard = null): mixed
    {
        // Kontrola autentifikace
        // Check authentication
        if (!auth($guard)->check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = auth($guard)->user();

        // Kontrola oprávnění
        // Check permission
        if (!$user->can($permission)) {
            return $this->handleUnauthorized($request, $permission, $user);
        }

        // Logování přístupu (pokud je povoleno)
        // Log access (if enabled)
        $this->logAccess($request, $permission, $user);

        return $next($request);
    }

    /**
     * Zpracuje neautentifikovaného uživatele
     * Handle unauthenticated user
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Response
     */
    protected function handleUnauthenticated(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'Authentication required for this action'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Zpracuje neautorizovaného uživatele
     * Handle unauthorized user
     *
     * @param Request $request
     * @param string $permission
     * @param mixed $user
     * @return JsonResponse|RedirectResponse|Response
     */
    protected function handleUnauthorized(Request $request, string $permission, $user)
    {
        // Logování neautorizovaného přístupu
        // Log unauthorized access
        if (config('modulio.log_events', true)) {
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'permission' => $permission,
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Forbidden',
                'error' => "Insufficient permissions. Required: {$permission}"
            ], Response::HTTP_FORBIDDEN);
        }

        // Pro web požadavky - přesměrování nebo error stránka
        // For web requests - redirect or error page
        if (view()->exists('errors.403')) {
            return response()->view('errors.403', [
                'required_permission' => $permission
            ], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, "Insufficient permissions. Required: {$permission}");
    }

    /**
     * Loguje přístup
     * Log access
     *
     * @param Request $request
     * @param string $permission
     * @param mixed $user
     */
    protected function logAccess(Request $request, string $permission, $user): void
    {
        if (!config('modulio.log_events', true)) {
            return;
        }

        Log::info('Module access granted', [
            'user_id' => $user->id,
            'permission' => $permission,
            'route' => $request->route()->getName(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);
    }
}