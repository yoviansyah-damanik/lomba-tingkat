<div>
    @if (! $this->user)
        <div class="p-10 text-center text-sm text-zinc-500">Pengguna tidak ditemukan.</div>
    @else
        <div class="border-b border-zinc-100 bg-linear-to-r from-amber-500/10 via-transparent to-transparent p-5 pr-16 dark:border-zinc-800">
            <h2 class="text-base font-bold text-zinc-900 dark:text-white">{{ $this->user->name }}</h2>
            <p class="font-mono text-xs text-zinc-500">{{ $this->user->username }} &bull; {{ $this->user->roleLabel() }}</p>
        </div>

        <div class="flex gap-2 border-b border-zinc-100 px-5 py-3 dark:border-zinc-800" role="tablist">
            <flux:button wire:click="$set('tab', 'login')" size="xs" :variant="$tab === 'login' ? 'primary' : 'subtle'" icon="arrow-right-end-on-rectangle">Riwayat Login ({{ $this->logins->count() }})</flux:button>
            <flux:button wire:click="$set('tab', 'audit')" size="xs" :variant="$tab === 'audit' ? 'primary' : 'subtle'" icon="clipboard-document-list">Audit Log ({{ $this->audits->count() }})</flux:button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto">
            @if ($tab === 'login')
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-zinc-50 text-zinc-500 dark:bg-zinc-800/80">
                        <tr>
                            <th class="px-5 py-2.5">Waktu</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5">IP</th>
                            <th class="px-3 py-2.5">Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->logins as $login)
                            <tr wire:key="login-{{ $login->id }}">
                                <td class="whitespace-nowrap px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-300">{{ $login->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $login->status === 'success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300' }}">{{ $login->status === 'success' ? 'Berhasil' : 'Gagal' }}</span>
                                </td>
                                <td class="px-3 py-2.5 font-mono text-zinc-500">{{ $login->ip_address ?: '-' }}</td>
                                <td class="max-w-[16rem] truncate px-3 py-2.5 text-zinc-500" title="{{ $login->user_agent }}">{{ $login->user_agent ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center italic text-zinc-400">Belum ada riwayat login tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-zinc-50 text-zinc-500 dark:bg-zinc-800/80">
                        <tr>
                            <th class="px-5 py-2.5">Waktu</th>
                            <th class="px-3 py-2.5">Aksi</th>
                            <th class="px-3 py-2.5">Peran</th>
                            <th class="px-3 py-2.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->audits as $audit)
                            <tr wire:key="audit-{{ $audit->id }}" class="align-top">
                                <td class="whitespace-nowrap px-5 py-2.5 font-mono text-zinc-600 dark:text-zinc-300">{{ $audit->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-200">{{ $audit->action }}</span>
                                    <span class="block text-[10px] text-zinc-400">{{ $audit->user_id === $this->userId ? 'dilakukan pengguna ini' : 'dilakukan oleh '.($audit->user?->name ?? 'sistem') }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    @if ($audit->acting_role === 'admin')
                                        <span class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">Administrator</span>
                                    @elseif ($audit->acting_role === 'judge')
                                        <span class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] font-bold text-sky-800 dark:bg-sky-950 dark:text-sky-300">Juri</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-zinc-600 dark:text-zinc-300">{{ $audit->reason ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center italic text-zinc-400">Belum ada audit log untuk pengguna ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    @endif
</div>
