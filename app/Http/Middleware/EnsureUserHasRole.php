<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan pengguna yang masuk memiliki peran yang berwenang
 */
class EnsureUserHasRole
{
    /**
     * Menangani permintaan masuk
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Pengguna wajib login terlebih dahulu
        if (! $user) {
            return redirect()->route('login');
        }

        // Pengguna non-aktif dicegah mengakses sistem
        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.',
            ]);
        }

        // Pengguna dengan kata sandi awal wajib mengganti kata sandi pada login pertama
        if ($user->must_change_password) {
            $request->session()->put('auth.password_confirmed_at', time());

            return redirect()->route('security.edit');
        }

        // Memeriksa apakah user memiliki salah satu peran yang disyaratkan
        if (! empty($roles)) {
            $parsedRoles = [];
            foreach ($roles as $role) {
                foreach (explode('|', $role) as $r) {
                    $parsedRoles[] = trim($r);
                }
            }

            if (! $user->hasAnyRole($parsedRoles)) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka halaman ini.');
            }
        }

        return $next($request);
    }
}
