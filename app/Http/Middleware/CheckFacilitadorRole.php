<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckFacilitadorRole
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $usuario = Auth::user();
            $rol = $usuario->rol->nombre;
            if ($rol === 'facilitador') {
                return $next($request);
            }
        }
        return response()->json(['error' => 'Acceso no autorizado'], 403);
    }
}