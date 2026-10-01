<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureArtistCanManage
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(current_artist_can_manage(), 403, 'This artist account has report-only access.');

        return $next($request);
    }
}
