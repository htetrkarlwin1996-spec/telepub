<?php

namespace App\Http\Middleware;

use App\Services\MaintenanceMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicMaintenanceMode
{
    public function __construct(private readonly MaintenanceMode $maintenanceMode)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->maintenanceMode->active() || $this->isAdminAccessPath($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Service temporarily unavailable for maintenance.',
            ], Response::HTTP_SERVICE_UNAVAILABLE, [
                'Retry-After' => $this->maintenanceMode->remainingSeconds(),
            ]);
        }

        return response()
            ->view('errors.503', [
                'remainingSeconds' => $this->maintenanceMode->remainingSeconds(),
                'maintenanceEndsAt' => $this->maintenanceMode->endsAt(),
            ], Response::HTTP_SERVICE_UNAVAILABLE)
            ->header('Retry-After', (string) $this->maintenanceMode->remainingSeconds());
    }

    private function isAdminAccessPath(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*',
            'login',
            'logout',
        );
    }
}
