<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $panel): Response
    {
        $user = Auth::user();

        if ($user) {
            // Jika akses panel /app tapi BUKAN siswa -> return 404
            if ($panel === 'app' && $user->role !== 'siswa') {
                abort(404);
            }

            // Jika akses panel /ujian-app tapi role-nya 'siswa' -> return 404
            if ($panel === 'ujian-app' && $user->role === 'siswa') {
                abort(404);
            }
        }

        return $next($request);
    }
}
