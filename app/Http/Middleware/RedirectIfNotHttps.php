<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfNotHttps
{
    public function handle($request, Closure $next)
    {
        if (!$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}


