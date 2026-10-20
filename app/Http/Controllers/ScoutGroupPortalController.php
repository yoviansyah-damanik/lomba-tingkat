<?php

namespace App\Http\Controllers;

use App\Enums\PatrolGenderEnum;
use App\Enums\PatrolRoleEnum;
use App\Enums\RegistrationStatusEnum;
use App\Models\Chaperone;
use App\Models\Participant;
use App\Models\Patrol;
use App\Models\ScoutGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk portal pendaftaran kontingen Gugus Depan
 */
class ScoutGroupPortalController extends Controller
{
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

        return view('portal.scout-group.profile', compact('scoutGroup'));
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

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'group_number' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
        ]);

        $groupNumberData = ScoutGroup::formatGroupNumbers($validated['group_number']);

        $scoutGroup->update([
            'school_name' => $validated['school_name'],
            'group_number' => $groupNumberData['combined'],
            'male_group_number' => $groupNumberData['male'],
            'female_group_number' => $groupNumberData['female'],
            'address' => $validated['address'],
            'contact_person' => $validated['contact_person'],
            'contact_phone' => $validated['contact_phone'],
        ]);

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
     * Cetak formulir rekap pendaftaran kontingen (FORM : 01)
     */
    public function form01Print(Request $request, string $patrolId): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $scoutGroup->load(['chaperones', 'patrols']);
        $patrol = $scoutGroup->patrols()->with('participants')->findOrFail($patrolId);
        $chaperone = $patrol->chaperone();
        $orderedParticipants = $patrol->orderedParticipants();

        return view('portal.scout-group.patrols.form-01', compact('scoutGroup', 'patrol', 'chaperone', 'orderedParticipants'));
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

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'role_in_patrol' => ['required', 'in:patrol_leader,assistant_patrol_leader,member'],
            'gender' => ['required', 'in:male,female'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_type' => ['nullable', 'in:A,B,AB,O'],
            'home_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_job' => ['nullable', 'string', 'max:100'],
            'parent_address' => ['nullable', 'string'],
            'parent_city' => ['nullable', 'string', 'max:100'],
            'parent_postal_code' => ['nullable', 'string', 'max:10'],
            'parent_home_phone' => ['nullable', 'string', 'max:30'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_siaga' => ['nullable', 'array'],
            'scout_penggalang' => ['nullable', 'array'],
        ]);

        // Validasi: 1 regu hanya boleh punya 1 Pinru dan 1 Wapinru
        if ($validated['role_in_patrol'] === PatrolRoleEnum::PatrolLeader->value && $patrol->hasLeader()) {
            return back()->withInput()->withErrors(['role_in_patrol' => 'Regu ini sudah memiliki Pimpinan Regu (Pinru).']);
        }

        if ($validated['role_in_patrol'] === PatrolRoleEnum::AssistantPatrolLeader->value && $patrol->hasAssistantLeader()) {
            return back()->withInput()->withErrors(['role_in_patrol' => 'Regu ini sudah memiliki Wakil Pimpinan Regu (Wapinru).']);
        }

        $data = $validated;
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('participants', 'public');
        }

        $patrol->participants()->create(array_merge($data, [
            'status' => RegistrationStatusEnum::Draft,
        ]));

        return redirect()->route('scout-group.patrols')->with('success', 'Peserta berhasil didaftarkan ke dalam regu.');
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

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'role_in_patrol' => ['required', 'in:patrol_leader,assistant_patrol_leader,member'],
            'gender' => ['required', 'in:male,female'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_type' => ['nullable', 'in:A,B,AB,O'],
            'home_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_job' => ['nullable', 'string', 'max:100'],
            'parent_address' => ['nullable', 'string'],
            'parent_city' => ['nullable', 'string', 'max:100'],
            'parent_postal_code' => ['nullable', 'string', 'max:10'],
            'parent_home_phone' => ['nullable', 'string', 'max:30'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_siaga' => ['nullable', 'array'],
            'scout_penggalang' => ['nullable', 'array'],
        ]);

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

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('participants', 'public');
        }

        $participant->update($data);

        return redirect()->route('scout-group.patrols')->with('success', 'Data peserta berhasil diperbarui.');
    }

    /**
     * Cetak formulir resmi biodata peserta
     */
    public function participantPrint(Request $request, string $id): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $participant = Participant::whereHas('patrol', function ($q) use ($scoutGroup) {
            $q->where('scout_group_id', $scoutGroup->id);
        })->with('patrol')->findOrFail($id);

        return view('portal.scout-group.participants.print', compact('scoutGroup', 'participant'));
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
    public function chaperoneCreate(Request $request): View
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
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'national_identity_number' => ['nullable', 'string', 'max:30'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_experiences' => ['nullable', 'array'],
            'scout_trainings' => ['nullable', 'array'],
            'other_trainings' => ['nullable', 'array'],
        ]);

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
    public function chaperoneEdit(Request $request, string $id): View
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
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:50'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'scout_id' => ['nullable', 'string', 'max:50'],
            'national_identity_number' => ['nullable', 'string', 'max:30'],
            'home_address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['required', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'formal_education' => ['nullable', 'array'],
            'scout_experiences' => ['nullable', 'array'],
            'scout_trainings' => ['nullable', 'array'],
            'other_trainings' => ['nullable', 'array'],
        ]);

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
     * Cetak lembar formulir biodata resmi pembina pendamping
     */
    public function chaperonePrint(Request $request, string $id): View
    {
        $scoutGroup = $this->getOrCreateScoutGroup($request);
        $chaperone = $scoutGroup->chaperones()->findOrFail($id);

        return view('portal.scout-group.chaperones.print', compact('scoutGroup', 'chaperone'));
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

        if ($scoutGroup->chaperones()->count() === 0) {
            return back()->withErrors(['error' => 'Gugus Depan wajib mendaftarkan minimal 1 Pembina Pendamping (Bindamping).']);
        }

        // Kunci status menjadi Submitted
        $scoutGroup->update([
            'verification_status' => RegistrationStatusEnum::Submitted,
        ]);

        $scoutGroup->patrols()->update(['status' => RegistrationStatusEnum::Submitted]);
        Participant::whereIn('patrol_id', $scoutGroup->patrols()->pluck('id'))
            ->update(['status' => RegistrationStatusEnum::Submitted]);
        $scoutGroup->chaperones()->update(['status' => RegistrationStatusEnum::Submitted]);

        return redirect()->route('scout-group.index')->with('success', 'Pendaftaran kontingen berhasil diajukan dan sedang menunggu verifikasi Administrator.');
    }
}
