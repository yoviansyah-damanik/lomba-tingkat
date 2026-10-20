<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak akun peserta/bindamping pada halaman yang mengubah kredensial akun sendiri
 */
class BlockManagedAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->isManagedAccount(), 403, 'Akun ini dikelola otomatis dari data gugus depan dan tidak dapat diubah sendiri.');

        return $next($request);
    }
}
