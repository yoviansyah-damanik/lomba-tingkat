        {{-- Profil Card Bindamping --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if($chaperone->photo_path)
                        <button type="button"
                                @click="openDocModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto 3x4)', '{{ pathinfo($chaperone->photo_path, PATHINFO_EXTENSION) }}')"
                                class="group relative size-20 rounded-2xl overflow-hidden border-2 border-indigo-500 shadow-sm shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                title="Klik untuk memperbesar foto">
                            <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:icon name="magnifying-glass-plus" class="size-6 text-white" />
                            </span>
                        </button>
                    @else
                        <div class="size-20 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-zinc-400 font-bold text-2xl shrink-0">
                            {{ strtoupper(substr($chaperone->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $chaperone->gender === 'male' ? 'Pembina Pendamping Putra' : 'Pembina Pendamping Putri' }}
                            </span>
                        </div>
                        <h2 class="mt-1 text-lg sm:text-xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $chaperone->full_name }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Pangkalan: <strong class="text-zinc-700 dark:text-zinc-300">{{ $scoutGroup?->school_name ?? '-' }}</strong> &bull; Kwarran: {{ $scoutGroup?->kwarran_name ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-right text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                    <span class="text-zinc-400 block text-[11px]">Status Pas Foto:</span>
                    @if($chaperone->photo_path)
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                            Foto Resmi Terunggah
                        </span>
                    @else
                        <span class="text-sm font-bold text-rose-600 dark:text-rose-400">
                            Belum Ada Pas Foto
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 1: Berkas Yang Diunggah (Dapat Ditampilkan dalam Modal) --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Berkas Unggahan Pembina Pendamping</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Klik tombol pratinjau untuk menampilkan berkas di dalam modal dengan fitur zoom in / out</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex items-center justify-between p-4 rounded-xl border {{ $chaperone->photo_path ? 'border-emerald-200 bg-emerald-50/20 dark:border-emerald-800/40 dark:bg-emerald-950/10' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/30' }}">
                    <div class="flex items-center gap-3">
                        @if($chaperone->photo_path)
                            <button type="button"
                                    @click="openDocModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} (Pas Foto)', '{{ pathinfo($chaperone->photo_path, PATHINFO_EXTENSION) }}')"
                                    class="group relative size-14 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 shrink-0 cursor-zoom-in focus:outline-none"
                                    title="Klik untuk memperbesar">
                                <img src="{{ asset('storage/' . $chaperone->photo_path) }}" alt="{{ $chaperone->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                                <span class="absolute inset-0 flex items-center justify-center bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:icon name="magnifying-glass-plus" class="size-4 text-white" />
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
                        <div class="flex items-center gap-1.5">
                            <flux:button size="xs" variant="primary" icon="eye"
                                         @click="openDocModal('{{ asset('storage/' . $chaperone->photo_path) }}', '{{ addslashes($chaperone->full_name) }} — Pas Foto 3x4', '{{ pathinfo($chaperone->photo_path, PATHINFO_EXTENSION) }}')">
                                Pratinjau File
                            </flux:button>
                            <flux:button href="{{ asset('storage/' . $chaperone->photo_path) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square">
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 2: Biodata Pribadi & Kontak --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Biodata Pribadi &amp; Kontak</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
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
        @php
            $chaperoneEdu = $chaperone->formal_education ?: \App\Models\Chaperone::defaultFormalEducation();
            $chaperoneExp = $chaperone->scout_experiences ?: \App\Models\Chaperone::defaultScoutExperiences();
            $chaperoneTrain = $chaperone->scout_trainings ?: \App\Models\Chaperone::defaultScoutTrainings();
        @endphp

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Pendidikan Formal</h3>
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
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Golongan Pramuka</h3>
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

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Kursus Kepramukaan</h3>
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

        @if(is_array($chaperone->other_trainings) && collect($chaperone->other_trainings)->filter(fn ($t) => filled($t['name'] ?? null))->isNotEmpty())
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Kursus / Pelatihan Lainnya</h3>
                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th class="px-3 py-2 font-semibold">Nama</th>
                                <th class="px-3 py-2 font-semibold">Tahun</th>
                                <th class="px-3 py-2 font-semibold">Tempat</th>
                                <th class="px-3 py-2 font-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($chaperone->other_trainings as $ot)
                                @continue(blank($ot['name'] ?? null))
                                <tr>
                                    <td class="px-3 py-2 font-bold">{{ $ot['name'] }}</td>
                                    <td class="px-3 py-2 font-mono">{{ $ot['year'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $ot['location'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-zinc-500">{{ $ot['notes'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

