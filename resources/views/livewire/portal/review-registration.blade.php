<div class="flex h-full w-full flex-1 flex-col gap-6">
    <x-official-header />

    <x-heading-section
        title="Pemeriksaan Ulang Berkas Kontingen"
        description="Periksa kelengkapan seluruh data pangkalan, regu, anggota peserta, dan bindamping sebelum pengajuan akhir."
        badge="Akreditasi Kontingen"
        badge-icon="clipboard-document-check"
        badge-color="amber"
    />

    {{-- Notice / Caution Banner --}}
    <div class="rounded-2xl border border-amber-300/80 bg-amber-50/70 p-5 text-xs text-amber-950 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
        <div class="flex items-start gap-3">
            <svg class="size-5 shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="leading-relaxed">
                <strong>Pemeriksaan Sebelum Pengajuan:</strong> Setelah tombol <em>&ldquo;Konfirmasi & Ajukan Pendaftaran&rdquo;</em> ditekan, data kontingen akan dikunci dan masuk ke antrean verifikasi Administrator Kwarcab. Anda tidak dapat melakukan penambahan atau perubahan data kecuali Administrator mengembalikan berkas untuk perbaikan.
            </div>
        </div>
    </div>

    {{-- Readiness Checklist Card (Livewire 4 Reactive) --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">
            Status Kelengkapan Persyaratan Berkas
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['is_profile_complete'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20' }} p-3 text-xs">
                @if($readiness['is_profile_complete'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Profil Pangkalan Lengkap</span>
                @else
                    <flux:icon name="exclamation-circle" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                    <span class="font-medium text-amber-900 dark:text-amber-200">Profil Belum Lengkap</span>
                @endif
            </div>

            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['has_patrol'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20' }} p-3 text-xs">
                @if($readiness['has_patrol'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Minimal 1 Regu Terdaftar</span>
                @else
                    <flux:icon name="exclamation-circle" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                    <span class="font-medium text-amber-900 dark:text-amber-200">Belum Ada Regu</span>
                @endif
            </div>

            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['has_chaperone'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20' }} p-3 text-xs">
                @if($readiness['has_chaperone'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Minimal 1 Bindamping Terdaftar</span>
                @else
                    <flux:icon name="exclamation-circle" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                    <span class="font-medium text-amber-900 dark:text-amber-200">Belum Ada Bindamping</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 1. Identitas Gugus Depan --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">1. Data Pangkalan Gugus Depan</h3>
            @if($scoutGroup->isEditable())
                <a href="{{ route('scout-group.profile') }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:underline dark:text-amber-400">
                    Ubah Data &rarr;
                </a>
            @endif
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
            <div>
                <span class="text-zinc-400 block">Nama Pangkalan:</span>
                <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">{{ $scoutGroup->school_name }}</span>
            </div>
            <div>
                <span class="text-zinc-400 block">Nomor Gugus Depan:</span>
                <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">{{ $scoutGroup->group_number }}</span>
            </div>
            <div>
                <span class="text-zinc-400 block">Penanggung Jawab (PIC):</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->contact_person }} ({{ $scoutGroup->contact_phone }})</span>
            </div>
            <div>
                <span class="text-zinc-400 block">Alamat Pangkalan:</span>
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->address }}</span>
            </div>
        </div>
    </div>

    {{-- 2. Data Regu & Peserta --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">2. Data Regu & Peserta ({{ $scoutGroup->patrols->count() }} Regu)</h3>
            @if($scoutGroup->isEditable())
                <a href="{{ route('scout-group.patrols') }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:underline dark:text-amber-400">
                    Kelola Regu &rarr;
                </a>
            @endif
        </div>

        <div class="mt-4 space-y-6">
            @forelse($scoutGroup->patrols as $patrol)
                <div class="rounded-xl border border-zinc-200/60 p-4 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Regu {{ $patrol->patrol_name }}</span>
                            <span class="text-xs text-zinc-400">({{ $patrol->gender_category?->label() }})</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('scout-group.patrols.form-01', $patrol->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-amber-800 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-300">
                                <flux:icon name="printer" class="size-3.5" />
                                Cetak FORM : 01
                            </a>
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                                {{ $patrol->participantCount() }} Peserta
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-zinc-100 bg-zinc-50 text-zinc-500 uppercase dark:border-zinc-800 dark:bg-zinc-800/40">
                                <tr>
                                    <th class="py-2 px-2.5">No</th>
                                    <th class="py-2 px-2.5">Nama</th>
                                    <th class="py-2 px-2.5">Jabatan</th>
                                    <th class="py-2 px-2.5">NTA Pramuka</th>
                                    <th class="py-2 px-2.5">Tempat, Tanggal Lahir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse($patrol->participants as $idx => $participant)
                                    <tr>
                                        <td class="py-2 px-2.5 text-zinc-400">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-2.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $participant->full_name }}</td>
                                        <td class="py-2 px-2.5">
                                            <span class="rounded bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                {{ $participant->role_in_patrol?->label() }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2.5 font-mono text-zinc-500">{{ $participant->scout_id ?: '-' }}</td>
                                        <td class="py-2 px-2.5 text-zinc-500">{{ $participant->birth_place }}, {{ $participant->birth_date?->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-zinc-400">Regu ini belum memiliki anggota peserta.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-xs text-zinc-400 text-center py-4">Belum ada regu yang dibentuk.</p>
            @endforelse
        </div>
    </div>

    {{-- 3. Daftar Bindamping --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">3. Pembina Pendamping ({{ $scoutGroup->chaperones->count() }} Orang)</h3>
            @if($scoutGroup->isEditable())
                <a href="{{ route('scout-group.chaperones') }}" wire:navigate class="text-xs font-semibold text-amber-700 hover:underline dark:text-amber-400">
                    Kelola Bindamping &rarr;
                </a>
            @endif
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            @forelse($scoutGroup->chaperones as $chaperone)
                <div class="rounded-xl border border-zinc-100 bg-zinc-50/50 p-3.5 text-xs dark:border-zinc-800 dark:bg-zinc-800/40">
                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $chaperone->full_name }}</span>
                    <span class="text-zinc-500">{{ $chaperone->gender === 'male' ? 'Pembina Putra' : 'Pembina Putri' }} &bull; HP: {{ $chaperone->phone_number }}</span>
                </div>
            @empty
                <p class="text-xs text-zinc-400 col-span-2 text-center py-3">Belum ada pembina pendamping yang didaftarkan.</p>
            @endforelse
        </div>
    </div>

    {{-- Action Submit Form --}}
    @if($scoutGroup->isEditable())
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Siap Mengajukan Pendaftaran?</h4>
                <p class="text-xs text-zinc-500">Pastikan seluruh data peserta dan bindamping telah lengkap dan valid.</p>
            </div>

            <form method="POST" action="{{ route('scout-group.submit') }}" wire:submit="submitRegistration" onsubmit="return confirm('Apakah Anda yakin data kontingen sudah lengkap dan siap diajukan untuk verifikasi Administrator? Data akan dikunci setelah pengajuan.');">
                @csrf
                <button
                    type="submit"
                    class="rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-amber-600/25 hover:brightness-105 cursor-pointer"
                >
                    Konfirmasi & Ajukan Pendaftaran
                </button>
            </form>
        </div>
    @else
        <div class="rounded-xl border border-zinc-200 bg-zinc-100 p-4 text-center text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">
            Pendaftaran kontingen ini telah diajukan pada status: <strong>{{ $scoutGroup->verification_status?->label() }}</strong>.
        </div>
    @endif
</div>
