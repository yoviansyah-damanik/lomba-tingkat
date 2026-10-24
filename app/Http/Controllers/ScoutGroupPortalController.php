<?php

namespace App\Http\Controllers;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Controller untuk portal pendaftaran kontingen Gugus Depan
 */
class ScoutGroupPortalController extends Controller
{
    public function __construct(
        private readonly \App\Services\PdfService $pdf,
    ) {}

    /**
     * Daftar Kwartir Ranting resmi (sama dengan form registrasi)
     */
    public const KWARRANS = [
        'Kwarran Padangsidimpuan Utara',
        'Kwarran Padangsidimpuan Selatan',
        'Kwarran Padangsidimpuan Batunadua',
        'Kwarran Padangsidimpuan Hutaimbaru',
        'Kwarran Padangsidimpuan Tenggara',
        'Kwarran Padangsidimpuan Angkola Julu',
    ];

    /**
     * Mendapatkan atau membuat instance ScoutGroup milik user yang sedang login
     */
    protected function getOrCreateScoutGroup(Request $request): ScoutGroup
    {
        return ScoutGroup::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'registration_code' => 'GDP-'.strtoupper(substr(md5($request->user()->id), 0, 6)),
                'school_name' => $request->user()->name,
                'group_number' => 'Belum Diisi',
                'address' => 'Kota Padangsidimpuan',
                'contact_person' => $request->user()->name,
                'contact_phone' => $request->user()->phone_number ?? '08123456789',
                'verification_status' => RegistrationStatusEnum::Draft,
            ]
        );
    }

    /**
     * Beranda portal Gugus Depan
     */
    public function index(Request $request): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $scoutGroup->load(['patrols.participants', 'chaperones']);

        $boysPatrol = $scoutGroup->boysPatrol;
        $girlsPatrol = $scoutGroup->girlsPatrol;

        return view('portal.scout-group.index', compact('scoutGroup', 'boysPatrol', 'girlsPatrol'));
    }

    /**
     * Formulir profil pangkalan Gugus Depan
     */
    public function profile(Request $request): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        // Pisahkan alamat jalan dari sufiks ", {Kwarran}, Kota Padangsidimpuan" agar sama seperti input registrasi
        $streetAddress = (string) $scoutGroup->address;
        $suffix = ', '.$scoutGroup->kwarran_name.', Kota Padangsidimpuan';
        if ($scoutGroup->kwarran_name && str_ends_with($streetAddress, $suffix)) {
            $streetAddress = substr($streetAddress, 0, -strlen($suffix));
        }

        return view('portal.scout-group.profile', [
            'scoutGroup' => $scoutGroup,
            'kwarrans' => self::KWARRANS,
            'streetAddress' => $streetAddress,
        ]);
    }

    /**
     * Simpan pembaruan profil Gugus Depan
     */
    public function profileUpdate(Request $request): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Data terkunci dan tidak dapat diubah karena sedang dalam proses verifikasi atau telah disetujui.']);
        }

        $user = $request->user();

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'group_number' => ['required', 'string', 'max:100'],
            'kwarran' => ['required', 'string', 'in:'.implode(',', self::KWARRANS)],
            'address' => ['required', 'string', 'max:500'],
            'pic_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'username' => User::usernameRules($user->id),
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
        ], [
            'school_name.required' => 'Nama pangkalan / sekolah wajib diisi.',
            'group_number.required' => 'Nomor Gugus Depan resmi pangkalan wajib diisi.',
            'kwarran.required' => 'Pilih Kwartir Ranting (Kwarran) pangkalan.',
            'kwarran.in' => 'Kwartir Ranting (Kwarran) tidak valid.',
            'address.required' => 'Alamat lengkap pangkalan wajib diisi.',
            'pic_name.required' => 'Nama lengkap pembina penanggung jawab (PIC) wajib diisi.',
            'phone_number.required' => 'Nomor WhatsApp / HP penanggung jawab wajib diisi.',
            ...User::usernameMessages(),
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $groupNumberData = ScoutGroup::formatGroupNumbers($validated['group_number']);
        $fullAddress = trim($validated['address']).', '.$validated['kwarran'].', Kota Padangsidimpuan';

        DB::transaction(function () use ($scoutGroup, $user, $validated, $groupNumberData, $fullAddress) {
            $scoutGroup->update([
                'school_name' => $validated['school_name'],
                'group_number' => $groupNumberData['combined'],
                'male_group_number' => $groupNumberData['male'],
                'female_group_number' => $groupNumberData['female'],
                'kwarran_name' => $validated['kwarran'],
                'address' => $fullAddress,
                'contact_person' => $validated['pic_name'],
                'contact_phone' => $validated['phone_number'],
            ]);

            $userData = [
                'name' => $validated['pic_name'],
                'username' => $validated['username'],
                'phone_number' => $validated['phone_number'],
            ];
            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData);
        });

        return redirect()->route('scout-group.index')->with('success', 'Profil pangkalan Gugus Depan berhasil diperbarui.');
    }

    /**
     * Kelola regu pangkalan
     */
    public function patrols(Request $request): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $scoutGroup->load('patrols.participants');

        return view('portal.scout-group.patrols.index', compact('scoutGroup'));
    }

    /**
     * Buat regu baru (Putra atau Putri)
     */
    public function patrolStore(Request $request): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci dan tidak dapat menambah regu.']);
        }

        $validated = $request->validate([
            'patrol_name' => ['required', 'string', 'max:100'],
            'gender_category' => ['required', 'in:boys,girls'],
        ]);

        // Cek kuota: Maksimal 1 regu putra dan 1 regu putri per pangkalan
        $existing = $scoutGroup->patrols()->where('gender_category', $validated['gender_category'])->first();
        if ($existing) {
            return back()->withErrors(['gender_category' => 'Pangkalan Anda sudah memiliki regu untuk kategori ini.']);
        }

        $scoutGroup->patrols()->create([
            'patrol_name' => $validated['patrol_name'],
            'gender_category' => $validated['gender_category'],
            'status' => RegistrationStatusEnum::Draft,
        ]);

        return redirect()->route('scout-group.patrols')->with('success', 'Regu berhasil dibentuk.');
    }

    /**
     * Hapus data regu pangkalan
     */
    public function patrolDestroy(Request $request, string $patrolId): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci dan data regu tidak dapat dihapus.']);
        }

        $patrol = $scoutGroup->patrols()->with('participants')->findOrFail($patrolId);
        $patrolName = $patrol->patrol_name;

        foreach ($patrol->participants as $participant) {
            if ($participant->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($participant->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($participant->photo_path);
            }
        }

        $patrol->delete();

        return redirect()->route('scout-group.patrols')->with('success', "Regu {$patrolName} berhasil dihapus.");
    }

    /**
     * Cetak formulir rekap pendaftaran kontingen (FORM : 01) (PDF)
     */
    public function form01Print(Request $request, string $patrolId): \Illuminate\Http\Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $scoutGroup->load(['chaperones', 'patrols.participants']);
        $patrol = $scoutGroup->patrols()->with('participants')->findOrFail($patrolId);
        $patrol->setRelation('scoutGroup', $scoutGroup);

        $chaperone = $patrol->isBoys()
            ? $scoutGroup->chaperones->firstWhere('gender', 'male')
            : $scoutGroup->chaperones->firstWhere('gender', 'female');
        $orderedParticipants = $patrol->orderedParticipants();

        $filename = 'Form_01_Rekap_Regu_' . \Illuminate\Support\Str::slug($patrol->patrol_name) . '.pdf';

        return $this->pdf->response(
            'portal.scout-group.patrols.form-01',
            compact('scoutGroup', 'patrol', 'chaperone', 'orderedParticipants'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Formulir tambah peserta ke regu
     */
    public function participantCreate(Request $request, string $patrolId): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $patrol = $scoutGroup->patrols()->findOrFail($patrolId);

        $formalEducation = Participant::defaultFormalEducation();
        $scoutSiaga = Participant::defaultScoutSiaga();
        $scoutPenggalang = Participant::defaultScoutPenggalang();

        return view('portal.scout-group.participants.create', compact('scoutGroup', 'patrol', 'formalEducation', 'scoutSiaga', 'scoutPenggalang'));
    }

    /**
     * Simpan data peserta baru
     */
    public function participantStore(Request $request, string $patrolId): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $patrol = $scoutGroup->patrols()->findOrFail($patrolId);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci dan tidak dapat menambah peserta.']);
        }

        if ($patrol->participants()->count() >= Patrol::REQUIRED_MEMBERS) {
            return back()->withErrors(['status' => 'Setiap regu maksimal '.Patrol::REQUIRED_MEMBERS.' peserta.']);
        }

        $validated = $request->validate($this->participantRules(), $this->participantMessages(), $this->participantAttributes());

        // Validasi: 1 regu hanya boleh punya 1 Pinru dan 1 Wapinru
        if ($validated['role_in_patrol'] === PatrolRoleEnum::PatrolLeader->value && $patrol->hasLeader()) {
            return back()->withInput()->withErrors(['role_in_patrol' => 'Regu ini sudah memiliki Pimpinan Regu (Pinru).']);
        }

        if ($validated['role_in_patrol'] === PatrolRoleEnum::AssistantPatrolLeader->value && $patrol->hasAssistantLeader()) {
            return back()->withInput()->withErrors(['role_in_patrol' => 'Regu ini sudah memiliki Wakil Pimpinan Regu (Wapinru).']);
        }

        $data = $validated;
        unset($data['photo']);

        // Nomor gudep dan pangkalan riwayat Penggalang selalu mengikuti data gugus depan (tidak dapat diubah)
        $data['scout_penggalang'] = array_merge($data['scout_penggalang'] ?? [], [
            'group_number' => $patrol->group_number,
            'pangkalan' => $scoutGroup->school_name,
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('participants', 'public');
        }

        // Simpan 7 berkas persyaratan peserta jika diunggah
        foreach (Participant::DOCUMENTS as $inputKey => [$column, $label]) {
            unset($data[$inputKey]);
            if ($request->hasFile($inputKey)) {
                $data[$column] = $request->file($inputKey)->store('participants/documents', 'public');
            }
        }

        $patrol->participants()->create(array_merge($data, [
            'status' => RegistrationStatusEnum::Draft,
        ]));

        return redirect()->route('scout-group.patrols')->with('success', 'Peserta berhasil didaftarkan ke dalam regu.');
    }

    /**
     * Aturan validasi data peserta: Bagian 1 (identitas), 2 (orang tua), dan 3 (pendidikan formal) wajib diisi
     */
    protected function participantRules(?Participant $participant = null): array
    {
        $rules = [
            // Bagian 1: Identitas diri & jabatan regu (telepon rumah opsional)
            'photo' => [$participant?->photo_path ? 'nullable' : 'required', 'image', 'max:2048'],
            'full_name' => ['required', 'string', 'max:255'],
            'role_in_patrol' => ['required', 'in:patrol_leader,assistant_patrol_leader,member'],
            'gender' => ['required', 'in:male,female'],
            'religion' => ['required', 'string', 'max:50'],
            'blood_type' => ['required', \App\Support\BloodType::rule()],
            'scout_id' => ['required', 'string', 'max:50'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'home_address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:30'],

            // Bagian 2: Data orang tua / wali (telepon rumah opsional)
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_job' => ['required', 'string', 'max:100'],
            'parent_address' => ['required', 'string'],
            'parent_city' => ['required', 'string', 'max:100'],
            'parent_postal_code' => ['required', 'string', 'max:10'],
            'parent_home_phone' => ['nullable', 'string', 'max:30'],
            'parent_phone' => ['required', 'string', 'max:30'],

            // Bagian 3: Pendidikan formal (SD & SMP)
            'formal_education' => ['required', 'array', 'min:1'],
            'formal_education.*.level' => ['required', 'string', 'max:20'],
            'formal_education.*.school_name' => ['required', 'string', 'max:255'],
            'formal_education.*.city' => ['required', 'string', 'max:100'],
            'formal_education.*.graduation_year' => ['required', 'string', 'max:10'],
            'formal_education.*.notes' => ['nullable', 'string', 'max:255'],

            // Bagian 4 & 5: Riwayat kepramukaan (opsional)
            'scout_siaga' => ['nullable', 'array'],
            'scout_penggalang' => ['nullable', 'array'],

            // Bagian 6: Berkas persyaratan peserta (KTA, BPJS, Surat Ket. Sehat, Akta Kelahiran, KK, SKU, SKK)
            'doc_kta' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_bpjs' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_health_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_birth_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_family_card' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_sku' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'doc_skk' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
        ];

        // Tahun lulus wajib diisi, kecuali SMP: peserta diasumsikan masih bersekolah
        unset($rules['formal_education.*.graduation_year']);
        foreach ((array) request()->input('formal_education', []) as $index => $education) {
            $rules["formal_education.$index.graduation_year"] = (($education['level'] ?? null) === 'SMP' ? 'nullable' : 'required').'|string|max:10';
        }

        return $rules;
    }

    protected function participantMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'photo.image' => 'Pas foto harus berupa gambar JPG atau PNG.',
            'photo.max' => 'Ukuran pas foto maksimal 2MB.',
            'file' => ':attribute harus berupa berkas yang valid.',
            'mimes' => ':attribute harus berformat PDF.',
            'max' => 'Ukuran :attribute maksimal :max KB.',
        ];
    }

    protected function participantAttributes(): array
    {
        return [
            'photo' => 'Pas foto',
            'full_name' => 'Nama lengkap',
            'role_in_patrol' => 'Jabatan dalam regu',
            'gender' => 'Jenis kelamin',
            'religion' => 'Agama',
            'blood_type' => 'Golongan darah',
            'scout_id' => 'Nomor KTA',
            'birth_place' => 'Tempat lahir',
            'birth_date' => 'Tanggal lahir',
            'home_address' => 'Alamat rumah',
            'city' => 'Kota / kabupaten',
            'postal_code' => 'Kode pos',
            'home_phone' => 'Telepon rumah',
            'phone' => 'Nomor HP peserta',
            'parent_name' => 'Nama orang tua / wali',
            'parent_job' => 'Pekerjaan orang tua / wali',
            'parent_address' => 'Alamat orang tua / wali',
            'parent_city' => 'Kota orang tua / wali',
            'parent_postal_code' => 'Kode pos orang tua / wali',
            'parent_home_phone' => 'Telepon rumah orang tua / wali',
            'parent_phone' => 'Nomor HP orang tua / wali',
            'formal_education' => 'Pendidikan formal',
            'formal_education.*.level' => 'Jenjang pendidikan',
            'formal_education.*.school_name' => 'Nama sekolah',
            'formal_education.*.city' => 'Kota sekolah',
            'formal_education.*.graduation_year' => 'Tahun lulus',
            'formal_education.*.notes' => 'Keterangan pendidikan',
            'doc_kta' => 'Scan KTA',
            'doc_bpjs' => 'Scan BPJS',
            'doc_health_certificate' => 'Scan Surat Keterangan Sehat',
            'doc_birth_certificate' => 'Scan Akta Kelahiran',
            'doc_family_card' => 'Scan Kartu Keluarga (KK)',
            'doc_sku' => 'Scan SKU',
            'doc_skk' => 'Scan SKK',
        ];
    }

    /**
     * Formulir edit data peserta
     */
    public function participantEdit(Request $request, string $id): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroup) {
            $q->where('scout_group_id', $scoutGroup->id);
        })->with('patrol')->findOrFail($id);

        $patrol = $participant->patrol;
        $formalEducation = $participant->formal_education ?: Participant::defaultFormalEducation();
        $scoutSiaga = $participant->scout_siaga ?: Participant::defaultScoutSiaga();
        $scoutPenggalang = $participant->scout_penggalang ?: Participant::defaultScoutPenggalang();

        return view('portal.scout-group.participants.edit', compact('scoutGroup', 'patrol', 'participant', 'formalEducation', 'scoutSiaga', 'scoutPenggalang'));
    }

    /**
     * Perbarui data peserta
     */
    public function participantUpdate(Request $request, string $id): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroup) {
            $q->where('scout_group_id', $scoutGroup->id);
        })->with('patrol')->findOrFail($id);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci dan data peserta tidak dapat diubah.']);
        }

        $validated = $request->validate($this->participantRules($participant), $this->participantMessages(), $this->participantAttributes());

        $patrol = $participant->patrol;

        // Validasi Pinru / Wapinru unik
        if ($validated['role_in_patrol'] === PatrolRoleEnum::PatrolLeader->value) {
            $otherLeader = $patrol->participants()->where('role_in_patrol', PatrolRoleEnum::PatrolLeader->value)->where('id', '!=', $participant->id)->first();
            if ($otherLeader) {
                return back()->withInput()->withErrors(['role_in_patrol' => 'Regu ini sudah memiliki Pimpinan Regu (Pinru).']);
            }
        }

        if ($validated['role_in_patrol'] === PatrolRoleEnum::AssistantPatrolLeader->value) {
            $otherAssistant = $patrol->participants()->where('role_in_patrol', PatrolRoleEnum::AssistantPatrolLeader->value)->where('id', '!=', $participant->id)->first();
            if ($otherAssistant) {
                return back()->withInput()->withErrors(['role_in_patrol' => 'Regu ini sudah memiliki Wakil Pimpinan Regu (Wapinru).']);
            }
        }

        $data = $validated;
        unset($data['photo']);

        // Nomor gudep dan pangkalan riwayat Penggalang selalu mengikuti data gugus depan (tidak dapat diubah)
        $data['scout_penggalang'] = array_merge($data['scout_penggalang'] ?? [], [
            'group_number' => $patrol->group_number,
            'pangkalan' => $scoutGroup->school_name,
        ]);

        if ($request->hasFile('photo')) {
            if ($participant->photo_path && Storage::disk('public')->exists($participant->photo_path)) {
                Storage::disk('public')->delete($participant->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('participants', 'public');
        }

        // Perbarui berkas persyaratan peserta jika diunggah berkas baru
        foreach (Participant::DOCUMENTS as $inputKey => [$column, $label]) {
            unset($data[$inputKey]);
            if ($request->hasFile($inputKey)) {
                if ($participant->$column && Storage::disk('public')->exists($participant->$column)) {
                    Storage::disk('public')->delete($participant->$column);
                }
                $data[$column] = $request->file($inputKey)->store('participants/documents', 'public');
            }
        }

        $participant->update($data);

        return redirect()->route('scout-group.patrols')->with('success', 'Data peserta berhasil diperbarui.');
    }

    /**
     * Cetak formulir resmi biodata peserta (FORM : 02) (PDF)
     */
    public function participantPrint(Request $request, string $id): \Illuminate\Http\Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroup) {
            $q->where('scout_group_id', $scoutGroup->id);
        })->with('patrol')->findOrFail($id);

        $filename = 'Form_02_Biodata_' . \Illuminate\Support\Str::slug($participant->full_name) . '.pdf';

        return $this->pdf->response(
            'portal.scout-group.participants.print',
            compact('scoutGroup', 'participant'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Hapus peserta dari regu
     */
    public function participantDestroy(Request $request, string $id): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci dan data peserta tidak dapat dihapus.']);
        }

        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroup) {
            $q->where('scout_group_id', $scoutGroup->id);
        })->findOrFail($id);

        if ($participant->photo_path && Storage::disk('public')->exists($participant->photo_path)) {
            Storage::disk('public')->delete($participant->photo_path);
        }

        foreach (Participant::DOCUMENTS as $inputKey => [$column, $label]) {
            if ($participant->$column && Storage::disk('public')->exists($participant->$column)) {
                Storage::disk('public')->delete($participant->$column);
            }
        }

        $participant->delete();

        return back()->with('success', 'Peserta berhasil dihapus dari regu.');
    }

    /**
     * Kelola Pembina Pendamping (Bindamping)
     */
    public function chaperones(Request $request): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $scoutGroup->load('chaperones');

        return view('portal.scout-group.chaperones.index', compact('scoutGroup'));
    }

    /**
     * Formulir pendaftaran biodata lengkap pembina pendamping
     */
    public function chaperoneCreate(Request $request): View|RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return redirect()->route('scout-group.chaperones')
                ->withErrors(['status' => 'Pendaftaran terkunci dan data tidak dapat diubah.']);
        }

        $gender = $request->query('gender', 'male');
        if (! in_array($gender, ['male', 'female'])) {
            $gender = 'male';
        }

        // Regu wajib ada lebih dulu; bindamping mengikuti regu putra/putri
        if (! $scoutGroup->canRegisterChaperone($gender)) {
            return redirect()->route($scoutGroup->patrols()->exists() ? 'scout-group.chaperones' : 'scout-group.patrols')
                ->withErrors(['gender' => ScoutGroup::chaperoneRequiresPatrolMessage($gender)]);
        }

        $defaultEducation = Chaperone::defaultFormalEducation();
        $defaultExperiences = Chaperone::defaultScoutExperiences();
        $defaultTrainings = Chaperone::defaultScoutTrainings();

        return view('portal.scout-group.chaperones.create', compact(
            'scoutGroup',
            'gender',
            'defaultEducation',
            'defaultExperiences',
            'defaultTrainings'
        ));
    }

    /**
     * Simpan data bindamping baru sesuai format resmi
     */
    public function chaperoneStore(Request $request): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci.']);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', 'string', 'max:50'],
            'blood_type' => ['required', \App\Support\BloodType::rule()],
            'scout_id' => ['required', 'string', 'max:50'],
            'national_identity_number' => ['nullable', 'string', 'max:30'],
            'home_address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:30'],
            'photo' => ['required', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_experiences' => ['nullable', 'array'],
            'scout_trainings' => ['nullable', 'array'],
            'other_trainings' => ['nullable', 'array'],
        ]);

        if (! $scoutGroup->canRegisterChaperone($validated['gender'])) {
            return back()->withInput()->withErrors(['gender' => ScoutGroup::chaperoneRequiresPatrolMessage($validated['gender'])]);
        }

        if ($scoutGroup->chaperones()->where('gender', $validated['gender'])->exists()) {
            $label = $validated['gender'] === 'male' ? 'Pembina Pendamping Putra' : 'Pembina Pendamping Putri';
            return back()->withInput()->withErrors(['gender' => "{$label} sudah terdaftar. Setiap pangkalan maksimal mendaftarkan 1 Bindamping Putra dan 1 Bindamping Putri."]);
        }

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('chaperones', 'public');
        }
        unset($validated['photo']);

        $scoutGroup->chaperones()->create(array_merge($validated, [
            'status' => RegistrationStatusEnum::Draft,
        ]));

        $label = $validated['gender'] === 'male' ? 'Bindamping Putra' : 'Bindamping Putri';

        return redirect()->route('scout-group.chaperones')->with('success', "Formulir biodata resmi {$label} berhasil disimpan.");
    }

    /**
     * Formulir edit biodata pembina pendamping
     */
    public function chaperoneEdit(Request $request, string $id): View|RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return redirect()->route('scout-group.chaperones')
                ->withErrors(['status' => 'Pendaftaran terkunci dan data tidak dapat diubah.']);
        }

        $chaperone = $scoutGroup->chaperones()->findOrFail($id);

        $defaultEducation = !empty($chaperone->formal_education)
            ? $chaperone->formal_education
            : Chaperone::defaultFormalEducation();

        $defaultExperiences = !empty($chaperone->scout_experiences)
            ? $chaperone->scout_experiences
            : Chaperone::defaultScoutExperiences();

        $defaultTrainings = !empty($chaperone->scout_trainings)
            ? $chaperone->scout_trainings
            : Chaperone::defaultScoutTrainings();

        $otherTrainings = $chaperone->other_trainings ?? [];

        return view('portal.scout-group.chaperones.edit', compact(
            'scoutGroup',
            'chaperone',
            'defaultEducation',
            'defaultExperiences',
            'defaultTrainings',
            'otherTrainings'
        ));
    }

    /**
     * Perbarui data bindamping
     */
    public function chaperoneUpdate(Request $request, string $id): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci.']);
        }

        $chaperone = $scoutGroup->chaperones()->findOrFail($id);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', 'string', 'max:50'],
            'blood_type' => ['required', \App\Support\BloodType::rule()],
            'scout_id' => ['required', 'string', 'max:50'],
            'national_identity_number' => ['nullable', 'string', 'max:30'],
            'home_address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:30'],
            'photo' => [$chaperone->photo_path ? 'nullable' : 'required', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_experiences' => ['nullable', 'array'],
            'scout_trainings' => ['nullable', 'array'],
            'other_trainings' => ['nullable', 'array'],
        ]);

        // Kategori bindamping tidak dapat diubah: selalu mengikuti data tersimpan
        $validated['gender'] = $chaperone->gender;

        if ($validated['gender'] !== $chaperone->gender && $scoutGroup->chaperones()->where('gender', $validated['gender'])->where('id', '!=', $chaperone->id)->exists()) {
            $label = $validated['gender'] === 'male' ? 'Pembina Pendamping Putra' : 'Pembina Pendamping Putri';
            return back()->withInput()->withErrors(['gender' => "{$label} sudah terdaftar."]);
        }

        if ($request->hasFile('photo')) {
            if ($chaperone->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($chaperone->photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($chaperone->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('chaperones', 'public');
        }
        unset($validated['photo']);

        $chaperone->update($validated);

        return redirect()->route('scout-group.chaperones')->with('success', 'Biodata resmi pembina pendamping berhasil diperbarui.');
    }

    /**
     * Cetak lembar formulir biodata resmi pembina pendamping (FORM : 03) (PDF)
     */
    public function chaperonePrint(Request $request, string $id): \Illuminate\Http\Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $chaperone = $scoutGroup->chaperones()->findOrFail($id);
        $chaperone->setRelation('scoutGroup', $scoutGroup);

        $filename = 'Form_03_Biodata_' . \Illuminate\Support\Str::slug($chaperone->full_name) . '.pdf';

        return $this->pdf->response(
            'portal.scout-group.chaperones.print',
            compact('scoutGroup', 'chaperone'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Cetak dokumen resmi Bukti Registrasi Kontingen Gugusdepan (PDF)
     */
    public function registrationCertificatePrint(Request $request): \Illuminate\Http\Response
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        abort_unless($scoutGroup->canDownloadDocuments(), 403, ScoutGroup::DOWNLOAD_LOCKED_MESSAGE);

        $scoutGroup->load(['patrols.participants', 'chaperones', 'user']);
        $filename = 'Bukti_Registrasi_' . \Illuminate\Support\Str::slug($scoutGroup->school_name) . '.pdf';

        return $this->pdf->response(
            'admin.scout-groups.registration-certificate',
            compact('scoutGroup'),
            $filename,
            $request->boolean('download')
        );
    }

    /**
     * Hapus bindamping
     */
    public function chaperoneDestroy(Request $request, string $id): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran terkunci.']);
        }

        $chaperone = $scoutGroup->chaperones()->findOrFail($id);

        if ($chaperone->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($chaperone->photo_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($chaperone->photo_path);
        }

        $chaperone->delete();

        return back()->with('success', 'Bindamping berhasil dihapus.');
    }

    /**
     * Halaman pemeriksaan ulang (Review) sebelum pengajuan akhir
     */
    public function review(Request $request): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $scoutGroup->load(['patrols.participants', 'chaperones']);

        return view('portal.scout-group.review', compact('scoutGroup'));
    }

    /**
     * Pengajuan akhir pendaftaran kontingen (Submit)
     */
    public function submit(Request $request): RedirectResponse
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);

        if (! $scoutGroup->isEditable()) {
            return back()->withErrors(['status' => 'Pendaftaran sudah pernah diajukan sebelumnya.']);
        }

        // Pengecekan kelengkapan minimal: Wajib punya minimal 1 regu dan bindamping
        if ($scoutGroup->patrols()->count() === 0) {
            return back()->withErrors(['error' => 'Gugus Depan wajib membentuk minimal 1 regu sebelum mengajukan pendaftaran.']);
        }

        // Bindamping wajib sesuai regu: regu putra -> bindamping putra, regu putri -> bindamping putri
        foreach ($scoutGroup->requiredChaperoneGenders() as $gender) {
            if (! $scoutGroup->chaperones()->where('gender', $gender)->exists()) {
                $label = $gender === 'male' ? 'Putra' : 'Putri';

                return back()->withErrors(['error' => "Regu {$label} sudah terdaftar, sehingga Gugus Depan wajib mendaftarkan Pembina Pendamping (Bindamping) {$label}."]);
            }
        }

        // Susunan regu: tepat 8 peserta serta Pinru dan Wapinru
        if ($problems = $scoutGroup->patrolRosterProblems()) {
            return back()->withErrors(['error' => implode(' ', $problems)]);
        }

        // Validasi unggah berkas pangkalan jika disertakan
        $request->validate([
            'mandate_letter' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'mandate_letter.file' => 'Berkas Surat Tugas / Mandat harus valid.',
            'mandate_letter.mimes' => 'Surat Tugas / Mandat harus berformat PDF.',
            'mandate_letter.max' => 'Ukuran Surat Tugas / Mandat maksimal 5MB.',
            'payment_proof.file' => 'Berkas Bukti Bayar harus valid.',
            'payment_proof.mimes' => 'Bukti Bayar harus berformat PDF, JPG, atau PNG.',
            'payment_proof.max' => 'Ukuran Bukti Bayar maksimal 5MB.',
        ]);

        $groupUpdates = [
            'verification_status' => RegistrationStatusEnum::Submitted,
        ];

        if ($request->hasFile('mandate_letter')) {
            if ($scoutGroup->mandate_letter_path && Storage::disk('public')->exists($scoutGroup->mandate_letter_path)) {
                Storage::disk('public')->delete($scoutGroup->mandate_letter_path);
            }
            $groupUpdates['mandate_letter_path'] = $request->file('mandate_letter')->store('scout-groups/documents', 'public');
        }

        if ($request->hasFile('payment_proof')) {
            if ($scoutGroup->payment_proof_path && Storage::disk('public')->exists($scoutGroup->payment_proof_path)) {
                Storage::disk('public')->delete($scoutGroup->payment_proof_path);
            }
            $groupUpdates['payment_proof_path'] = $request->file('payment_proof')->store('scout-groups/documents', 'public');
        }

        // Kunci status menjadi Submitted
        $scoutGroup->update($groupUpdates);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::Submitted]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Submitted]);
        $scoutGroup->chaperones()->update(['status' => RegistrationStatusEnum::Submitted]);

        return redirect()->route('scout-group.index')->with('success', 'Pendaftaran kontingen berhasil diajukan dan sedang menunggu verifikasi Administrator.');
    }
}
