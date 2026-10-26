<div class="space-y-6">
    {{-- Heading Section Konsisten --}}
    <x-heading-section
        title="Audit Penilaian & Log Sistem"
        subtitle="Pengawasan integritas skor dewan juri, transparansi rekapitulasi, dan jejak audit pembukaan kunci nilai."
    >
        <x-slot:actions>
            <flux:button
                icon="trophy"
                variant="subtle"
                href="{{ route('admin.standings.index') }}"
                wire:navigate
            >
                Klasemen & Rekap
            </flux:button>
        </x-slot:actions>
    </x-heading-section>

    {{-- Kartu Statistik Ringkasan --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-stat-card
            label="Total Penilaian"
            :value="$stats['total_assessments']"
            unit="Data"
            description="Penilaian dari seluruh juri"
            color="neutral"
            icon="clipboard-document-list"
        />

        <x-stat-card
            label="Telah Difinalisasi"
            :value="$stats['finalized']"
            unit="Terkunci"
            description="Nilai sah masuk klasemen"
            color="emerald"
            icon="lock-closed"
        />

        <x-stat-card
            label="Draf Terbuka"
            :value="$stats['draft']"
            unit="Draf"
            description="Belum difinalisasi juri"
            color="amber"
            icon="pencil"
        />

        <x-stat-card
            label="Jejak Audit"
            :value="$stats['total_logs']"
            unit="Log"
            description="Aktivitas terekam sistem"
            color="indigo"
            icon="shield-check"
        />
    </div>

    {{-- Tab Pilihan: Penilaian Juri vs Log Audit --}}
    <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-2">
        <button
            type="button"
            wire:click="$set('activeTab', 'assessments')"
            class="px-4 py-2 text-xs font-bold rounded-xl transition {{ $activeTab === 'assessments' ? 'bg-amber-500 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
        >
            Daftar Penilaian Juri
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'audit_logs')"
            class="px-4 py-2 text-xs font-bold rounded-xl transition {{ $activeTab === 'audit_logs' ? 'bg-amber-500 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
        >
            Riwayat Audit Trail Log
        </button>
    </div>

    @if($activeTab === 'assessments')
        {{-- Filter & Search Penilaian --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                <div class="sm:col-span-6">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama regu, pangkalan sekolah, atau nama juri..."
                        icon="magnifying-glass"
                        clearable
                    />
                </div>
                <div class="sm:col-span-3">
                    <flux:select wire:model.live="competitionFilter">
                        <flux:select.option value="all">Semua Mata Lomba</flux:select.option>
                        @foreach($competitions as $comp)
                            <flux:select.option value="{{ $comp->id }}">{{ $comp->code }} - {{ $comp->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="sm:col-span-3">
                    <flux:select wire:model.live="statusFilter">
                        <flux:select.option value="all">Semua Status</flux:select.option>
                        <flux:select.option value="finalized">Difinalisasi (Terkunci)</flux:select.option>
                        <flux:select.option value="draft">Draf Terbuka</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        {{-- Tabel Penilaian Juri Transparan --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200/80 text-zinc-500 font-bold uppercase tracking-wider dark:bg-zinc-800/50 dark:border-zinc-800">
                        <tr>
                            <th class="p-3.5">Mata Lomba</th>
                            <th class="p-3.5">NPP</th>
                            <th class="p-3.5">Nama Regu</th>
                            <th class="p-3.5">Pangkalan Sekolah</th>
                            <th class="p-3.5">Juri Penilai</th>
                            <th class="p-3.5 text-center">Nilai Mentah</th>
                            <th class="p-3.5 text-center">Nilai Akhir</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        @forelse($assessments as $ass)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition">
                                <td class="p-3.5 font-medium text-zinc-900 dark:text-zinc-100">
                                    <div class="font-bold">{{ $ass->competition?->title ?? '-' }}</div>
                                    <span class="text-[11px] text-zinc-400">Sesi: {{ $ass->schedule?->session_name ?? '-' }}</span>
                                </td>
                                <td class="p-3.5">
                                    <x-npp-badge :npp="$ass->patrol?->npp" />
                                </td>
                                <td class="p-3.5 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $ass->patrol?->patrol_name ?? '-' }}
                                </td>
                                <td class="p-3.5 text-zinc-600 dark:text-zinc-300">
                                    {{ $ass->patrol?->scoutGroup?->school_name ?? '-' }}
                                </td>
                                <td class="p-3.5">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $ass->judge?->name ?? '-' }}</div>
                                    <span class="text-[11px] text-zinc-400">{{ $ass->judge?->username ?? '-' }}</span>
                                </td>
                                <td class="p-3.5 text-center font-mono text-zinc-500">
                                    {{ number_format((float) $ass->raw_total_score, 2) }}
                                </td>
                                <td class="p-3.5 text-center font-mono font-bold text-sm text-amber-700 dark:text-amber-400">
                                    {{ number_format((float) $ass->final_score, 2) }}
                                </td>
                                <td class="p-3.5 text-center">
                                    @if($ass->isFinalized())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            <flux:icon name="lock-closed" class="size-3" />
                                            Final Terkunci
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            <flux:icon name="pencil" class="size-3" />
                                            Draf Terbuka
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right">
                                    @if($ass->isLocked())
                                        <flux:button
                                            icon="lock-open"
                                            size="sm"
                                            variant="subtle"
                                            wire:click="openUnlockModal('{{ $ass->id }}')"
                                        >
                                            Buka Kunci
                                        </flux:button>
                                    @else
                                        <span class="text-[11px] text-zinc-400 italic">Draf Juri</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-xs text-zinc-500">
                                    Tidak ada data penilaian yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assessments->hasPages())
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                    {{ $assessments->links() }}
                </div>
            @endif
        </div>

    @else
        {{-- Riwayat Audit Trail Log --}}
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 mb-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Cari aksi, pengguna, atau alasan perbaikan nilai..."
                icon="magnifying-glass"
                clearable
            />
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50 border-b border-zinc-200/80 text-zinc-500 font-bold uppercase tracking-wider dark:bg-zinc-800/50 dark:border-zinc-800">
                        <tr>
                            <th class="p-3.5">Waktu Kejadian</th>
                            <th class="p-3.5">Aktor / Pengguna</th>
                            <th class="p-3.5">Aksi Sistem</th>
                            <th class="p-3.5">Entitas Model</th>
                            <th class="p-3.5">Alasan / Catatan Perbaikan</th>
                            <th class="p-3.5">IP & Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition">
                                <td class="p-3.5 text-zinc-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ $log->created_at?->format('d/m/Y H:i:s') ?? '-' }}
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $log->user?->name ?? 'Sistem' }}</div>
                                    <span class="text-[10px] text-zinc-400">{{ $log->user?->username ?? '-' }}</span>
                                    @if($log->acting_role === 'admin')
                                        <span class="mt-1 block w-fit rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">Administrator</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    @php
                                        $badgeColor = match($log->action) {
                                            'score_finalized' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                            'score_unlocked' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                            'score_draft_saved', 'score_draft_created' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                            default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-bold font-mono {{ $badgeColor }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-zinc-500 font-mono text-[11px]">
                                    {{ class_basename($log->model_type) }} #{{ substr($log->model_id, 0, 8) }}
                                </td>
                                <td class="p-3.5 text-zinc-700 dark:text-zinc-300 max-w-xs">
                                    {{ $log->reason ?: '-' }}
                                </td>
                                <td class="p-3.5 text-zinc-400 font-mono text-[11px]">
                                    <div>{{ $log->ip_address ?: '-' }}</div>
                                    <div class="text-[10px] truncate max-w-[140px]">{{ $log->user_agent ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-xs text-zinc-500">
                                    Belum ada catatan riwayat audit trail log.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($auditLogs->hasPages())
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Pembukaan Kunci Nilai --}}
    @if($isUnlockModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400">
                        <flux:icon name="lock-open" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                            Buka Kunci Penilaian Juri
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 leading-relaxed">
                            Aksi ini akan mengembalikan status penilaian ke <strong>Draf Terbuka</strong> sehingga dewan juri terkait dapat memperbaiki input nilai.
                        </p>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Alasan Pembukaan Kunci / Koreksi (Wajib Masuk Audit Log):
                    </label>
                    <textarea
                        wire:model="unlockReason"
                        rows="3"
                        placeholder="Masukkan alasan pembukaan kunci (misal: pengajuan revisi teknis pos nomor...)"
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 focus:border-amber-500 focus:outline-hidden dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    ></textarea>
                    @error('unlockReason')
                        <span class="text-[11px] text-rose-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <flux:button
                        variant="subtle"
                        wire:click="$set('isUnlockModalOpen', false)"
                    >
                        Batal
                    </flux:button>
                    <flux:button
                        variant="danger"
                        icon="lock-open"
                        wire:click="unlockAssessment"
                    >
                        Buka Kunci & Log Audit
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
