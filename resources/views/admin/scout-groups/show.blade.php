<x-layouts::app title="Detail Verifikasi Kontingen - Admin">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-official-header />

        {{-- Top Breadcrumb & Status Navigation --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.scout-groups.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white p-2 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">
                            {{ $scoutGroup->registration_code }}
                        </span>
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $scoutGroup->verification_status?->badgeClasses() }}">
                            {{ $scoutGroup->verification_status?->label() }}
                        </span>
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-2xl">
                        {{ $scoutGroup->school_name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($scoutGroup->canDownloadDocuments())
                <a href="{{ route('admin.scout-groups.id-cards', $scoutGroup->id) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <flux:icon name="qr-code" class="size-4 text-emerald-600" />
                    <span>ID Card Kontingen (.ZIP / .PNG)</span>
                </a>
                @endif

                @if($scoutGroup->verified_by && $scoutGroup->verifier)
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-right text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-400">
                        Diverifikasi oleh: <strong class="text-zinc-900 dark:text-zinc-200">{{ $scoutGroup->verifier->name }}</strong>
                        <div class="text-[10px] text-zinc-400">{{ $scoutGroup->verified_at?->translatedFormat('d M Y, H:i') }} WIB</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Revision Notes Alert --}}
        @if($scoutGroup->revision_notes)
            <div class="rounded-2xl border border-orange-300 bg-orange-50/70 p-4 text-xs text-orange-900 dark:border-orange-500/30 dark:bg-orange-950/40 dark:text-orange-200">
                <div class="font-bold">Catatan Perbaikan / Alasan Penolakan:</div>
                <p class="mt-1 leading-relaxed">{{ $scoutGroup->revision_notes }}</p>
            </div>
        @endif

        {{-- School Profile Details --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Informasi Pangkalan Gugus Depan</h2>
            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 text-xs">
                <div>
                    <span class="text-zinc-400">Nomor Gugus Depan:</span>
                    <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->group_number }}</p>
                </div>
                <div>
                    <span class="text-zinc-400">Penanggung Jawab (PIC):</span>
                    <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->contact_person }}</p>
                </div>
                <div>
                    <span class="text-zinc-400">Nomor Telepon / WA:</span>
                    <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->contact_phone }}</p>
                </div>
                <div>
                    <span class="text-zinc-400">Alamat Pangkalan:</span>
                    <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->address }}</p>
                </div>
            </div>
        </div>

        {{-- Patrols & Participants Sections --}}
        <div class="space-y-6">
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                Daftar Regu & Peserta Penggalang ({{ $scoutGroup->patrols->count() }} Regu)
            </h2>

            @forelse($scoutGroup->patrols as $patrol)
                <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-col justify-between gap-2 border-b border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50 sm:flex-row sm:items-center">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $patrol->isBoys() ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                {{ $patrol->gender_category?->label() }}
                            </span>
                            <span class="rounded-md font-mono text-xs font-bold border border-zinc-200 bg-white px-2 py-0.5 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                Gudep {{ $patrol->group_number }}
                            </span>
                            <h3 class="font-bold text-zinc-900 dark:text-zinc-100">
                                Regu {{ $patrol->patrol_name }}
                            </h3>
                            <span class="text-xs text-zinc-400">({{ $patrol->participants->count() }} Peserta)</span>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($scoutGroup->canDownloadDocuments())
                            <a href="{{ route('admin.scout-groups.patrols.id-cards', [$scoutGroup->id, $patrol->id]) }}" target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                <flux:icon name="qr-code" class="size-3.5" />
                                ID Card Regu (.ZIP / .PNG)
                            </a>
                            @endif
                            @if($scoutGroup->canDownloadDocuments())
                            <a href="{{ route('admin.scout-groups.patrols.form-01', [$scoutGroup->id, $patrol->id]) }}" target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-amber-800 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-300">
                                <flux:icon name="printer" class="size-3.5" />
                                Cetak FORM : 01
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                                <tr>
                                    <th class="px-5 py-2.5 font-semibold">Nama Peserta</th>
                                    <th class="px-5 py-2.5 font-semibold">Peran di Regu</th>
                                    <th class="px-5 py-2.5 font-semibold">NTA Pramuka</th>
                                    <th class="px-5 py-2.5 font-semibold">Tempat, Tgl Lahir</th>
                                    <th class="px-5 py-2.5 font-semibold">Gol. Darah</th>
                                    <th class="px-5 py-2.5 font-semibold text-right">Biodata</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @forelse($patrol->participants as $participant)
                                    <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                                        <td class="px-5 py-2.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $participant->full_name }}
                                            <div class="mt-0.5"><x-id-badge :value="$participant->id_number" /></div>
                                        </td>
                                        <td class="px-5 py-2.5">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $participant->role_in_patrol?->badgeClasses() }}">
                                                {{ $participant->role_in_patrol?->label() }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-400">
                                            {{ $participant->scout_id ?? '-' }}
                                        </td>
                                        <td class="px-5 py-2.5 text-zinc-600 dark:text-zinc-400">
                                            {{ $participant->birth_place }}, {{ $participant->birth_date?->format('d/m/Y') }}
                                        </td>
                                        <td class="px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-400">
                                            {{ $participant->blood_type ?? '-' }}
                                        </td>
                                        <td class="px-5 py-2.5 text-right">
                                            <div class="inline-flex items-center justify-end gap-1.5">
                                                @if($scoutGroup->canDownloadDocuments())
                                                <a href="{{ route('admin.scout-groups.participants.print', [$scoutGroup->id, $participant->id]) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-400">
                                                    <flux:icon name="printer" class="size-3.5" />
                                                    Form
                                                </a>
                                                @endif
                                                @if($scoutGroup->canDownloadDocuments())
                                                <a href="{{ route('digital-cards.participant', $participant->id) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                                    <flux:icon name="qr-code" class="size-3.5" />
                                                    Kartu
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-6 text-center text-zinc-400">
                                            Belum ada data anggota peserta dalam regu ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 text-center text-xs text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900">
                    Pangkalan ini belum mendaftarkan regu.
                </div>
            @endforelse
        </div>

        {{-- Chaperones Section --}}
        <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-xs sm:text-sm">
                    Pembina Pendamping (Bindamping) &bull; {{ $scoutGroup->chaperones->count() }} Orang
                </h3>
                @if($scoutGroup->chaperones->count() > 0)
                    @if($scoutGroup->canDownloadDocuments())
                    <a href="{{ route('admin.scout-groups.chaperones.id-cards', $scoutGroup->id) }}" target="_blank"
                       class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        <flux:icon name="qr-code" class="size-3.5" />
                        ID Card Bindamping (.ZIP / .PNG)
                    </a>
                    @endif
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-5 py-2.5 font-semibold">Nama Lengkap</th>
                            <th class="px-5 py-2.5 font-semibold">Jenis Kelamin</th>
                            <th class="px-5 py-2.5 font-semibold">NTA Pramuka</th>
                            <th class="px-5 py-2.5 font-semibold">No. HP / WA</th>
                            <th class="px-5 py-2.5 font-semibold text-right">Formulir Biodata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($scoutGroup->chaperones as $chaperone)
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-2.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $chaperone->full_name }}
                                    <div class="mt-0.5"><x-id-badge :value="$chaperone->id_number" /></div>
                                </td>
                                <td class="px-5 py-2.5">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $chaperone->gender === 'male' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                        {{ $chaperone->gender === 'male' ? 'Bindamping Putra' : 'Bindamping Putri' }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-400">
                                    {{ $chaperone->scout_id ?? '-' }}
                                </td>
                                <td class="px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-400">
                                    {{ $chaperone->phone_number }}
                                </td>
                                <td class="px-5 py-2.5 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @if($scoutGroup->canDownloadDocuments())
                                        <a href="{{ route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]) }}" target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-amber-400">
                                            <flux:icon name="printer" class="size-3.5" />
                                            Form
                                        </a>
                                        @endif
                                        @if($scoutGroup->canDownloadDocuments())
                                        <a href="{{ route('digital-cards.chaperone', $chaperone->id) }}" target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                            <flux:icon name="qr-code" class="size-3.5" />
                                            Kartu
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-zinc-400">
                                    Belum ada pembina pendamping yang didaftarkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Verification Actions Panel (Admin Only) --}}
        @if(auth()->user()?->isAdmin())
            <div class="rounded-2xl border border-zinc-300 bg-gradient-to-br from-zinc-50 to-amber-50/40 p-6 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-900/60">
                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    Aksi Keputusan Verifikasi Administrator
                </h2>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Tindakan ini akan mengunci atau membuka akses perbaikan bagi Gugus Depan, serta memicu kesiapan penerbitan kartu digital dan QR Code peserta.
                </p>

                <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {{-- Action 1: Approve / Verify --}}
                    <div class="rounded-xl border border-emerald-200 bg-white p-4 shadow-sm dark:border-emerald-800/40 dark:bg-zinc-800/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui Berkas Kontingen
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Nyatakan berkas pendaftaran pangkalan ini valid, lengkap, dan berhak mengikuti seluruh mata lomba LT III.
                        </p>
                        <form method="POST" action="{{ route('admin.scout-groups.verify', $scoutGroup->id) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran kontingen {{ $scoutGroup->school_name }}?');"
                                    class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                                Setujui & Sahkan Kontingen
                            </button>
                        </form>
                    </div>

                    {{-- Action 2: Request Revision --}}
                    <div class="rounded-xl border border-orange-200 bg-white p-4 shadow-sm dark:border-orange-800/40 dark:bg-zinc-800/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-orange-700 dark:text-orange-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Minta Perbaikan Berkas
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Kembalikan formulir ke status Draft agar pangkalan dapat melengkapi data yang kurang.
                        </p>
                        <form method="POST" action="{{ route('admin.scout-groups.revision', $scoutGroup->id) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="revision_notes"
                                      rows="2"
                                      required
                                      placeholder="Tuliskan catatan kekurangan berkas / data..."
                                      class="w-full rounded-lg border border-zinc-300 bg-zinc-50 p-2 text-xs text-zinc-900 focus:border-orange-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">{{ old('revision_notes', $scoutGroup->revision_notes) }}</textarea>
                            <button type="submit"
                                    class="w-full rounded-lg bg-orange-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-orange-700">
                                Kirim Catatan Perbaikan
                            </button>
                        </form>
                    </div>

                    {{-- Action 3: Reject --}}
                    <div class="rounded-xl border border-red-200 bg-white p-4 shadow-sm dark:border-red-800/40 dark:bg-zinc-800/80">
                        <div class="flex items-center gap-2 text-xs font-bold text-red-700 dark:text-red-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tolak Pendaftaran
                        </div>
                        <p class="mt-1 text-[11px] text-zinc-500">
                            Pendaftaran ditolak secara permanen jika pangkalan tidak memenuhi syarat kriteria kepesertaan.
                        </p>
                        <form method="POST" action="{{ route('admin.scout-groups.reject', $scoutGroup->id) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="revision_notes"
                                      rows="2"
                                      required
                                      placeholder="Alasan penolakan pendaftaran..."
                                      class="w-full rounded-lg border border-zinc-300 bg-zinc-50 p-2 text-xs text-zinc-900 focus:border-red-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"></textarea>
                            <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin MENOLAK pendaftaran kontingen ini?');"
                                    class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700">
                                Tolak Pendaftaran
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
