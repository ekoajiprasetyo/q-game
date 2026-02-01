<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Check if user has one of the specified roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Jika belum login, biarkan auth middleware yang handle redirect ke login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // 2. Check if user has any of the specified roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // 3. RESTRICTED ACCESS (Misal Siswa mencoba akses Admin)
        // Redirect ke dashboard URL user tersebut (Untuk siswa -> /game)
        // Dan kirim flash message 'error' untuk Toast notification
        return redirect($request->user()->getDashboardUrl())
            ->with('error', 'Akses Ditolak: Akun Siswa tidak diizinkan masuk Dashboard Guru.');
    }
}
