<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{ //cek role
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (auth()->user()->role->name_roles !== $role) {
            return redirect()->route('login')->with('error', 'Anda tidak memiliki akses.');
        }
        return $next($request);
    }
}
