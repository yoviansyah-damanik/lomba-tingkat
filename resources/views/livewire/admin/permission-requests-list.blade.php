<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <flux:select wire:model.live="typeFilter" class="max-w-xs">
            <flux:select.option value="all">Semua Jenis Izin</flux:select.option>
            @foreach ($types as $option)
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="statusFilter" class="max-w-[12rem]">
            <flux:select.option value="all">Semua Status</flux:select.option>
            <flux:select.option value="active">Belum Kembali</flux:select.option>
            <flux:select.option value="completed">Selesai</flux:select.option>
        </flux:select>
        <span class="ml-auto text-xs text-zinc-500">{{ $requests->total() }} permohonan</span>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50/75 text-xs font-semibold text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="py-3 pl-5 pr-3">Waktu</th>
                        <th class="px-3 py-3">Jenis & Keterangan</th>
                        <th class="px-3 py-3">Gudep / Tamu</th>
                        <th class="px-3 py-3">Peserta & Bindamping</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="py-3 pl-3 pr-5 text-right">Surat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($requests as $request)
                        <tr wire:key="permission-{{ $request->id }}" class="align-top">
                            <td class="whitespace-nowrap py-3 pl-5 pr-3 text-xs text-zinc-500">{{ $request->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
                            <td class="px-3 py-3">
                                <span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-bold {{ $request->type->badgeClasses() }}">{{ $request->type->label() }}</span>
                                <p class="mt-1 text-xs text-zinc-700 dark:text-zinc-300">{{ $request->reason }}</p>
                            </td>
                            <td class="px-3 py-3 text-xs text-zinc-700 dark:text-zinc-300">
                                @if ($request->scoutGroup)
                                    <span class="font-semibold">{{ $request->scoutGroup->school_name }}</span>
                                @elseif ($request->guest_name)
                                    Tamu: <span class="font-semibold">{{ $request->guest_name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs">
                                @forelse ($request->members as $member)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $member->displayName() }}</span>
                                        <span class="text-[10px] text-zinc-400">({{ $member->participant_id ? 'Peserta' : 'Bindamping' }})</span>
                                        @if ($request->type->requiresReturn() && $member->participant_id)
                                            @if ($member->returned_at)
                                                <span class="text-[10px] font-semibold text-emerald-600">kembali {{ $member->returned_at->timezone('Asia/Jakarta')->format('H:i') }}</span>
                                            @else
                                                <span class="text-[10px] font-semibold text-amber-600">belum kembali</span>
                                            @endif
                                        @endif
                                    </div>
                                @empty
                                    <span class="text-zinc-400">-</span>
                                @endforelse
                            </td>
                            <td class="px-3 py-3">
                                @if ($request->status === 'active')
                                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Belum Kembali</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Selesai</span>
                                @endif
                            </td>
                            <td class="py-3 pl-3 pr-5 text-right">
                                <flux:button href="{{ route('admin.permission-requests.letter', $request->id) }}" target="_blank" size="xs" variant="subtle" icon="printer">Cetak</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-zinc-400">Belum ada permohonan izin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
