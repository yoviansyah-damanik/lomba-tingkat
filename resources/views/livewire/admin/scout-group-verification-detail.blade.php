<div class="flex h-full w-full flex-1 flex-col gap-6"
     x-data="{
         imageViewerOpen: false,
         imageViewerSrc: '',
         imageViewerTitle: '',
         imageZoomScale: 1,
         zoomIn() {
             if (this.imageZoomScale < 3.5) {
                 this.imageZoomScale = +(this.imageZoomScale + 0.25).toFixed(2);
             }
         },
         zoomOut() {
             if (this.imageZoomScale > 0.5) {
                 this.imageZoomScale = +(this.imageZoomScale - 0.25).toFixed(2);
             }
         },
         resetZoom() {
             this.imageZoomScale = 1;
         },
         toggleZoom() {
             this.imageZoomScale = this.imageZoomScale === 1 ? 2 : 1;
         },
         openModal(src, title) {
             this.imageViewerSrc = src;
             this.imageViewerTitle = title || 'Pratinjau Foto';
             this.imageZoomScale = 1;
             this.imageViewerOpen = true;
         },
         closeModal() {
             this.imageViewerOpen = false;
             this.imageViewerSrc = '';
             this.imageZoomScale = 1;
         }
     }"
     @keydown.escape.window="if (imageViewerOpen) closeModal()">
    <x-official-header />

    {{-- Top Breadcrumb & Status Navigation --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('admin.scout-groups.index') }}" variant="subtle" icon="arrow-left" size="sm">
            </flux:button>
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
                <flux:button href="{{ route('admin.scout-groups.id-cards', $scoutGroup->id) }}" target="_blank" variant="subtle" size="sm" icon="qr-code"
                    data-loading="Menyiapkan ID Card Kontingen (.ZIP)...">
                    ID Card Kontingen (.ZIP / .PNG)
                </flux:button>
            @endif

            <flux:button href="{{ route('admin.scout-groups.download-zip', $scoutGroup->id) }}" variant="primary" size="sm" icon="arrow-down-tray"
                data-loading="Mengunduh Semua Berkas Kontingen (.ZIP)...">
                Unduh Semua Berkas (.ZIP)
            </flux:button>

            @if($scoutGroup->verified_at)
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-right text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-400">
                    Diverifikasi oleh: <strong class="text-zinc-900 dark:text-zinc-200">Administrator</strong>
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
                <p class="font-semibold text-zinc-800 dark:text-zinc-200">
                    Putra: {{ $scoutGroup->male_group_number ?: ($scoutGroup->group_number ?: '-') }} &bull;
                    Putri: {{ $scoutGroup->female_group_number ?: ($scoutGroup->group_number ?: '-') }}
                </p>
            </div>
            <div>
                <span class="text-zinc-400">Kwartir Ranting (Kwarran):</span>
                <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->kwarran_name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-zinc-400">Penanggung Jawab (PIC):</span>
                <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->contact_person }} ({{ $scoutGroup->contact_phone }})</p>
            </div>
            <div>
                <span class="text-zinc-400">Alamat Pangkalan:</span>
                <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $scoutGroup->address }}</p>
            </div>
        </div>

        @if ($scoutGroup->registered_ip || $scoutGroup->invitation_id)
            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-1 rounded-xl border border-zinc-200 bg-zinc-50/70 px-3 py-2 text-[11px] text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40">
                <span class="font-semibold uppercase tracking-wider text-zinc-400">Jejak Registrasi</span>
                @if ($scoutGroup->invitation)
                    <span>Kode undangan: <span class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">{{ $scoutGroup->invitation->formattedCode() }}</span></span>
                @endif
                <span>IP: <span class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">{{ $scoutGroup->registered_ip ?: '-' }}</span></span>
                <span class="truncate" title="{{ $scoutGroup->registered_user_agent }}">Perangkat: {{ \Illuminate\Support\Str::limit($scoutGroup->registered_user_agent ?: '-', 70) }}</span>
            </div>
        @endif
    </div>

    {{-- Scout Group Uploaded Documents --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
            <div>
                <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Berkas Unggahan Gugus Depan (Pangkalan)</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Surat tugas gugus depan dan bukti pelunasan biaya kontingen</p>
            </div>
            @php
                $gudepDocsCount = ($scoutGroup->mandate_letter_path ? 1 : 0) + ($scoutGroup->payment_proof_path ? 1 : 0);
                $isMandateImage = in_array(strtolower(pathinfo($scoutGroup->mandate_letter_path ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']);
                $isPaymentImage = in_array(strtolower(pathinfo($scoutGroup->payment_proof_path ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']);
            @endphp
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $gudepDocsCount === 2 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                {{ $gudepDocsCount }}/2 Berkas Terunggah
            </span>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Document 1: Surat Mandat / Tugas --}}
            <div class="flex flex-col justify-between rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Surat Tugas / Mandat Gugus Depan</span>
                        @if($scoutGroup->mandate_letter_path)
                            <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                Terunggah
                            </span>
                        @else
                            <span class="rounded-md bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                Belum Diunggah
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Surat mandat pendelegasian resmi kontingen dari Gugus Depan.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between gap-2">
                    <span class="font-mono text-[11px] text-zinc-400 truncate max-w-[200px]">
                        {{ $scoutGroup->mandate_letter_path ? basename($scoutGroup->mandate_letter_path) : '-' }}
                    </span>
                    @if($scoutGroup->mandate_letter_path)
                        <div class="flex items-center gap-1.5">
                            @if($isMandateImage)
                                <flux:button size="xs" variant="primary" icon="magnifying-glass-plus" @click="openModal('{{ asset('storage/' . $scoutGroup->mandate_letter_path) }}', 'Surat Mandat Gugus Depan - {{ addslashes($scoutGroup->school_name) }}')">
                                    Zoom
                                </flux:button>
                                <flux:button href="{{ asset('storage/' . $scoutGroup->mandate_letter_path) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square">
                                    Buka Berkas
                                </flux:button>
                            @else
                                <x-file-viewer-trigger :path="$scoutGroup->mandate_letter_path" :title="'Surat Mandat Gugus Depan - '.$scoutGroup->school_name" label="Pratinjau Berkas" variant="primary" />
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Document 2: Bukti Pembayaran --}}
            <div class="flex flex-col justify-between rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Bukti Pembayaran Pendaftaran</span>
                        @if($scoutGroup->payment_proof_path)
                            <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                Terunggah
                            </span>
                        @else
                            <span class="rounded-md bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                Belum Diunggah
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Struk transfer / bukti pembayaran biaya registrasi kontingen lomba tingkat.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between gap-2">
                    <span class="font-mono text-[11px] text-zinc-400 truncate max-w-[200px]">
                        {{ $scoutGroup->payment_proof_path ? basename($scoutGroup->payment_proof_path) : '-' }}
                    </span>
                    @if($scoutGroup->payment_proof_path)
                        <div class="flex items-center gap-1.5">
                            @if($isPaymentImage)
                                <flux:button size="xs" variant="primary" icon="magnifying-glass-plus" @click="openModal('{{ asset('storage/' . $scoutGroup->payment_proof_path) }}', 'Bukti Pembayaran - {{ addslashes($scoutGroup->school_name) }}')">
                                    Zoom
                                </flux:button>
                                <flux:button href="{{ asset('storage/' . $scoutGroup->payment_proof_path) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square">
                                    Buka Berkas
                                </flux:button>
                            @else
                                <x-file-viewer-trigger :path="$scoutGroup->payment_proof_path" :title="'Bukti Pembayaran - '.$scoutGroup->school_name" label="Pratinjau Berkas" variant="primary" />
                            @endif
                        </div>
                    @endif
                </div>
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

                    <div class="flex items-center gap-2 flex-wrap">
                        @if($scoutGroup->canDownloadDocuments())
                            <flux:button href="{{ route('admin.scout-groups.patrols.id-cards', [$scoutGroup->id, $patrol->id]) }}" target="_blank" variant="subtle" size="xs" icon="qr-code"
                                data-loading="Menyiapkan ID Card Regu (.ZIP)...">
                                ID Card Regu (.ZIP / .PNG)
                            </flux:button>
                            <flux:button href="{{ route('admin.scout-groups.patrols.form-01', [$scoutGroup->id, $patrol->id]) }}" target="_blank" variant="subtle" size="xs" icon="printer"
                                data-loading="Mencetak FORM 01 Regu {{ $patrol->patrol_name }} (PDF)...">
                                Cetak FORM : 01
                            </flux:button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-5 py-2.5 font-semibold">Peserta</th>
                                <th class="px-5 py-2.5 font-semibold">Peran di Regu</th>
                                <th class="px-5 py-2.5 font-semibold">NTA &amp; TTL</th>
                                <th class="px-5 py-2.5 font-semibold">Gol. Darah</th>
                                <th class="px-5 py-2.5 font-semibold text-center">Kelengkapan Berkas</th>
                                <th class="px-5 py-2.5 font-semibold text-right">Aksi Review</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @forelse($patrol->participants as $participant)
                                @php
                                    // Hitung kelengkapan dokumen peserta (8 berkas)
                                    $docsCheck = [
                                        $participant->photo_path,
                                        $participant->doc_kta_path,
                                        $participant->doc_bpjs_path,
                                        $participant->doc_health_certificate_path,
                                        $participant->doc_birth_certificate_path,
                                        $participant->doc_family_card_path,
                                        $participant->doc_sku_path,
                                        $participant->doc_skk_path,
                                    ];
                                    $uploadedDocsCount = collect($docsCheck)->filter(fn($d) => !empty($d))->count();
                                    $isFullDocs = $uploadedDocsCount === 8;
                                @endphp
                                <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                                    <td class="px-5 py-2.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                        <div class="flex items-center gap-2.5">
                                            @if($participant->photo_path)
                                                <button type="button"
                                                        @click="openModal('{{ asset('storage/' . $participant->photo_path) }}', '{{ addslashes($participant->full_name) }} (Pas Foto Peserta)')"
                                                        class="group relative size-9 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-amber-500"
                                                        title="Klik untuk memperbesar foto">
                                                    <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                                    <span class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <flux:icon name="magnifying-glass-plus" class="size-3.5 text-white" />
                                                    </span>
                                                </button>
                                            @else
                                                <div class="size-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 text-xs font-bold shrink-0">
                                                    {{ strtoupper(substr($participant->full_name, 0, 2)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div>{{ $participant->full_name }}</div>
                                                <div class="mt-0.5"><x-id-badge :value="$participant->id_number" /></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-2.5">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $participant->role_in_patrol?->badgeClasses() }}">
                                            {{ $participant->role_in_patrol?->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-2.5 text-zinc-600 dark:text-zinc-400">
                                        <div class="font-mono">{{ $participant->scout_id ?: '-' }}</div>
                                        <div class="text-[11px] text-zinc-400">{{ $participant->birth_place }}, {{ $participant->birth_date?->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-400">
                                        {{ $participant->blood_type ?: '-' }}
                                    </td>
                                    <td class="px-5 py-2.5 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $isFullDocs ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : ($uploadedDocsCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300') }}">
                                            {{ $uploadedDocsCount }}/8 Berkas
                                        </span>
                                    </td>
                                    <td class="px-5 py-2.5 text-right">
                                        <div class="inline-flex items-center justify-end gap-1.5">
                                            <flux:modal.trigger name="review-participant-{{ $participant->id }}">
                                                <flux:button size="xs" variant="primary" icon="eye">
                                                    Review Data
                                                </flux:button>
                                            </flux:modal.trigger>

                                            <flux:button href="{{ route('admin.participants.show', $participant->id) }}" size="xs" variant="subtle" icon="identification">
                                                Detail
                                            </flux:button>

                                            @if($scoutGroup->canDownloadDocuments())
                                                <flux:button href="{{ route('admin.scout-groups.participants.print', [$scoutGroup->id, $participant->id]) }}" target="_blank" size="xs" variant="subtle" icon="printer"
                                                    data-loading="Mencetak Form Biodata {{ $participant->full_name }} (PDF)...">
                                                    Form
                                                </flux:button>
                                                <flux:button href="{{ route('digital-cards.participant', $participant->id) }}" target="_blank" size="xs" variant="subtle" icon="qr-code"
                                                    data-loading="Memuat Kartu Digital {{ $participant->full_name }}...">
                                                    Kartu
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-6 text-center text-zinc-400">
                                        Belum ada data anggota peserta dalam regu ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modals for each participant in patrol --}}
            @foreach($patrol->participants as $participant)
                @php
                    $participantDocsList = [
                        ['label' => 'Pas Foto 3x4', 'path' => $participant->photo_path, 'desc' => 'Foto berseragam pramuka lengkap'],
                        ['label' => 'Scan KTA Pramuka', 'path' => $participant->doc_kta_path, 'desc' => 'Kartu Tanda Anggota aktif'],
                        ['label' => 'Scan BPJS / Asuransi', 'path' => $participant->doc_bpjs_path, 'desc' => 'Kartu jaminan kesehatan aktif'],
                        ['label' => 'Surat Keterangan Sehat', 'path' => $participant->doc_health_certificate_path, 'desc' => 'Dari dokter / faskes pemerintah'],
                        ['label' => 'Scan Akta Kelahiran', 'path' => $participant->doc_birth_certificate_path, 'desc' => 'Akta otentik catatan sipil'],
                        ['label' => 'Scan Kartu Keluarga (KK)', 'path' => $participant->doc_family_card_path, 'desc' => 'Kartu keluarga terbaru'],
                        ['label' => 'Scan SKU Penggalang', 'path' => $participant->doc_sku_path, 'desc' => 'Syarat Kecakapan Umum'],
                        ['label' => 'Scan SKK Penggalang', 'path' => $participant->doc_skk_path, 'desc' => 'Syarat Kecakapan Khusus'],
                    ];
                    $formalEdu = $participant->formal_education ?: \App\Models\Participant::defaultFormalEducation();
                    $scoutSiaga = $participant->scout_siaga ?: \App\Models\Participant::defaultScoutSiaga();
                    $scoutPenggalang = $participant->scout_penggalang ?: \App\Models\Participant::defaultScoutPenggalang();
                @endphp

                <flux:modal name="review-participant-{{ $participant->id }}" class="w-full max-w-4xl max-h-[92vh] overflow-y-auto p-4 sm:p-6">
                    <div class="space-y-6">
                        {{-- Header Modal Peserta --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                @if($participant->photo_path)
                                    <button type="button"
                                            @click="openModal('{{ asset('storage/' . $participant->photo_path) }}', '{{ addslashes($participant->full_name) }} (Pas Foto Peserta)')"
                                            class="group relative size-16 rounded-xl overflow-hidden border-2 border-amber-500 shadow-xs shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-amber-400"
                                            title="Klik untuk memperbesar pas foto">
                                        <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                        <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <flux:icon name="magnifying-glass-plus" class="size-5 text-white" />
                                        </span>
                                    </button>
                                @else
                                    <div class="size-16 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-zinc-400 font-bold text-lg shrink-0">
                                        {{ strtoupper(substr($participant->full_name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $participant->role_in_patrol?->badgeClasses() }}">
                                            {{ $participant->role_in_patrol?->label() }}
                                        </span>
                                        <span class="rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            Regu {{ $patrol->patrol_name }} ({{ $patrol->gender_category?->label() }})
                                        </span>
                                        <x-id-badge :value="$participant->id_number" />
                                    </div>
                                    <h3 class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $participant->full_name }}
                                    </h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $scoutGroup->school_name }} &bull; Gudep {{ $patrol->group_number }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $participant->status?->badgeClasses() }}">
                                    {{ $participant->status?->label() ?? 'Status Belum Diverifikasi' }}
                                </span>
                            </div>
                        </div>

                        {{-- Section 1: Seluruh Dokumen Persyaratan yang Diunggah --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3">
                                1. Seluruh Dokumen Persyaratan Yang Diunggah (8 Berkas)
                            </h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
                                @foreach($participantDocsList as $doc)
                                    @php
                                        $ext = strtolower(pathinfo($doc['path'] ?? '', PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                    @endphp
                                    <div class="rounded-xl border {{ !empty($doc['path']) ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-800/40 dark:bg-emerald-950/20' : 'border-zinc-200 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-800/30' }} p-3 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate" title="{{ $doc['label'] }}">{{ $doc['label'] }}</span>
                                                @if(!empty($doc['path']))
                                                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">Ada</span>
                                                @else
                                                    <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400">Kosong</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-[10px] text-zinc-500 dark:text-zinc-400 line-clamp-1">
                                                {{ $doc['desc'] }}
                                            </p>
                                        </div>
                                        <div class="mt-3 pt-2 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between">
                                            @if(!empty($doc['path']))
                                                @if($isImg)
                                                    <div class="flex items-center gap-1.5 w-full">
                                                        <flux:button size="xs" variant="primary" icon="magnifying-glass-plus" class="flex-1 justify-center" @click="openModal('{{ asset('storage/' . $doc['path']) }}', '{{ addslashes($participant->full_name) }} - {{ addslashes($doc['label']) }}')">
                                                            Zoom
                                                        </flux:button>
                                                        <flux:button href="{{ asset('storage/' . $doc['path']) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square" title="Buka berkas di tab baru">
                                                        </flux:button>
                                                    </div>
                                                @else
                                                    <x-file-viewer-trigger :path="$doc['path']" :title="$doc['label'].' - '.$participant->full_name" label="Pratinjau Berkas" variant="primary" class="w-full justify-center" />
                                                @endif
                                            @else
                                                <span class="text-[11px] text-zinc-400 italic">Belum diunggah</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Section 2: Biodata Diri Peserta --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">
                                2. Biodata Lengkap Peserta
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">NTA Pramuka:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $participant->scout_id ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Jenis Kelamin:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Tempat, Tgl Lahir:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->birth_place ?: '-' }}, {{ $participant->birth_date?->translatedFormat('d F Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Agama &amp; Gol. Darah:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->religion ?: '-' }} ({{ $participant->blood_type ?: 'Gol. -' }})</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Tingkat / Kelas:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->class_grade ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">No. HP / WA:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $participant->phone ?: '-' }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-zinc-400 block text-[11px]">Alamat Rumah:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->home_address ?: '-' }}, {{ $participant->city ?: '-' }} {{ $participant->postal_code ?: '' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Data Orang Tua / Wali --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">
                                3. Data Orang Tua / Wali
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Nama Orang Tua / Wali:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->parent_name ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Pekerjaan:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->parent_job ?: '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">No. Telepon / WA Ortu:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $participant->parent_phone ?: ($participant->parent_home_phone ?: '-') }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-400 block text-[11px]">Kota &amp; Kode Pos:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->parent_city ?: '-' }} {{ $participant->parent_postal_code ?: '' }}</span>
                                </div>
                                <div class="col-span-2 sm:col-span-4">
                                    <span class="text-zinc-400 block text-[11px]">Alamat Orang Tua:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->parent_address ?: '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Riwayat Pendidikan Formal --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">
                                4. Riwayat Pendidikan Formal
                            </h4>
                            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-3 py-2 font-semibold">Tingkat</th>
                                            <th class="px-3 py-2 font-semibold">Nama Sekolah</th>
                                            <th class="px-3 py-2 font-semibold">Kota</th>
                                            <th class="px-3 py-2 font-semibold">Tahun Lulus</th>
                                            <th class="px-3 py-2 font-semibold">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                        @foreach($formalEdu as $edu)
                                            <tr>
                                                <td class="px-3 py-1.5 font-bold">{{ $edu['level'] ?? '-' }}</td>
                                                <td class="px-3 py-1.5">{{ $edu['school_name'] ?? '-' }}</td>
                                                <td class="px-3 py-1.5">{{ $edu['city'] ?? '-' }}</td>
                                                <td class="px-3 py-1.5 font-mono">{{ $edu['graduation_year'] ?? '-' }}</td>
                                                <td class="px-3 py-1.5 text-zinc-500">{{ $edu['notes'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Section 5: Riwayat Kepramukaan (Giat, TKK Siaga & Penggalang) --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-2">
                                5. Riwayat Kepramukaan, Giat &amp; Tanda Kecakapan Khusus
                            </h4>
                            @include('admin.participants.partials.scout-history', ['scoutSiaga' => $scoutSiaga, 'scoutPenggalang' => $scoutPenggalang])
                        </div>

                        {{-- Footer Modal --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center gap-2 flex-wrap">
                                <flux:button href="{{ route('admin.participants.show', $participant->id) }}" size="sm" variant="subtle" icon="arrow-top-right-on-square">
                                    Halaman Detail Lengkap
                                </flux:button>
                                @if($scoutGroup->canDownloadDocuments())
                                    <flux:button href="{{ route('admin.scout-groups.participants.print', [$scoutGroup->id, $participant->id]) }}" target="_blank" size="sm" variant="subtle" icon="printer"
                                        data-loading="Mencetak Form Biodata {{ $participant->full_name }} (PDF)...">
                                        Cetak Form Biodata
                                    </flux:button>
                                    <flux:button href="{{ route('digital-cards.participant', $participant->id) }}" target="_blank" size="sm" variant="subtle" icon="qr-code"
                                        data-loading="Memuat Kartu Digital {{ $participant->full_name }}...">
                                        Kartu Digital
                                    </flux:button>
                                @endif
                            </div>
                            <flux:modal.close>
                                <flux:button variant="filled">Tutup</flux:button>
                            </flux:modal.close>
                        </div>
                    </div>
                </flux:modal>
            @endforeach
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
            @if($scoutGroup->chaperones->count() > 0 && $scoutGroup->canDownloadDocuments())
                <flux:button href="{{ route('admin.scout-groups.chaperones.id-cards', $scoutGroup->id) }}" target="_blank" variant="subtle" size="xs" icon="qr-code"
                    data-loading="Menyiapkan ID Card Bindamping (.ZIP)...">
                    ID Card Bindamping (.ZIP / .PNG)
                </flux:button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-2.5 font-semibold">Bindamping</th>
                        <th class="px-5 py-2.5 font-semibold">Kategori</th>
                        <th class="px-5 py-2.5 font-semibold">NTA / NIK</th>
                        <th class="px-5 py-2.5 font-semibold">No. HP / WA</th>
                        <th class="px-5 py-2.5 font-semibold text-center">Pas Foto</th>
                        <th class="px-5 py-2.5 font-semibold text-right">Aksi Review</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($scoutGroup->chaperones as $chaperone)
                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40">
                            <td class="px-5 py-2.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                <div class="flex items-center gap-2.5">
                                    @if($chaperone->photo_path)
                                        <button type="button"
                                                @click="openModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto Bindamping)')"
                                                class="group relative size-9 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                title="Klik untuk memperbesar foto">
                                            <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                            <span class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <flux:icon name="magnifying-glass-plus" class="size-3.5 text-white" />
                                            </span>
                                        </button>
                                    @else
                                        <div class="size-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($chaperone->full_name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div>{{ $chaperone->full_name }}</div>
                                        <div class="mt-0.5"><x-id-badge :value="$chaperone->id_number" /></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-2.5">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $chaperone->gender === 'male' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                    {{ $chaperone->gender === 'male' ? 'Bindamping Putra' : 'Bindamping Putri' }}
                                </span>
                            </td>
                            <td class="px-5 py-2.5 text-zinc-600 dark:text-zinc-400 font-mono">
                                <div>{{ $chaperone->scout_id ?: '-' }}</div>
                                <div class="text-[11px] text-zinc-400">NIK: {{ $chaperone->national_identity_number ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-400">
                                {{ $chaperone->phone_number }}
                            </td>
                            <td class="px-5 py-2.5 text-center">
                                @if($chaperone->photo_path)
                                    <button type="button"
                                            @click="openModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto Bindamping)')"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900/80 cursor-zoom-in"
                                            title="Klik untuk memperbesar">
                                        <flux:icon name="magnifying-glass-plus" class="size-3" />
                                        Terunggah
                                    </button>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <flux:modal.trigger name="review-chaperone-{{ $chaperone->id }}">
                                        <flux:button size="xs" variant="primary" icon="eye">
                                            Review Data
                                        </flux:button>
                                    </flux:modal.trigger>

                                    <flux:button href="{{ route('admin.chaperones.show', $chaperone->id) }}" size="xs" variant="subtle" icon="identification">
                                        Detail
                                    </flux:button>

                                    @if($scoutGroup->canDownloadDocuments())
                                        <flux:button href="{{ route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]) }}" target="_blank" size="xs" variant="subtle" icon="printer">
                                            Form
                                        </flux:button>
                                        <flux:button href="{{ route('digital-cards.chaperone', $chaperone->id) }}" target="_blank" size="xs" variant="subtle" icon="qr-code">
                                            Kartu
                                        </flux:button>
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

    {{-- Modals for each chaperone --}}
    @foreach($scoutGroup->chaperones as $chaperone)
        @php
            $chaperoneEdu = $chaperone->formal_education ?: \App\Models\Chaperone::defaultFormalEducation();
            $chaperoneExp = $chaperone->scout_experiences ?: \App\Models\Chaperone::defaultScoutExperiences();
            $chaperoneTrain = $chaperone->scout_trainings ?: \App\Models\Chaperone::defaultScoutTrainings();
        @endphp

        <flux:modal name="review-chaperone-{{ $chaperone->id }}" class="w-full max-w-4xl max-h-[92vh] overflow-y-auto p-4 sm:p-6">
            <div class="space-y-6">
                {{-- Header Modal Bindamping --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        @if($chaperone->photo_path)
                            <button type="button"
                                    @click="openModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto Bindamping)')"
                                    class="group relative size-16 rounded-xl overflow-hidden border-2 border-indigo-500 shadow-xs shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                    title="Klik untuk memperbesar pas foto">
                                <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:icon name="magnifying-glass-plus" class="size-5 text-white" />
                                </span>
                            </button>
                        @else
                            <div class="size-16 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-zinc-400 font-bold text-lg shrink-0">
                                {{ strtoupper(substr($chaperone->full_name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $chaperone->gender === 'male' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                    {{ $chaperone->gender === 'male' ? 'Bindamping Putra' : 'Bindamping Putri' }}
                                </span>
                                <x-id-badge :value="$chaperone->id_number" />
                            </div>
                            <h3 class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $chaperone->full_name }}
                            </h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $scoutGroup->school_name }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $chaperone->status?->badgeClasses() }}">
                            {{ $chaperone->status?->label() ?? 'Status Belum Diverifikasi' }}
                        </span>
                    </div>
                </div>

                {{-- Section 1: Pas Foto Bindamping --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-2">
                        1. Berkas Unggahan Pas Foto
                    </h4>
                    <div class="flex items-center justify-between p-3 rounded-xl border {{ $chaperone->photo_path ? 'border-emerald-200 bg-emerald-50/40 dark:border-emerald-800/40 dark:bg-emerald-950/20' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/30' }}">
                        <div class="flex items-center gap-3">
                            @if($chaperone->photo_path)
                                <button type="button"
                                        @click="openModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto 3x4)')"
                                        class="group relative size-12 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 shrink-0 cursor-zoom-in focus:outline-none"
                                        title="Klik untuk memperbesar foto">
                                    <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <flux:icon name="magnifying-glass-plus" class="size-3.5 text-white" />
                                    </span>
                                </button>
                                <div>
                                    <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Pas Foto 3x4 Resmi Pembina</div>
                                    <div class="text-[11px] text-zinc-400 font-mono">{{ basename($chaperone->photo_path) }}</div>
                                </div>
                            @else
                                <div class="text-xs text-zinc-500">Pas foto belum diunggah oleh pangkalan.</div>
                            @endif
                        </div>
                        @if($chaperone->photo_path)
                            <div class="flex items-center gap-2">
                                <flux:button size="xs" variant="primary" icon="magnifying-glass-plus" @click="openModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto 3x4)')">
                                    Zoom Foto
                                </flux:button>
                                <flux:button href="{{ asset('storage/' . $chaperone->photo_path) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square">
                                    Buka
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Section 2: Biodata Lengkap Bindamping --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-2">
                        2. Biodata Pribadi &amp; Kontak
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Nomor Induk Kependudukan (NIK):</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->national_identity_number ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">NTA Pramuka:</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->scout_id ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Tempat, Tanggal Lahir:</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->birth_place ?: '-' }}, {{ $chaperone->birth_date?->translatedFormat('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Agama &amp; Gol. Darah:</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->religion ?: '-' }} ({{ $chaperone->blood_type ?: 'Gol. -' }})</span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">No. Telepon / WhatsApp:</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->phone_number ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Telepon Rumah:</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $chaperone->home_phone ?: '-' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-zinc-400 block text-[11px]">Alamat Lengkap:</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $chaperone->home_address ?: '-' }}, {{ $chaperone->city ?: '-' }} {{ $chaperone->postal_code ?: '' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Riwayat Pendidikan Formal --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-2">
                        3. Riwayat Pendidikan Formal
                    </h4>
                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                                <tr>
                                    <th class="px-3 py-2 font-semibold">Tingkat</th>
                                    <th class="px-3 py-2 font-semibold">Nama Sekolah / Instansi</th>
                                    <th class="px-3 py-2 font-semibold">Kota</th>
                                    <th class="px-3 py-2 font-semibold">Tahun Lulus</th>
                                    <th class="px-3 py-2 font-semibold">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach($chaperoneEdu as $edu)
                                    <tr>
                                        <td class="px-3 py-1.5 font-bold">{{ $edu['level'] ?? '-' }}</td>
                                        <td class="px-3 py-1.5">{{ $edu['school_name'] ?? '-' }}</td>
                                        <td class="px-3 py-1.5">{{ $edu['city'] ?? '-' }}</td>
                                        <td class="px-3 py-1.5 font-mono">{{ $edu['graduation_year'] ?? '-' }}</td>
                                        <td class="px-3 py-1.5 text-zinc-500">{{ $edu['notes'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Section 4: Riwayat Golongan & Kursus Kepramukaan --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-2">
                            4. Riwayat Golongan Pramuka
                        </h4>
                        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-3 py-1.5 font-semibold">Golongan</th>
                                        <th class="px-3 py-1.5 font-semibold">Tahun</th>
                                        <th class="px-3 py-1.5 font-semibold">No. Gudep</th>
                                        <th class="px-3 py-1.5 font-semibold">Pangkalan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach($chaperoneExp as $exp)
                                        <tr>
                                            <td class="px-3 py-1.5 font-bold">{{ $exp['level'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5 font-mono">{{ $exp['year'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5 font-mono">{{ $exp['group_number'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5">{{ $exp['base'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-2">
                            5. Riwayat Kursus Kepramukaan
                        </h4>
                        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-3 py-1.5 font-semibold">Kursus</th>
                                        <th class="px-3 py-1.5 font-semibold">Tahun</th>
                                        <th class="px-3 py-1.5 font-semibold">Tempat</th>
                                        <th class="px-3 py-1.5 font-semibold">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach($chaperoneTrain as $tr)
                                        <tr>
                                            <td class="px-3 py-1.5 font-bold">{{ $tr['course'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5 font-mono">{{ $tr['year'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5">{{ $tr['location'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5 text-zinc-500">{{ $tr['notes'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Section 6: Kursus / Pelatihan di Luar Kepramukaan --}}
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-2">
                        6. Kursus / Pelatihan di Luar Kepramukaan
                    </h4>
                    @php
                        $otherTrainings = collect($chaperone->other_trainings ?? [])->filter(fn ($t) => filled($t['name'] ?? null));
                    @endphp
                    @if($otherTrainings->isEmpty())
                        <p class="rounded-xl border border-dashed border-zinc-200 px-3 py-2 text-[11px] italic text-zinc-400 dark:border-zinc-800">Belum ada kursus/pelatihan lain yang dicatat.</p>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-3 py-1.5 font-semibold">Nama</th>
                                        <th class="px-3 py-1.5 font-semibold">Tahun</th>
                                        <th class="px-3 py-1.5 font-semibold">Tempat</th>
                                        <th class="px-3 py-1.5 font-semibold">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    @foreach($otherTrainings as $ot)
                                        <tr>
                                            <td class="px-3 py-1.5 font-bold">{{ $ot['name'] }}</td>
                                            <td class="px-3 py-1.5 font-mono">{{ $ot['year'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5">{{ $ot['location'] ?? '-' }}</td>
                                            <td class="px-3 py-1.5 text-zinc-500">{{ $ot['notes'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Footer Modal --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center gap-2 flex-wrap">
                        <flux:button href="{{ route('admin.chaperones.show', $chaperone->id) }}" size="sm" variant="subtle" icon="arrow-top-right-on-square">
                            Halaman Detail Lengkap
                        </flux:button>
                        @if($scoutGroup->canDownloadDocuments())
                            <flux:button href="{{ route('admin.scout-groups.chaperones.print', [$scoutGroup->id, $chaperone->id]) }}" target="_blank" size="sm" variant="subtle" icon="printer"
                                data-loading="Mencetak Form Biodata {{ $chaperone->full_name }} (PDF)...">
                                Cetak Form Biodata
                            </flux:button>
                            <flux:button href="{{ route('digital-cards.chaperone', $chaperone->id) }}" target="_blank" size="sm" variant="subtle" icon="qr-code"
                                data-loading="Memuat Kartu Digital {{ $chaperone->full_name }}...">
                                Kartu Digital
                            </flux:button>
                        @endif
                    </div>
                    <flux:modal.close>
                        <flux:button variant="filled">Tutup</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endforeach

    {{-- Panel keputusan verifikasi (Livewire): modal konfirmasi, toast langsung, dan pemantauan perubahan --}}
    <livewire:admin.scout-group-verification-actions :scout-group-id="$scoutGroup->id" lazy />

    {{-- Lightbox Image Zoom Viewer Modal --}}
    <div x-show="imageViewerOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex flex-col items-center justify-between bg-black/85 backdrop-blur-xs p-4 sm:p-6"
         @click.self="closeModal()">

        {{-- Viewer Header & Controls --}}
        <div class="flex w-full max-w-5xl items-center justify-between gap-3 rounded-2xl bg-zinc-900/90 px-4 py-3 text-white border border-zinc-700/60 shadow-xl shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <flux:icon name="photo" class="size-5 text-amber-400 shrink-0" />
                <div class="truncate">
                    <div class="text-xs sm:text-sm font-bold truncate text-zinc-100" x-text="imageViewerTitle"></div>
                    <div class="text-[11px] text-zinc-400">Gunakan roda mouse atau tombol kontrol untuk zoom in / out</div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="rounded-lg bg-zinc-800 px-2 py-1 font-mono text-xs font-semibold text-amber-300 border border-zinc-700/50"
                      x-text="Math.round(imageZoomScale * 100) + '%'">
                </span>

                <flux:button size="xs" variant="subtle" icon="magnifying-glass-plus" @click="zoomIn()">
                    Zoom In
                </flux:button>
                <flux:button size="xs" variant="subtle" icon="magnifying-glass-minus" @click="zoomOut()">
                    Zoom Out
                </flux:button>
                <flux:button size="xs" variant="subtle" icon="arrow-path" @click="resetZoom()">
                    Reset
                </flux:button>
                <flux:button size="xs" variant="danger" icon="x-mark" @click="closeModal()">
                    Tutup
                </flux:button>
            </div>
        </div>

        {{-- Viewer Canvas Container --}}
        <div class="flex flex-1 w-full max-w-5xl items-center justify-center overflow-auto p-4 my-2 select-none"
             @wheel.prevent="if ($event.deltaY < 0) zoomIn(); else zoomOut();"
             @dblclick="toggleZoom()">
            <img :src="imageViewerSrc"
                 :alt="imageViewerTitle"
                 class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-2xl transition-transform duration-150 ease-out cursor-pointer"
                 :style="'transform: scale(' + imageZoomScale + ');'"
                 title="Klik ganda untuk toggle 2x / 1x zoom, scroll roda mouse untuk zoom in/out">
        </div>

        {{-- Viewer Footer Hint --}}
        <div class="text-[11px] text-zinc-400 text-center shrink-0">
            Tip: Scroll roda mouse ke atas/bawah untuk zoom in/out &bull; Klik ganda foto untuk toggle zoom &bull; Tekan tombol ESC untuk menutup
        </div>
    </div>

    <x-file-viewer />
</div>
