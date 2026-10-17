<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsJury
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isJury(), 403, 'Acceso solo para jurados.');

        return $next($request);
    }
}
