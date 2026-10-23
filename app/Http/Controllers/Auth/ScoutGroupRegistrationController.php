<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RegistrationStatusEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Jobs\SendScoutGroupRegisteredPush;
use App\Models\Event;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

/**
 * Controller untuk registrasi akun baru pangkalan Gugus Depan (Sekolah / Kontingen)
 * Penambahan regu, peserta, dan bindamping dilakukan setelah login di dasbor pangkalan masing-masing.
 */
class ScoutGroupRegistrationController extends Controller
{
    /**
     * Daftar 6 Kwartir Ranting resmi di bawah naungan Kwarcab Gerakan Pramuka Kota Padangsidimpuan
     */
    protected array $kwarrans = [
        'Kwarran Padangsidimpuan Utara',
        'Kwarran Padangsidimpuan Selatan',
        'Kwarran Padangsidimpuan Batunadua',
        'Kwarran Padangsidimpuan Hutaimbaru',
        'Kwarran Padangsidimpuan Tenggara',
        'Kwarran Padangsidimpuan Angkola Julu',
    ];

    /**
     * Menampilkan formulir pendaftaran pangkalan Gugus Depan
     */
    public function create(): View
    {
        $event = Event::where('is_active', true)->first();
        $isRegistrationOpen = $event ? $event->isRegistrationOpen() : config('registration.is_enabled', true);

        return view('pages.auth.register-scout-group', [
            'kwarrans' => $this->kwarrans,
            'event' => $event,
            'isRegistrationOpen' => $isRegistrationOpen,
        ]);
    }

    /**
     * Memproses pendaftaran akun pangkalan Gugus Depan
     */
    public function store(Request $request): RedirectResponse
    {
        $event = Event::where('is_active', true)->first();

        // 1. Validasi batas waktu registrasi pangkalan
        if ($event && ! $event->isRegistrationOpen()) {
            return back()->withErrors([
                'registration_closed' => 'Batas waktu pendaftaran akun Gugus Depan telah berakhir. Silakan hubungi Sekretariat Panitia Pelaksana LT III Kwarcab Kota Padangsidimpuan.',
            ])->withInput();
        }

        // 2. Validasi input formulir identitas pangkalan & penanggung jawab
        $validated = $request->validate([
            // Identitas Pangkalan Sekolah
            'school_name' => ['required', 'string', 'max:255'],
            'group_number' => ['required', 'string', 'max:100'],
            'kwarran' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],

            // Akun Penanggung Jawab / Pembina (PIC Kontingen)
            'pic_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],

            // Pernyataan & Pakta Integritas
            'terms' => ['accepted'],
        ], [
            'school_name.required' => 'Nama pangkalan / sekolah wajib diisi.',
            'group_number.required' => 'Nomor Gugus Depan resmi pangkalan wajib diisi.',
            'kwarran.required' => 'Pilih Kwartir Ranting (Kwarran) pangkalan.',
            'address.required' => 'Alamat lengkap pangkalan wajib diisi.',
            'pic_name.required' => 'Nama lengkap pembina penanggung jawab (PIC) wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp / HP penanggung jawab wajib diisi.',
            'terms.accepted' => 'Anda wajib menyetujui pernyataan keabsahan data pangkalan.',
        ]);

        $fullAddress = trim($validated['address']) . ', ' . $validated['kwarran'] . ', Kota Padangsidimpuan';

        // 3. Format nomor gudep dan buat ID unik Gudep (G-{no_gudep})
        $groupNumberData = ScoutGroup::formatGroupNumbers($validated['group_number']);
        $gudepId = ScoutGroup::generateGudepId($groupNumberData);

        // 4. Username persis sama dengan ID Gudep (case-sensitive); kata sandi acak aman
        $username = $gudepId;

        $plainPassword = Str::password(10, letters: true, numbers: true, symbols: false, spaces: false);

        $scoutGroup = DB::transaction(function () use ($validated, $fullAddress, $groupNumberData, $gudepId, $username, $plainPassword) {
            // Buat akun penanggung jawab dengan kredensial tergenerasi otomatis
            $user = User::create([
                'name' => $validated['pic_name'],
                'username' => $username,
                'id_number' => $gudepId,
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($plainPassword),
                'is_active' => true,
            ]);

            // Tetapkan hak akses pangkalan Gudep
            $user->assignRole(RoleEnum::ScoutGroup);

            // Buat entitas pangkalan Gugus Depan dengan ID resmi
            return ScoutGroup::create([
                'user_id' => $user->id,
                'registration_code' => $gudepId,
                'school_name' => $validated['school_name'],
                'group_number' => $groupNumberData['combined'],
                'male_group_number' => $groupNumberData['male'],
                'female_group_number' => $groupNumberData['female'],
                'kwarran_name' => $validated['kwarran'],
                'address' => $fullAddress,
                'contact_person' => $validated['pic_name'],
                'contact_phone' => $validated['phone_number'],
                'verification_status' => RegistrationStatusEnum::Draft,
            ]);
        });

        // Beritahu Admin lewat push; kegagalan notifikasi tidak boleh menggagalkan pendaftaran
        try {
            SendScoutGroupRegisteredPush::dispatch($scoutGroup->id);
        } catch (Throwable $e) {
            Log::warning('Gagal mengantrekan notifikasi pendaftaran gudep: '.$e->getMessage());
        }

        // Login otomatis dan tetapkan konfirmasi password agar dapat langsung ubah password
        Auth::login($scoutGroup->user);
        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        // Simpan kredensial terbuat di flash session untuk ditampilkan pada halaman sukses
        $request->session()->flash('newly_registered_credentials', [
            'school_name' => $scoutGroup->school_name,
            'gudep_id' => $gudepId,
            'username' => $username,
            'password' => $plainPassword,
            'pic_name' => $validated['pic_name'],
        ]);

        return redirect()->route('register.scout-group.success');
    }

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
