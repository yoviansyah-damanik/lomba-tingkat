@assets
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endassets

<div class="flex h-full w-full flex-1 flex-col gap-6" x-on:permission-letter-ready.window="window.open($event.detail.url, '_blank')">
    <x-heading-section
        title="Permohonan Izin"
        description="Catat izin meninggalkan perkemahan (permanen atau sementara) dan kunjungan tamu. Izin meninggalkan perkemahan wajib memindai QR peserta dan bindamping."
        badge="Hanya Administrator"
        badge-icon="clipboard-document-check"
        badge-color="amber"
    />

    {{-- Tab --}}
    <div class="flex flex-wrap items-center gap-2" role="tablist">
        <flux:button wire:click="setTab('create')" size="sm" :variant="$tab === 'create' ? 'primary' : 'subtle'" icon="plus">Permohonan Baru</flux:button>
        <flux:button wire:click="setTab('return')" size="sm" :variant="$tab === 'return' ? 'primary' : 'subtle'" icon="arrow-uturn-left">Konfirmasi Kembali</flux:button>
        <flux:button wire:click="setTab('unreturned')" size="sm" :variant="$tab === 'unreturned' ? 'primary' : 'subtle'" icon="user-minus" class="relative">
            Belum Kembali
            @if ($unreturnedCount > 0)
                <span class="ml-1.5 inline-flex items-center rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-400/20 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                    {{ $unreturnedCount }}
                </span>
            @endif
        </flux:button>
        <flux:button wire:click="setTab('history')" size="sm" :variant="$tab === 'history' ? 'primary' : 'subtle'" icon="clock">Riwayat</flux:button>
    </div>

    @if ($feedback)
        <div class="flex items-start gap-2 rounded-xl border p-3 text-sm {{ $feedback['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/30 dark:bg-red-950/30 dark:text-red-200' }}" role="status">
            <flux:icon :name="$feedback['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'" class="mt-0.5 size-4 shrink-0" />
            <span>{{ $feedback['message'] }}</span>
            @if ($lastLetterUrl && $feedback['type'] === 'success')
                <flux:button href="{{ $lastLetterUrl }}" target="_blank" size="xs" variant="primary" icon="printer" class="ml-auto shrink-0">Cetak Surat Izin</flux:button>
            @endif
        </div>
    @endif

    @if ($tab === 'create')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            {{-- Form permohonan --}}
            <form wire:submit="submit" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-7">
                <flux:select wire:model.live="type" label="Jenis Izin">
                    @foreach ($types as $option)
                        <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if (! $selectedType->requiresScan())
                    <div>
                        <flux:input wire:model="guest_name" label="Nama Tamu" placeholder="Masukkan minimal 1 nama tamu yang berkunjung..." required />
                        @error('guest_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <flux:textarea wire:model="reason" label="Keterangan" rows="3" placeholder="Isi alasan permohonan izin..." />
                    @error('reason') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                @if ($selectedType->requiresScan())
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Peserta & Bindamping ({{ count($members) }})</h3>
                        <p class="mt-0.5 text-[11px] text-zinc-500">Pindai QR minimal 1 bindamping dan 1 peserta dari gudep yang sama. Peserta boleh lebih dari satu.</p>

                        <ul class="mt-3 divide-y divide-zinc-100 rounded-xl border border-zinc-200 text-sm dark:divide-zinc-800 dark:border-zinc-800">
                            @forelse ($members as $index => $member)
                                <li wire:key="member-{{ $member['id'] }}" class="flex items-center justify-between gap-3 px-3 py-2.5">
                                    <div class="min-w-0">
                                        <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $member['name'] }}</span>
                                        <span class="text-[11px] text-zinc-500">{{ $member['kind'] === 'participant' ? 'Peserta' : 'Bindamping' }} &bull; NTA {{ $member['scout_id'] }} &bull; {{ $member['role'] }} &bull; {{ $member['group_name'] }}</span>
                                    </div>
                                    <flux:button wire:click="removeMember({{ $index }})" size="xs" variant="subtle" icon="x-mark" />
                                </li>
                            @empty
                                <li class="px-3 py-4 text-center text-xs italic text-zinc-400">Belum ada QR yang dipindai.</li>
                            @endforelse
                        </ul>
                        @error('members') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @endif

                <flux:button type="submit" variant="primary" icon="paper-airplane" class="w-full">Simpan Permohonan Izin</flux:button>
            </form>

            {{-- Pemindai --}}
            @if ($selectedType->requiresScan())
                <div class="space-y-4 lg:col-span-5">
                    <x-qr-scanner method="handleScan" reader-id="permission-qr-reader" />
                    @include('livewire.admin.partials.permission-manual-scan')
                </div>
            @endif
        </div>
    @elseif ($tab === 'return')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            <div class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-7">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Konfirmasi Kembali (Izin Sementara)</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Pindai QR peserta yang kembali atau gunakan daftar di bawah.</p>
                    </div>
                    <flux:button wire:click="setTab('unreturned')" size="xs" variant="primary" icon="list-bullet">
                        Buka Daftar Lengkap
                    </flux:button>
                </div>

                {{-- Daftar Singkat Peserta Belum Kembali --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                        <span>Peserta Belum Kembali ({{ $unreturnedCount }})</span>
                        <span class="text-[11px] text-zinc-400">Klik tombol check untuk konfirmasi manual</span>
                    </div>

                    <ul class="divide-y divide-zinc-100 rounded-xl border border-zinc-200 text-sm dark:divide-zinc-800 dark:border-zinc-800 max-h-96 overflow-y-auto">
                        @forelse ($unreturnedMembers as $member)
                            <li wire:key="return-member-{{ $member->id }}" class="flex items-center justify-between gap-3 p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if ($member->participant?->photo_path)
                                        <img src="{{ asset('storage/' . $member->participant->photo_path) }}" alt="{{ $member->participant->full_name }}" class="size-10 rounded-xl object-cover border border-zinc-200 dark:border-zinc-700 shrink-0">
                                    @else
                                        <div class="size-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-white font-bold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($member->participant?->full_name ?? 'P', 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-xs text-zinc-900 dark:text-zinc-100 truncate">{{ $member->participant?->full_name }}</span>
                                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">{{ $member->participant?->id_number }}</span>
                                        </div>
                                        <div class="text-[11px] text-zinc-500 truncate">
                                            Regu {{ $member->participant?->patrol?->patrol_name }} &bull; {{ $member->participant?->patrol?->scoutGroup?->school_name }}
                                        </div>
                                        <div class="text-[10px] text-amber-600 font-medium">
                                            Izin sejak {{ $member->permissionRequest?->created_at->timezone('Asia/Jakarta')->format('H:i') }} WIB ({{ $member->permissionRequest?->created_at->diffForHumans() }})
                                        </div>
                                    </div>
                                </div>
                                <flux:button wire:click="confirmReturnManual('{{ $member->id }}')" size="xs" variant="primary" icon="check" class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white">
                                    Konfirmasi
                                </flux:button>
                            </li>
                        @empty
                            <li class="p-6 text-center text-xs italic text-zinc-400">
                                Tidak ada peserta yang sedang di luar perkemahan saat ini.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-5">
                <x-qr-scanner method="handleScan" reader-id="permission-return-qr-reader" />
                @include('livewire.admin.partials.permission-manual-scan')
            </div>
        </div>
    @elseif ($tab === 'unreturned')
        <div class="space-y-5">
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-card
                    label="Peserta Belum Kembali"
                    value="{{ $unreturnedCount }}"
                    description="Sedang di luar bumi perkemahan"
                    color="amber"
                />
                <x-stat-card
                    label="Pangkalan / Gudep"
                    value="{{ $unreturnedGroupsCount }}"
                    description="Pangkalan asal peserta"
                    color="neutral"
                />
                <x-stat-card
                    label="Surat Izin Aktif"
                    value="{{ $unreturnedRequestsCount }}"
                    description="Izin sementara berjalan"
                    color="blue"
                />
            </div>

            {{-- Toolbar Pencarian & Filter --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex-1 max-w-md">
                    <flux:input wire:model.live.debounce.300ms="unreturnedSearch" icon="magnifying-glass" placeholder="Cari nama peserta, NTA, regu, sekolah, atau no surat..." clearable />
                </div>
                <div class="flex items-center gap-2">
                    <flux:select wire:model.live="unreturnedGender" class="w-36">
                        <flux:select.option value="all">Semua Golongan</flux:select.option>
                        <flux:select.option value="male">Putra</flux:select.option>
                        <flux:select.option value="female">Putri</flux:select.option>
                    </flux:select>
                    <span class="text-xs text-zinc-500 whitespace-nowrap">
                        Total: <strong class="text-zinc-800 dark:text-zinc-200">{{ count($unreturnedMembers) }}</strong> peserta
                    </span>
                </div>
            </div>

            {{-- Kartu Rinci Data Lengkap Peserta Belum Kembali --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($unreturnedMembers as $member)
                    @php
                        $participant = $member->participant;
                        $req = $member->permissionRequest;
                        $chaperoneMember = $req?->members->firstWhere('chaperone_id', '!=', null);
                        $chaperone = $chaperoneMember?->chaperone;
                    @endphp
                    <div wire:key="unreturned-card-{{ $member->id }}" class="flex flex-col justify-between rounded-2xl border border-amber-200/80 bg-white p-5 shadow-xs dark:border-amber-900/40 dark:bg-zinc-900 transition hover:shadow-md">
                        <div class="space-y-4">
                            {{-- Header Kartu: Profil Peserta --}}
                            <div class="flex items-start gap-3.5">
                                @if ($participant?->photo_path)
                                    <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->full_name }}" class="size-14 rounded-2xl object-cover border-2 border-amber-300 dark:border-amber-700 shadow-sm shrink-0">
                                @else
                                    <div class="size-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white font-extrabold flex items-center justify-center text-base shadow-sm shrink-0">
                                        {{ strtoupper(substr($participant?->full_name ?? 'P', 0, 2)) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">
                                            {{ $participant?->full_name }}
                                        </h3>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold border border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            DI LUAR
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-1.5 mt-1 text-[11px] text-zinc-500">
                                        <span class="font-mono font-semibold text-zinc-700 dark:text-zinc-300">{{ $participant?->id_number }}</span>
                                        <span>&bull;</span>
                                        <span>NTA: {{ $participant?->scout_id ?: '-' }}</span>
                                        <span>&bull;</span>
                                        <span class="font-medium text-amber-600 dark:text-amber-400 uppercase text-[10px]">
                                            {{ $participant?->isLeader() ? 'PINRU' : ($participant?->isAssistantLeader() ? 'WAPINRU' : 'ANGGOTA') }}
                                        </span>
                                        <span>&bull;</span>
                                        <span>{{ $participant?->isMale() ? 'Putra' : 'Putri' }}</span>
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-400 font-medium">
                                        Regu {{ $participant?->patrol?->patrol_name }} &bull; {{ $participant?->patrol?->scoutGroup?->school_name }}
                                    </div>
                                    @if ($participant?->patrol?->scoutGroup?->kwarran_name)
                                        <div class="text-[10px] text-zinc-400">
                                            Kwarran {{ $participant->patrol->scoutGroup->kwarran_name }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Informasi Detail Surat Izin --}}
                            <div class="rounded-xl border border-zinc-100 bg-zinc-50/80 p-3 text-xs space-y-2 dark:border-zinc-800 dark:bg-zinc-800/40">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] text-zinc-400 font-medium">No. Surat Izin:</span>
                                    <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-200 text-[11px]">{{ $req?->letterNumber() }}</span>
                                </div>

                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[11px] text-zinc-400 font-medium shrink-0">Alasan Izin:</span>
                                    <span class="text-zinc-700 dark:text-zinc-300 text-right leading-tight font-medium">{{ $req?->reason }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] text-zinc-400 font-medium">Waktu Izin Dibuat:</span>
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                        {{ $req?->created_at->timezone('Asia/Jakarta')->isoFormat('D MMM Y • HH:mm') }} WIB
                                    </span>
                                </div>

                                <div class="flex items-center justify-between pt-1 border-t border-zinc-200/60 dark:border-zinc-700/60">
                                    <span class="text-[11px] text-amber-700 dark:text-amber-400 font-bold">Lama Berada di Luar:</span>
                                    <span class="font-bold text-amber-600 dark:text-amber-400">
                                        {{ $req?->created_at->diffForHumans(['parts' => 2]) }}
                                    </span>
                                </div>

                                @if ($chaperone)
                                    <div class="flex items-center justify-between pt-1 border-t border-zinc-200/60 dark:border-zinc-700/60 text-[11px]">
                                        <span class="text-zinc-400">Bindamping Pendamping:</span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                            {{ $chaperone->full_name }}
                                            @if ($chaperone->phone_number)
                                                ({{ $chaperone->phone_number }})
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer Aksi --}}
                        <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                @if ($req)
                                    <flux:button href="{{ route('admin.permission-requests.letter', $req->id) }}" target="_blank" size="xs" variant="subtle" icon="printer">
                                        Surat
                                    </flux:button>
                                @endif
                                @if ($participant)
                                    <flux:button href="{{ route('digital-cards.participant', $participant->id) }}" target="_blank" size="xs" variant="subtle" icon="identification">
                                        Kartu
                                    </flux:button>
                                    <flux:button href="{{ route('admin.participants.show', $participant->id) }}" target="_blank" size="xs" variant="subtle" icon="user">
                                        Profil
                                    </flux:button>
                                @endif
                            </div>

                            <flux:button wire:click="confirmReturnManual('{{ $member->id }}')" size="xs" variant="primary" icon="check" class="bg-emerald-600 hover:bg-emerald-700 text-white">
                                Konfirmasi Kembali
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-8">
                        <flux:icon name="check-circle" class="size-12 mx-auto text-emerald-500 mb-2" />
                        <h4 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Seluruh Peserta Telah Berada di Perkemahan</h4>
                        <p class="text-xs text-zinc-500 mt-1 max-w-md mx-auto">
                            Tidak ada peserta yang sedang berstatus di luar bumi perkemahan (izin sementara). Seluruh peserta telah dikonfirmasi kepulangannya.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <livewire:admin.permission-requests-list lazy />
    @endif

    {{-- Modal Verifikasi 2 Tahap: Konfirmasi Kembali Peserta --}}
    @if ($showReturnModal && $returnParticipantData)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            x-data
            @keydown.escape.window="$wire.closeReturnModal()">
            <div class="w-full max-w-lg rounded-3xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900 overflow-y-auto max-h-[92vh] space-y-5"
                @click.outside="$wire.closeReturnModal()">

                {{-- Header Modal --}}
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-800 shrink-0">
                            <flux:icon name="shield-check" class="size-5" />
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                Tahap 2 dari 2 &bull; Verifikasi Fisik
                            </div>
                            <h2 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100 mt-1">
                                Konfirmasi Kembali Peserta
                            </h2>
                        </div>
                    </div>
                    <button type="button" wire:click="closeReturnModal" class="size-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                {{-- Alert Petunjuk Verifikasi --}}
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-200 flex items-start gap-2.5">
                    <flux:icon name="exclamation-circle" class="size-4 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <div>
                        <span class="font-bold">Pastikan peserta fisik telah berada di depan Anda.</span>
                        <p class="mt-0.5 text-[11px] text-amber-800/90 dark:text-amber-300/80">
                            Cek kesesuaian wajah/foto, nama, dan identitas kontingen peserta di bawah ini sebelum mengesahkan kepulangan ke perkemahan.
                        </p>
                    </div>
                </div>

                {{-- Kartu Profil Peserta --}}
                <div class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                    @if ($returnParticipantData['photo_path'])
                        <img src="{{ asset('storage/' . $returnParticipantData['photo_path']) }}" alt="{{ $returnParticipantData['name'] }}" class="size-20 rounded-2xl object-cover border border-zinc-200 dark:border-zinc-700 shadow-sm shrink-0">
                    @else
                        <div class="size-20 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white font-black flex items-center justify-center text-2xl shadow-sm shrink-0">
                            {{ strtoupper(substr($returnParticipantData['name'], 0, 2)) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-extrabold text-zinc-900 dark:text-zinc-100 truncate">
                            {{ $returnParticipantData['name'] }}
                        </h3>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs">
                            @if ($returnParticipantData['is_male'])
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 font-bold text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 text-[10px]">Putra</span>
                            @elseif ($returnParticipantData['is_female'])
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 font-bold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px]">Putri</span>
                            @endif
                            <span class="font-mono text-zinc-500 dark:text-zinc-400 text-[11px]">ID: {{ $returnParticipantData['id_number'] }}</span>
                            <span class="text-zinc-300 dark:text-zinc-700">&bull;</span>
                            <span class="font-mono text-zinc-500 dark:text-zinc-400 text-[11px]">NTA: {{ $returnParticipantData['scout_id'] }}</span>
                        </div>
                        <div class="mt-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate">
                            Regu {{ $returnParticipantData['patrol_name'] }} &bull; {{ $returnParticipantData['school_name'] }}
                        </div>
                        <div class="text-[11px] text-zinc-500 truncate">
                            Kwarran {{ $returnParticipantData['kwarran_name'] }}
                        </div>
                    </div>
                </div>

                {{-- Detail Izin Sementara --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-800/20 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-zinc-500">Nomor Surat Izin</span>
                        <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $returnParticipantData['letter_number'] }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-zinc-500 shrink-0">Alasan Izin</span>
                        <span class="font-medium text-right text-zinc-800 dark:text-zinc-200">{{ $returnParticipantData['reason'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-zinc-500">Waktu Berangkat</span>
                        <div class="text-right">
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $returnParticipantData['departure_time'] }}</span>
                            <span class="block text-[10px] text-amber-600 font-bold">Keluar {{ $returnParticipantData['departure_diff'] }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-500">Pendamping Bertanggung Jawab</span>
                        <div class="text-right">
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $returnParticipantData['chaperone_name'] }}</span>
                            @if ($returnParticipantData['chaperone_phone'] !== '-')
                                <span class="block text-[10px] text-zinc-400 font-mono">{{ $returnParticipantData['chaperone_phone'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi Verifikasi --}}
                <div class="flex items-center justify-end gap-2 pt-2">
                    <flux:button type="button" variant="subtle" wire:click="closeReturnModal">
                        Batal
                    </flux:button>
                    <flux:button type="button" variant="primary" icon="check-badge" wire:click="processReturnConfirmation" class="bg-emerald-600 hover:bg-emerald-700 text-white">
                        Ya, Konfirmasi Kembali
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
