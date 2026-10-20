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

        // 2. Validasi input formulir akun & pangkalan
        $validated = $request->validate([
            // Identitas Pangkalan Sekolah
            'school_name' => ['required', 'string', 'max:255'],
            'group_number' => ['required', 'string', 'max:100'],
            'kwarran' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],

            // Akun Penanggung Jawab / Pembina (PIC Kontingen)
            'pic_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'username' => User::usernameRules(),
            'password' => ['required', 'string', 'confirmed', Password::defaults()],

            // Pernyataan & Pakta Integritas
            'terms' => ['accepted'],
        ], [
            'school_name.required' => 'Nama pangkalan / sekolah wajib diisi.',
            'group_number.required' => 'Nomor Gugus Depan resmi pangkalan wajib diisi.',
            'kwarran.required' => 'Pilih Kwartir Ranting (Kwarran) pangkalan.',
            'address.required' => 'Alamat lengkap pangkalan wajib diisi.',
            'pic_name.required' => 'Nama lengkap pembina penanggung jawab (PIC) wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp / HP penanggung jawab wajib diisi.',
            ...User::usernameMessages(),
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'terms.accepted' => 'Anda wajib menyetujui pernyataan keabsahan data pangkalan.',
        ]);

        $fullAddress = trim($validated['address']) . ', ' . $validated['kwarran'] . ', Kota Padangsidimpuan';

        $scoutGroup = DB::transaction(function () use ($validated, $fullAddress) {
            // Buat akun penanggung jawab
            $user = User::create([
                'name' => $validated['pic_name'],
                'username' => $validated['username'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // Tetapkan hak akses pangkalan Gudep
            $user->assignRole(RoleEnum::ScoutGroup);

            // Buat entitas pangkalan Gugus Depan
            $registrationCode = ScoutGroup::generateRegistrationCode();

            $groupNumberData = ScoutGroup::formatGroupNumbers($validated['group_number']);

            return ScoutGroup::create([
                'user_id' => $user->id,
                'registration_code' => $registrationCode,
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

        // Login otomatis dan arahkan langsung ke Dasbor Kontingen Gudep
        Auth::login($scoutGroup->user);
        $request->session()->regenerate();

        return redirect()->route('scout-group.index')->with(
            'status',
            "Akun pangkalan {$scoutGroup->school_name} berhasil dibuat dengan kode {$scoutGroup->registration_code}! Silakan lanjutkan pengisian regu penggalang, anggota peserta, dan pembina pendamping (bindamping) di dasbor Anda."
        );
    }
}
