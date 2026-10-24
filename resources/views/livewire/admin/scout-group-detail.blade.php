@php($g = $this->group)

<div>
    @if(! $g)
        <div class="p-10 text-center text-sm text-zinc-500">Data pangkalan tidak ditemukan.</div>
    @else
        <div class="border-b border-zinc-100 bg-gradient-to-r from-amber-500/10 via-transparent to-transparent p-6 pr-16 dark:border-zinc-800">
            <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-400">{{ $g->registration_code }}</span>
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $g->school_name }}</h2>
            <div class="mt-1 text-xs text-zinc-500">{{ $g->kwarran_name }} &bull; Gudep {{ $g->group_number ?: '-' }}</div>
        </div>

        <div class="max-h-[70vh] space-y-6 overflow-y-auto p-6 text-xs">
            {{-- Data Pangkalan --}}
            <section>
                <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">1. Data Pangkalan</h4>
                <div class="grid grid-cols-1 gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40 sm:grid-cols-3">
                    <div><span class="block text-zinc-400">Gudep Putra:</span><span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $g->male_group_number ?: '-' }}</span></div>
                    <div><span class="block text-zinc-400">Gudep Putri:</span><span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $g->female_group_number ?: '-' }}</span></div>
                    <div><span class="block text-zinc-400">Kwarran:</span><span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->kwarran_name ?: '-' }}</span></div>
                    <div class="sm:col-span-3"><span class="block text-zinc-400">Alamat:</span><span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->address ?: '-' }}</span></div>
                    <div><span class="block text-zinc-400">Penanggung Jawab (PIC):</span><span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->contact_person ?: '-' }}</span></div>
                    <div><span class="block text-zinc-400">Kontak PIC:</span><span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $g->contact_phone ?: '-' }}</span></div>
                    <div><span class="block text-zinc-400">Terverifikasi:</span><span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $g->verified_at?->translatedFormat('d F Y, H:i') ?: '-' }} WIB</span></div>
                </div>
            </section>

            {{-- Regu & Peserta --}}
            <section>
                <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">2. Regu & Peserta</h4>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @forelse($g->patrols as $patrol)
                        <div class="rounded-2xl border border-zinc-100 p-4 dark:border-zinc-800">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">Regu {{ $patrol->patrol_name }}</span>
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $patrol->isBoys() ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                                    {{ $patrol->isBoys() ? 'Putra' : 'Putri' }}
                                </span>
                            </div>
                            <ol class="space-y-1">
                                @foreach($patrol->orderedParticipants() as $participant)
                                    <li class="flex items-center justify-between gap-2">
                                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $loop->iteration }}. {{ $participant->full_name }}</span>
                                        <span class="text-[10px] text-zinc-500">{{ $participant->role_in_patrol?->label() }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @empty
                        <div class="text-zinc-400 italic">Belum ada regu.</div>
                    @endforelse
                </div>
            </section>

            {{-- Bindamping --}}
            <section>
                <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">3. Pembina Pendamping</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @forelse($g->chaperones as $chaperone)
                        <div class="rounded-2xl border border-zinc-100 p-4 dark:border-zinc-800">
                            <div class="font-bold text-zinc-900 dark:text-white">{{ $chaperone->full_name }}</div>
                            <div class="text-[11px] text-zinc-500">{{ $chaperone->gender === 'male' ? 'Pembina Putra' : 'Pembina Putri' }} &bull; NTA {{ $chaperone->scout_id ?: '-' }}</div>
                            <div class="font-mono text-[11px] text-zinc-500">{{ $chaperone->phone_number ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="text-zinc-400 italic">Belum ada bindamping.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="flex justify-end border-t border-zinc-100 p-4 dark:border-zinc-800">
            <flux:button href="{{ route('admin.gudep-directory.show', $g->id) }}" wire:navigate variant="primary" icon="arrow-top-right-on-square">
                Buka Berkas Lengkap
            </flux:button>
        </div>
    @endif
</div>
