<section class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center gap-2 border-b border-zinc-100 pb-3 dark:border-zinc-800">
        <flux:icon name="bell-alert" class="size-5 text-amber-600 dark:text-amber-400" />
        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Perlu Ditindaklanjuti</h3>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
        {{-- Antrean verifikasi --}}
        <div class="lg:col-span-7">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Antrean Verifikasi ({{ $pendingTotal }})</h4>
                <flux:button href="{{ route('admin.scout-groups.index') }}" wire:navigate size="xs" variant="subtle" icon-trailing="arrow-right">Buka Antrean</flux:button>
            </div>
            <ul class="mt-2 divide-y divide-zinc-100 rounded-xl border border-zinc-100 dark:divide-zinc-800 dark:border-zinc-800">
                @forelse ($pendingGroups as $group)
                    <li wire:key="pending-{{ $group->id }}" class="flex items-center justify-between gap-3 px-3 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $group->school_name }}</p>
                            <p class="text-[11px] text-zinc-500">{{ $group->registration_code }} &bull; diajukan {{ $group->updated_at->timezone('Asia/Jakarta')->locale('id')->diffForHumans() }}</p>
                        </div>
                        <flux:button href="{{ route('admin.scout-groups.show', $group->id) }}" wire:navigate size="xs" variant="primary" icon="shield-check">Tinjau</flux:button>
                    </li>
                @empty
                    <li class="px-3 py-6 text-center text-xs italic text-zinc-400">Tidak ada pendaftaran yang menunggu verifikasi.</li>
                @endforelse
            </ul>
        </div>

        {{-- Ringkasan status --}}
        <dl class="space-y-2 text-sm lg:col-span-5">
            <div class="flex items-center justify-between gap-3 rounded-xl bg-zinc-50 px-3 py-2 dark:bg-zinc-800/50">
                <dt class="text-zinc-600 dark:text-zinc-300">Pendaftaran gudep</dt>
                <dd class="text-right">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $registrationOpen ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300' }}">{{ $registrationOpen ? 'Dibuka' : 'Ditutup' }}</span>
                    @if ($registrationOpen && $registrationEnd)
                        <span class="block text-[10px] text-zinc-400">s.d. {{ $registrationEnd->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</span>
                    @endif
                </dd>
            </div>
            <div class="flex items-center justify-between gap-3 rounded-xl bg-zinc-50 px-3 py-2 dark:bg-zinc-800/50">
                <dt class="text-zinc-600 dark:text-zinc-300">Perlu revisi</dt>
                <dd class="font-bold text-orange-600">{{ $revisionTotal }} gudep</dd>
            </div>
            <div class="flex items-center justify-between gap-3 rounded-xl bg-zinc-50 px-3 py-2 dark:bg-zinc-800/50">
                <dt class="text-zinc-600 dark:text-zinc-300">Terverifikasi</dt>
                <dd class="font-bold text-emerald-600">{{ $verifiedTotal }} gudep</dd>
            </div>
            <a href="{{ route('admin.registration-invitations.index') }}" wire:navigate class="flex items-center justify-between gap-3 rounded-xl bg-zinc-50 px-3 py-2 hover:bg-zinc-100 dark:bg-zinc-800/50 dark:hover:bg-zinc-800">
                <span class="text-zinc-600 dark:text-zinc-300">Kode undangan tersedia</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $invitationsUsable }} / {{ $invitationsTotal }}</span>
            </a>
            <a href="{{ route('admin.permission-requests.index') }}" wire:navigate class="flex items-center justify-between gap-3 rounded-xl px-3 py-2 {{ $outstandingPermissions ? 'bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/30' : 'bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800/50' }}">
                <span class="text-zinc-600 dark:text-zinc-300">Izin sementara belum kembali</span>
                <span class="font-bold {{ $outstandingPermissions ? 'text-amber-700 dark:text-amber-400' : 'text-zinc-900 dark:text-zinc-100' }}">{{ $outstandingPermissions }}</span>
            </a>
            <a href="{{ route('attendance.index') }}" wire:navigate class="flex items-center justify-between gap-3 rounded-xl bg-zinc-50 px-3 py-2 hover:bg-zinc-100 dark:bg-zinc-800/50 dark:hover:bg-zinc-800">
                <span class="text-zinc-600 dark:text-zinc-300">Sesi presensi aktif</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $activeSessions }}</span>
            </a>
        </dl>
    </div>
</section>
