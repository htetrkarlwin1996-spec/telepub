<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasArtist
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->artist) {
            return redirect()->route('artist.setup');
        }

        return $next($request);
    }
}
