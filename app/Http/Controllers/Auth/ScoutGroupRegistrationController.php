<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman sukses registrasi akun pangkalan Gugus Depan.
 * Gerbang kode undangan dan formulir registrasi berupa komponen Livewire (App\Livewire\Auth).
 */
class ScoutGroupRegistrationController extends Controller
{
    /**
     * Menampilkan informasi kredensial yang dibuat sistem dan mengarahkan untuk mengubah password
     */
    public function success(Request $request): View|RedirectResponse
    {
        $credentials = $request->session()->get('newly_registered_credentials');

        if (! $credentials) {
            return redirect()->route('scout-group.index');
        }

        // Pertahankan sesi flash jika pengguna me-refresh halaman
        $request->session()->reflash();

        return view('pages.auth.register-success', [
            'credentials' => $credentials,
        ]);
    }
}
