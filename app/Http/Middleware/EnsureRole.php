<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Batasi akses berdasarkan peran. Untuk request web, arahkan kembali alih-alih JSON.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $wantsJson = $request->is('api/*') || $request->expectsJson();

        if (! $user) {
            return $wantsJson
                ? response()->json(['message' => 'Unauthenticated.', 'code' => 'UNAUTHENTICATED'], 401)
                : redirect()->route('login');
        }

        $userRole = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        if (! in_array($userRole, $roles, true)) {
            return $wantsJson
                ? response()->json([
                    'message' => 'Anda tidak memiliki hak akses untuk resource ini.',
                    'code' => 'FORBIDDEN_ROLE_ACCESS',
                ], 403)
                : redirect()->route('home')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
