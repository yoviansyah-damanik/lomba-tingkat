{{-- Regu & Peserta --}}
<section class="space-y-4">
    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Regu & Peserta</h3>

    @forelse($g->patrols as $patrol)
        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900" wire:key="patrol-{{ $patrol->id }}">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-3 dark:border-zinc-800">
                <span class="text-sm font-bold text-zinc-900 dark:text-white">Regu {{ $patrol->patrol_name }}</span>
                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $patrol->isBoys() ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                    {{ $patrol->isBoys() ? 'Putra' : 'Putri' }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/75 text-zinc-500 dark:bg-zinc-800/50">
                        <tr>
                            <th class="py-2.5 pl-6 pr-3">No</th>
                            <th class="px-3 py-2.5">Nama</th>
                            <th class="px-3 py-2.5">Jabatan</th>
                            <th class="px-3 py-2.5">NTA</th>
                            <th class="px-3 py-2.5">ID</th>
                            <th class="px-3 py-2.5">Tempat, Tanggal Lahir</th>
                            <th class="px-3 py-2.5">Kelas</th>
                            <th class="py-2.5 pl-3 pr-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                        @foreach($patrol->orderedParticipants() as $participant)
                            <tr>
                                <td class="py-2.5 pl-6 pr-3">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2.5 font-semibold text-zinc-900 dark:text-white">
                                    {{ $participant->full_name }}
                                </td>
                                <td class="px-3 py-2.5">{{ $participant->role_in_patrol?->label() ?: '-' }}</td>
                                <td class="px-3 py-2.5 font-mono">{{ $participant->scout_id ?: '-' }}</td>
                                <td class="px-3 py-2.5 font-mono">{{ $participant->id_number ?: '-' }}</td>
                                <td class="px-3 py-2.5">{{ $participant->birth_place ?: '-' }}, {{ $participant->birth_date?->translatedFormat('d M Y') ?: '-' }}</td>
                                <td class="px-3 py-2.5">{{ $participant->class_grade ?: '-' }}</td>
                                <td class="py-2 pl-3 pr-6 text-right">
                                    <flux:button href="{{ route('admin.gudep-directory.participants.show', [$g->id, $participant->id]) }}" wire:navigate variant="subtle" size="xs" icon="eye">
                                        Lihat Detail
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center text-xs italic text-zinc-400 dark:border-zinc-700">Belum ada regu.</div>
    @endforelse
</section>
