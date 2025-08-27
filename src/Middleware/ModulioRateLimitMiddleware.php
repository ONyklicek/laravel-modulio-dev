<?php

namespace NyonCode\LaravelModulio\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Middleware pro rate limiting přístupu k modulům
 *
 * Omezuje počet požadavků na module API endpoints
 * podle konfigurace.
 *
 * @package NyonCode\LaravelModulio\Middleware
 *
 * ---
 *
 * Module Rate Limiting Middleware
 *
 * Limits number of requests to module API endpoints
 * based on configuration.
 */
class ModulioRateLimitMiddleware
{
    /**
     * Zpracuje požadavek
     * Handle request
     *
     * @param Request $request
     * @param Closure $next
     * @param int $maxAttempts Maximální počet pokusů
     * @param int $decayMinutes Doba resetování v minutách
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): mixed
    {
        $key = $this->resolveRequestSignature($request);

        if (cache()->has("rate_limit:{$key}")) {
            $attempts = cache()->increment("rate_limit:{$key}");
        } else {
            $attempts = 1;
            cache()->put("rate_limit:{$key}", $attempts, now()->addMinutes($decayMinutes));
        }

        if ($attempts > $maxAttempts) {
            return $this->handleTooManyAttempts($request, $maxAttempts, $decayMinutes);
        }

        $response = $next($request);

        // Přidání rate limit hlaviček
        // Add rate limit headers
        return $this->addHeaders($response, $maxAttempts, $attempts, $decayMinutes);
    }

    /**
     * Vytvoří podpis požadavku pro rate limiting
     * Create request signature for rate limiting
     *
     * @param Request $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($request->user()) {
            return sha1('modulio_rate_limit:' . $request->user()->id);
        }

        return sha1('modulio_rate_limit:' . $request->ip());
    }

    /**
     * Zpracuje příliš mnoho pokusů
     * Handle too many attempts
     *
     * @param Request $request
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return JsonResponse|Response
     */
    protected function handleTooManyAttempts(
        Request $request,
        int $maxAttempts,
        int $decayMinutes
    ): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Too Many Requests',
                'error' => "Rate limit exceeded. Max {$maxAttempts} requests per {$decayMinutes} minutes."
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        abort(Response::HTTP_TOO_MANY_REQUESTS, 'Too Many Requests');
    }

    /**
     * Přidá rate limit hlavičky
     * Add rate limit headers
     *
     * @param mixed $response
     * @param int $maxAttempts
     * @param int $currentAttempts
     * @param int $decayMinutes
     * @return mixed
     */
    protected function addHeaders(mixed $response, int $maxAttempts, int $currentAttempts, int $decayMinutes): mixed
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - $currentAttempts),
            'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->getTimestamp(),
        ]);

        return $response;
    }
}
