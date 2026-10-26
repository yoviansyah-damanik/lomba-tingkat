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
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                Status Kelengkapan Persyaratan Berkas
            </h3>
            @if(count($missingRequirements) > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    <span class="size-1.5 rounded-full bg-amber-600"></span>
                    {{ count($missingRequirements) }} Persyaratan Belum Lengkap
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                    <span class="size-1.5 rounded-full bg-emerald-600"></span>
                    Persyaratan Terpenuhi Lengkap
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['is_profile_complete'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20' }} p-3 text-xs">
                @if($readiness['is_profile_complete'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Profil Pangkalan</span>
                @else
                    <flux:icon name="exclamation-circle" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                    <span class="font-medium text-amber-900 dark:text-amber-200">Profil Belum Lengkap</span>
                @endif
            </div>

            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['has_patrol'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20' }} p-3 text-xs">
                @if($readiness['has_patrol'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Minimal 1 Regu</span>
                @else
                    <flux:icon name="exclamation-circle" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                    <span class="font-medium text-amber-900 dark:text-amber-200">Belum Ada Regu</span>
                @endif
            </div>

            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['has_chaperone'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50/60 dark:border-amber-900/40 dark:bg-amber-950/20' }} p-3 text-xs">
                @if($readiness['has_chaperone'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Minimal 1 Bindamping</span>
                @else
                    <flux:icon name="exclamation-circle" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" />
                    <span class="font-medium text-amber-900 dark:text-amber-200">Belum Ada Bindamping</span>
                @endif
            </div>

            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['has_mandate_letter'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-zinc-200 bg-zinc-50/60 dark:border-zinc-700 dark:bg-zinc-800/30' }} p-3 text-xs">
                @if($readiness['has_mandate_letter'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Surat Tugas Terunggah</span>
                @else
                    <flux:icon name="document-arrow-up" class="size-4 text-zinc-400 shrink-0" />
                    <span class="font-medium text-zinc-500">Surat Tugas Belum Ada</span>
                @endif
            </div>

            <div class="flex items-center gap-2.5 rounded-xl border {{ $readiness['has_payment_proof'] ? 'border-emerald-200 bg-emerald-50/60 dark:border-emerald-900/40 dark:bg-emerald-950/20' : 'border-zinc-200 bg-zinc-50/60 dark:border-zinc-700 dark:bg-zinc-800/30' }} p-3 text-xs">
                @if($readiness['has_payment_proof'])
                    <flux:icon name="check-circle" class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <span class="font-medium text-emerald-900 dark:text-emerald-200">Bukti Bayar Terunggah</span>
                @else
                    <flux:icon name="document-arrow-up" class="size-4 text-zinc-400 shrink-0" />
                    <span class="font-medium text-zinc-500">Bukti Bayar Belum Ada</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 1. Identitas Gugus Depan --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">1. Data Pangkalan Gugus Depan</h3>
            @if($scoutGroup->isEditable())
                <flux:button size="xs" variant="subtle" icon-trailing="arrow-right" href="{{ route('scout-group.profile') }}" wire:navigate>
                    Ubah Data
                </flux:button>
            @endif
        </div>

        @include('livewire.portal.partials.section-warning', ['items' => $this->sectionWarnings['profile'] ?? []])

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
                <flux:button size="xs" variant="subtle" icon-trailing="arrow-right" href="{{ route('scout-group.patrols') }}" wire:navigate>
                    Kelola Regu
                </flux:button>
            @endif
        </div>

        @if($scoutGroup->patrols->isEmpty())
            @include('livewire.portal.partials.section-warning', ['items' => $this->sectionWarnings['patrols'] ?? [], 'title' => 'Belum ada regu terdaftar'])
        @endif

        <div class="mt-4 space-y-6">
            @forelse($scoutGroup->patrols as $patrol)
                <div class="rounded-xl border border-zinc-200/60 p-4 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Regu {{ $patrol->patrol_name }}</span>
                            <span class="text-xs text-zinc-400">({{ $patrol->gender_category?->label() }})</span>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($scoutGroup->canDownloadDocuments())
                                <flux:button size="xs" variant="subtle" icon="printer" href="{{ route('scout-group.patrols.form-01', $patrol->id) }}" target="_blank"
                                    data-loading="Mencetak FORM 01 Regu {{ $patrol->patrol_name }} (PDF)...">
                                    Cetak FORM : 01
                                </flux:button>
                            @endif
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                                {{ $patrol->participantCount() }} Peserta
                            </span>
                        </div>
                    </div>

                    @include('livewire.portal.partials.section-warning', ['messages' => array_map('ucfirst', $scoutGroup->rosterProblemsFor($patrol)), 'title' => 'Regu ini belum memenuhi persyaratan'])

                    <div class="mt-3 overflow-x-auto">
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
                                @forelse($patrol->orderedParticipants() as $idx => $participant)
                                    <tr>
                                        <td class="py-2 px-2.5 text-zinc-400">{{ $idx + 1 }}</td>
                                        <td class="py-2 px-2.5 font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $participant->full_name }}
                                            <div class="mt-0.5"><x-id-badge :value="$participant->id_number" /></div>
                                        </td>
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
                <flux:button size="xs" variant="subtle" icon-trailing="arrow-right" href="{{ route('scout-group.chaperones') }}" wire:navigate>
                    Kelola Bindamping
                </flux:button>
            @endif
        </div>

        @include('livewire.portal.partials.section-warning', ['items' => $this->sectionWarnings['chaperones'] ?? []])

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            @forelse($scoutGroup->chaperones as $chaperone)
                <div class="rounded-xl border border-zinc-100 bg-zinc-50/50 p-3.5 text-xs dark:border-zinc-800 dark:bg-zinc-800/40">
                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $chaperone->full_name }}</span>
                    <x-id-badge :value="$chaperone->id_number" class="my-0.5" />
                    <span class="text-zinc-500">{{ $chaperone->gender === 'male' ? 'Pembina Putra' : 'Pembina Putri' }} &bull; HP: {{ $chaperone->phone_number }}</span>
                </div>
            @empty
                <p class="text-xs text-zinc-400 col-span-2 text-center py-3">Belum ada pembina pendamping yang didaftarkan.</p>
            @endforelse
        </div>
    </div>

    {{-- 4. Berkas Unggahan Seluruh Kontingen --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-3 mb-5 dark:border-zinc-800">
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">4. Berkas Unggahan Kontingen</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Ringkasan seluruh berkas yang telah diunggah oleh pangkalan, peserta, dan bindamping.</p>
            </div>
        </div>

        @include('livewire.portal.partials.section-warning', ['messages' => $this->participantDocumentWarnings, 'title' => 'Berkas persyaratan peserta belum lengkap'])

        {{-- 4a. Berkas Pangkalan --}}
        <div class="mb-6">
            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">Berkas Pangkalan</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Surat Tugas / Mandat --}}
                <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/30">
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $scoutGroup->mandate_letter_path ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">
                            <flux:icon name="{{ $scoutGroup->mandate_letter_path ? 'document-check' : 'document-arrow-up' }}" class="size-4" />
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">Surat Tugas / Mandat</span>
                            <span class="text-[11px] {{ $scoutGroup->mandate_letter_path ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                                {{ $scoutGroup->mandate_letter_path ? 'Terunggah' : 'Belum diunggah' }}
                            </span>
                        </div>
                    </div>
                    @if($scoutGroup->mandate_letter_path)
                        <x-file-viewer-trigger :path="$scoutGroup->mandate_letter_path" title="Surat Tugas / Mandat Gugus Depan" label="Lihat" />
                    @endif
                  </div>
                  @if($scoutGroup->mandate_letter_path)
                      <x-file-preview :path="$scoutGroup->mandate_letter_path" title="Surat Tugas / Mandat Gugus Depan" />
                  @endif
                </div>

                {{-- Bukti Pembayaran --}}
                <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/30">
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $scoutGroup->payment_proof_path ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800' }}">
                            <flux:icon name="{{ $scoutGroup->payment_proof_path ? 'document-check' : 'receipt-percent' }}" class="size-4" />
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">Bukti Pembayaran</span>
                            <span class="text-[11px] {{ $scoutGroup->payment_proof_path ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                                {{ $scoutGroup->payment_proof_path ? 'Terunggah' : 'Belum diunggah' }}
                            </span>
                        </div>
                    </div>
                    @if($scoutGroup->payment_proof_path)
                        <x-file-viewer-trigger :path="$scoutGroup->payment_proof_path" title="Bukti Pembayaran Biaya Pendaftaran" label="Lihat" />
                    @endif
                  </div>
                  @if($scoutGroup->payment_proof_path)
                      <x-file-preview :path="$scoutGroup->payment_proof_path" title="Bukti Pembayaran Biaya Pendaftaran" />
                  @endif
                </div>
            </div>
        </div>

        {{-- 4b. Berkas Per Regu & Peserta --}}
        @forelse($scoutGroup->patrols as $patrol)
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                        <flux:icon name="user-group" class="size-3.5" />
                        Regu {{ $patrol->patrol_name }} ({{ $patrol->gender_category?->label() }})
                    </span>
                    <span class="text-xs text-zinc-400">{{ $patrol->participants->count() }} Peserta</span>
                </div>

                @forelse($patrol->orderedParticipants() as $participant)
                    <div class="mb-4 rounded-xl border border-zinc-200/70 bg-zinc-50/30 dark:border-zinc-800 dark:bg-zinc-800/20 overflow-hidden">
                        {{-- Header Peserta --}}
                        <div class="flex items-center justify-between px-4 py-2.5 bg-zinc-100/60 border-b border-zinc-200/60 dark:bg-zinc-800/50 dark:border-zinc-700/50">
                            <div class="flex items-center gap-2.5">
                                @if($participant->photo_path)
                                    <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->full_name }}"
                                         class="size-8 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700">
                                @else
                                    <div class="size-8 rounded-lg bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                                        <flux:icon name="user" class="size-4 text-zinc-400" />
                                    </div>
                                @endif
                                <div>
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $participant->full_name }}</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <x-id-badge :value="$participant->id_number" />
                                        <span class="text-[10px] rounded bg-amber-50 px-1.5 py-0.5 font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                            {{ $participant->role_in_patrol?->label() }}
                                        </span>
                                        @if($participant->photo_path)
                                            <x-file-viewer-trigger :path="$participant->photo_path" :title="'Pas Foto — '.$participant->full_name" label="Foto" />
                                        @else
                                            <span class="text-[10px] text-zinc-400">Foto belum ada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @php
                                $docKeys = array_keys(\App\Models\Participant::DOCUMENTS);
                                $uploadedCount = collect($docKeys)->filter(fn($key) => !empty($participant->{$key . '_path'}))->count();
                                $totalDocs = count($docKeys);
                            @endphp
                            <span class="text-[11px] font-semibold {{ $uploadedCount === $totalDocs ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $uploadedCount }}/{{ $totalDocs }} Berkas
                            </span>
                        </div>

                        {{-- Grid Dokumen Peserta --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-px bg-zinc-200/50 dark:bg-zinc-700/30 p-px">
                            @foreach(\App\Models\Participant::DOCUMENTS as $key => [$pathCol, $label])
                                @php $filePath = $participant->{$pathCol}; @endphp
                                <div class="flex flex-col items-center gap-1.5 bg-white dark:bg-zinc-900 p-2.5 text-center">
                                    <div class="flex size-8 items-center justify-center rounded-lg {{ $filePath ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-300 dark:bg-zinc-800' }}">
                                        <flux:icon name="{{ $filePath ? 'document-check' : 'document' }}" class="size-4" />
                                    </div>
                                    <span class="text-[10px] leading-tight text-zinc-600 dark:text-zinc-400 font-medium line-clamp-2">
                                        {{ Str::after($label, 'Scan ') }}
                                    </span>
                                    @if($filePath)
                                        <x-file-viewer-trigger :path="$filePath" :title="Str::after($label, 'Scan ').' — '.$participant->full_name" label="Lihat" />
                                    @else
                                        <span class="text-[10px] text-zinc-400">Belum</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-zinc-400 text-center py-3">Regu ini belum memiliki peserta.</p>
                @endforelse
            </div>
        @empty
            <p class="text-xs text-zinc-400 text-center py-3 mb-4">Belum ada regu yang dibentuk.</p>
        @endforelse

        {{-- 4c. Berkas Bindamping --}}
        @if($scoutGroup->chaperones->isNotEmpty())
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">Berkas Pembina Pendamping</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($scoutGroup->chaperones as $chaperone)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/30">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if($chaperone->photo_path)
                                    <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}"
                                         class="size-9 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700 shrink-0">
                                @else
                                    <div class="size-9 rounded-lg bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center shrink-0">
                                        <flux:icon name="user" class="size-5 text-zinc-400" />
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="block text-xs font-semibold text-zinc-900 dark:text-zinc-100 truncate">{{ $chaperone->full_name }}</span>
                                    <span class="text-[11px] text-zinc-500">{{ $chaperone->gender === 'male' ? 'Pembina Putra' : 'Pembina Putri' }}</span>
                                    <div class="mt-0.5">
                                        <span class="text-[11px] {{ $chaperone->photo_path ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                                            Foto: {{ $chaperone->photo_path ? 'Terunggah' : 'Belum ada' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @if($chaperone->photo_path)
                                <x-file-viewer-trigger :path="$chaperone->photo_path" :title="'Foto Pembina Pendamping — '.$chaperone->full_name" label="Lihat Foto" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- 5. Berkas Administrasi Pangkalan (Surat Tugas & Bukti Bayar) --}}
    @if($scoutGroup->isEditable())
        <div id="section-administrative-docs" class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 scroll-mt-6">
            <div class="border-b border-zinc-100 pb-3 mb-5 dark:border-zinc-800">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">5. Berkas Administrasi Pangkalan</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Unggah Surat Tugas / Mandat dan Bukti Pembayaran sebelum mengajukan. Biaya pendaftaran dihitung berdasarkan jumlah regu terdaftar.</p>
            </div>

            @include('livewire.portal.partials.section-warning', ['items' => $this->sectionWarnings['documents'] ?? [], 'title' => 'Berkas administrasi belum lengkap'])

            {{-- Panel Penjelasan Biaya Pendaftaran Per Regu & Rekening Kwarcab --}}
            <div class="mb-6 rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/70 via-white to-amber-50/30 p-5 dark:border-amber-500/20 dark:from-zinc-900 dark:via-zinc-900 dark:to-amber-950/20">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
                    {{-- Detail Tagihan Pangkalan --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300">
                                <flux:icon name="banknotes" class="size-3.5" />
                                Rincian Biaya Pendaftaran Kontingen
                            </span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Tarif Resmi LT III</span>
                        </div>

                        <div class="space-y-1.5 text-xs text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-center justify-between sm:justify-start sm:gap-6 py-1 border-b border-amber-100 dark:border-zinc-800">
                                <span class="text-zinc-500 dark:text-zinc-400">Tarif Biaya per Regu:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $billingSummary['formatted_fee_per_patrol'] }} / regu</span>
                            </div>
                            <div class="flex items-center justify-between sm:justify-start sm:gap-6 py-1 border-b border-amber-100 dark:border-zinc-800">
                                <span class="text-zinc-500 dark:text-zinc-400">Jumlah Regu Terdaftar:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $billingSummary['patrol_count'] }} Regu</span>
                            </div>

                            @if($billingSummary['patrol_count'] > 0)
                                <div class="py-1">
                                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400 block mb-1">Rincian per Regu:</span>
                                    <div class="space-y-1 pl-2">
                                        @foreach($billingSummary['patrols'] as $patrolItem)
                                            <div class="flex items-center justify-between sm:justify-start sm:gap-4 text-[11px]">
                                                <span class="font-medium text-zinc-800 dark:text-zinc-200">&bull; Regu {{ $patrolItem['name'] }} ({{ $patrolItem['category'] }}):</span>
                                                <span class="font-mono text-zinc-600 dark:text-zinc-400">{{ $patrolItem['fee'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-[11px] text-amber-800 dark:text-amber-300 italic py-1">
                                    * Pangkalan belum mendaftarkan regu. Silakan daftarkan minimal 1 regu pada menu Kelola Regu.
                                </p>
                            @endif

                            <div class="flex items-center justify-between sm:justify-start sm:gap-6 pt-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200">Total Biaya Pendaftaran:</span>
                                <span class="text-base font-extrabold text-amber-700 dark:text-amber-400 font-mono">{{ $billingSummary['formatted_total_fee'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Rekening Tujuan Kwarcab --}}
                    <div class="rounded-xl border border-amber-300/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-800/80 md:w-80 shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block mb-1">Rekening Resmi Kwarcab</span>
                        <div class="space-y-1 text-xs">
                            <div>
                                <span class="text-zinc-400 block text-[11px]">Bank Tujuan:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $billingSummary['bank_name'] }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-[11px]">Nomor Rekening:</span>
                                <span class="font-mono font-extrabold text-amber-800 dark:text-amber-300 text-sm tracking-wide select-all">{{ $billingSummary['account_number'] }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-[11px]">Atas Nama:</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200 text-[11px] leading-tight block">{{ $billingSummary['account_holder'] }}</span>
                            </div>
                        </div>
                        <p class="mt-3 text-[10px] text-zinc-500 dark:text-zinc-400 leading-tight border-t border-zinc-100 dark:border-zinc-700/60 pt-2">
                            {{ $billingSummary['instructions'] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Surat Tugas / Mandat Gugus Depan</label>
                        @if($scoutGroup->mandate_letter_path)
                            <x-file-viewer-trigger :path="$scoutGroup->mandate_letter_path" title="Surat Tugas / Mandat Gugus Depan" label="Lihat Berkas" />
                        @else
                            <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-zinc-500 mb-2">Surat penugasan resmi dari Gugus Depan untuk mengikuti LT III (format PDF).</p>
                    <input type="file" wire:model="mandate_letter" accept=".pdf,application/pdf" class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300">
                    @error('mandate_letter') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Bukti Pembayaran Biaya Pendaftaran</label>
                        @if($scoutGroup->payment_proof_path)
                            <x-file-viewer-trigger :path="$scoutGroup->payment_proof_path" title="Bukti Pembayaran Biaya Pendaftaran" label="Lihat Berkas" />
                        @else
                            <span class="text-[10px] text-zinc-400">Belum diunggah</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-zinc-500 mb-2">Unggah struk/bukti transfer (PDF atau gambar) sejumlah <strong>{{ $billingSummary['formatted_total_fee'] }}</strong> ({{ $billingSummary['patrol_count'] }} regu).</p>
                    <input type="file" wire:model="payment_proof" accept=".pdf,image/png,image/jpeg,image/jpg" class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded-lg file:border-0 file:bg-amber-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-200 dark:file:bg-amber-950/60 dark:file:text-amber-300">
                    @error('payment_proof') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    @endif

    {{-- Action Submit --}}
    @if($scoutGroup->isEditable())
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Siap Mengajukan Pendaftaran?</h4>
                <p class="text-xs text-zinc-500">Sistem akan memeriksa kelengkapan seluruh berkas, regu, dan bindamping sebelum pengajuan dikonfirmasi.</p>
            </div>
            <flux:button type="button" variant="primary" icon="paper-airplane" wire:click="attemptSubmit" class="shrink-0">
                Konfirmasi &amp; Ajukan Pendaftaran
            </flux:button>
        </div>
    @else
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $scoutGroup->verification_status?->badgeClasses() }}">
                        {{ $scoutGroup->verification_status?->label() }}
                    </span>
                    <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Status Pendaftaran Kontingen</h4>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    @if($scoutGroup->canDownloadDocuments())
                        Pendaftaran kontingen telah diverifikasi resmi oleh Administrator. Anda dapat mengunduh Bukti Registrasi resmi.
                    @else
                        Pendaftaran kontingen telah diajukan dan terkunci. Saat ini sedang dalam tahap antrean verifikasi Administrator Kwarcab.
                    @endif
                </p>
            </div>
            @if($scoutGroup->canDownloadDocuments())
                <flux:button href="{{ route('scout-group.registration-certificate', ['download' => 1]) }}" variant="primary" icon="document-text"
                    data-loading="Mencetak Bukti Registrasi (PDF)..." class="shrink-0">
                    Unduh Bukti Registrasi (PDF)
                </flux:button>
            @endif
        </div>
    @endif

    {{-- Modal Peringatan: Persyaratan Belum Lengkap --}}
    <flux:modal wire:model="showIncompleteModal" class="w-full max-w-xl max-h-[92vh] overflow-y-auto">
        <div class="space-y-5">
            {{-- Header Modal --}}
            <div class="flex items-start gap-3.5 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 mt-0.5 shadow-xs">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                            Pemeriksaan Persyaratan
                        </span>
                        <span class="text-[11px] text-zinc-400 font-medium">{{ count($missingRequirements) }} Item Belum Dipenuhi</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Persyaratan Belum Lengkap
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                        Pendaftaran kontingen belum dapat diajukan ke panitia karena terdapat data atau dokumen persyaratan yang masih harus dilengkapi terlebih dahulu.
                    </p>
                </div>
            </div>

            {{-- Daftar Item yang Belum Lengkap --}}
            <div class="space-y-2.5">
                <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                    Daftar Persyaratan yang Harus Dilengkapi:
                </p>
                <div class="space-y-2.5">
                    @foreach($missingRequirements as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-amber-200/80 bg-amber-50/60 p-3.5 dark:border-amber-900/40 dark:bg-amber-950/20">
                            <div class="flex items-start gap-3">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white shadow-xs border border-amber-200 dark:bg-zinc-900 dark:border-zinc-800 text-amber-600 dark:text-amber-400 mt-0.5">
                                    <flux:icon :name="$item['icon']" class="size-4" />
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 block mb-0.5">
                                        {{ $item['category'] }}
                                    </span>
                                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $item['title'] }}
                                    </h4>
                                    <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mt-0.5 leading-normal">
                                        {{ $item['description'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 self-end sm:self-center">
                                @if(str_starts_with($item['action_url'], '#'))
                                    <flux:button type="button" size="xs" variant="primary" icon-trailing="arrow-down" wire:click="$set('showIncompleteModal', false)">
                                        {{ $item['action_label'] }}
                                    </flux:button>
                                @else
                                    <flux:button size="xs" variant="primary" icon-trailing="arrow-right" href="{{ $item['action_url'] }}" wire:navigate>
                                        {{ $item['action_label'] }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Catatan Berkas Peserta --}}
            @if(isset($participantDocs['incomplete']) && $participantDocs['incomplete'] > 0)
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-400 flex items-start gap-2.5">
                    <flux:icon name="information-circle" class="size-4 text-zinc-500 shrink-0 mt-0.5" />
                    <div class="leading-relaxed">
                        <strong>Catatan Berkas Peserta:</strong> Sebanyak <strong>{{ $participantDocs['incomplete'] }}</strong> dari <strong>{{ $participantDocs['total'] }}</strong> peserta belum mengunggah lengkap 7 dokumen scan persyaratan (KTA, BPJS, SKU, dll). Anda dapat melengkapinya secara berkala melalui menu <a href="{{ route('scout-group.patrols') }}" wire:navigate class="text-amber-700 font-semibold underline dark:text-amber-400">Kelola Regu</a>.
                    </div>
                </div>
            @endif

            {{-- Footer Modal --}}
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="primary" wire:click="$set('showIncompleteModal', false)">
                    Mengerti, Saya Akan Lengkapi
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Konfirmasi Pengajuan (Ketika Seluruh Persyaratan Terpenuhi) --}}
    <flux:modal wire:model="showSubmitModal" class="w-full max-w-lg">
        <div class="space-y-4">
            {{-- Header Modal --}}
            <div class="flex items-start gap-3.5 pb-3 border-b border-zinc-200/80 dark:border-zinc-800">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 mt-0.5 shadow-xs">
                    <flux:icon name="check-badge" class="size-6" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                            Persyaratan Terpenuhi
                        </span>
                        <span class="text-[11px] text-zinc-400 font-medium">Siap Diajukan</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Konfirmasi Pengajuan Pendaftaran
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                        Seluruh persyaratan minimal kontingen telah lengkap dan siap diajukan ke panitia.
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-200">
                <div class="flex items-start gap-2.5">
                    <flux:icon name="lock-closed" class="size-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <div class="leading-relaxed">
                        Setelah diajukan, berkas pangkalan, regu, peserta, dan bindamping akan <strong>dikunci otomatis</strong> dan masuk ke antrean verifikasi Administrator Kwarcab. Anda tidak dapat mengubah data kecuali panitia meminta perbaikan berkas.
                    </div>
                </div>
            </div>

            <div class="space-y-2 rounded-xl border border-zinc-200 bg-zinc-50/60 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-800/30">
                <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                    <span>Pangkalan:</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $scoutGroup->school_name }} ({{ $scoutGroup->group_number }})</span>
                </div>
                <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                    <span>Total Regu &amp; Peserta:</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $scoutGroup->patrols->count() }} Regu &bull; {{ $scoutGroup->patrols->sum(fn($p) => $p->participants->count()) }} Peserta</span>
                </div>
                <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                    <span>Biaya Pendaftaran:</span>
                    <span class="font-bold text-amber-800 dark:text-amber-300 font-mono">{{ $billingSummary['formatted_total_fee'] }} ({{ $billingSummary['patrol_count'] }} Regu)</span>
                </div>
                <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                    <span>Pembina Pendamping:</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $scoutGroup->chaperones->count() }} Orang</span>
                </div>
                <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                    <span>Berkas Administrasi:</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-400">Surat Tugas &amp; Bukti Bayar Terlampir</span>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button type="button" variant="subtle" wire:click="$set('showSubmitModal', false)">
                    Batal &amp; Periksa Kembali
                </flux:button>
                <flux:button type="button" variant="primary" icon="paper-airplane" wire:click="submitRegistration">
                    Ya, Ajukan Pendaftaran
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <x-file-viewer />
</div>
