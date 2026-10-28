        {{-- Profil Card Peserta --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if($participant->photo_path)
                        <button type="button"
                                @click="openDocModal('{{ asset('storage/' . $participant->photo_path) }}', '{{ addslashes($participant->full_name) }} (Pas Foto)', '{{ pathinfo($participant->photo_path, PATHINFO_EXTENSION) }}')"
                                class="group relative size-20 rounded-2xl overflow-hidden border-2 border-amber-500 shadow-sm shrink-0 cursor-zoom-in focus:outline-none focus:ring-2 focus:ring-amber-400"
                                title="Klik untuk memperbesar foto">
                            <img src="{{ asset('storage/' . $participant->photo_path) }}" alt="{{ $participant->full_name }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-200">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:icon name="magnifying-glass-plus" class="size-6 text-white" />
                            </span>
                        </button>
                    @else
                        <div class="size-20 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-zinc-400 font-bold text-2xl shrink-0">
                            {{ strtoupper(substr($participant->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                Regu {{ $participant->patrol?->patrol_name }} ({{ $participant->patrol?->gender_category?->label() }})
                            </span>
                            <span class="text-xs text-zinc-400">
                                Gudep {{ $participant->patrol?->group_number }}
                            </span>
                        </div>
                        <h2 class="mt-1 text-lg sm:text-xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $participant->full_name }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            Pangkalan: <strong class="text-zinc-700 dark:text-zinc-300">{{ $scoutGroup?->school_name ?? '-' }}</strong> &bull; Kwarran: {{ $scoutGroup?->kwarran_name ?? '-' }}
                        </p>
                    </div>
                </div>

                @php
                    $docsCheck = [
                        $participant->photo_path,
                        $participant->doc_kta_path,
                        $participant->doc_bpjs_path,
                        $participant->doc_health_certificate_path,
                        $participant->doc_parent_consent_path,
                        $participant->doc_birth_certificate_path,
                        $participant->doc_family_card_path,
                        $participant->doc_sku_path,
                        $participant->doc_skk_path,
                    ];
                    $uploadedCount = collect($docsCheck)->filter(fn($d) => !empty($d))->count();
                @endphp
                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-right text-xs dark:border-zinc-800 dark:bg-zinc-800/50">
                    <span class="text-zinc-400 block text-[11px]">Kelengkapan Berkas:</span>
                    <span class="text-sm font-bold {{ $uploadedCount === 9 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $uploadedCount }} dari 9 Berkas Terunggah
                    </span>
                </div>
            </div>
        </div>

        {{-- Section 1: Seluruh File Yang Diunggah (Dapat Ditampilkan dalam Modal) --}}
        @php
            $docsList = [
                ['label' => 'Pas Foto 3x4', 'path' => $participant->photo_path, 'desc' => 'Foto resmi berseragam pramuka lengkap'],
                ['label' => 'Scan KTA Pramuka', 'path' => $participant->doc_kta_path, 'desc' => 'Kartu Tanda Anggota aktif'],
                ['label' => 'Scan BPJS / Asuransi', 'path' => $participant->doc_bpjs_path, 'desc' => 'Kartu jaminan kesehatan aktif'],
                ['label' => 'Surat Keterangan Sehat', 'path' => $participant->doc_health_certificate_path, 'desc' => 'Dari dokter / faskes pemerintah'],
                ['label' => 'Surat Izin Orang Tua', 'path' => $participant->doc_parent_consent_path, 'desc' => 'Persetujuan orang tua / wali'],
                ['label' => 'Scan Akta Kelahiran', 'path' => $participant->doc_birth_certificate_path, 'desc' => 'Akta otentik catatan sipil'],
                ['label' => 'Scan Kartu Keluarga (KK)', 'path' => $participant->doc_family_card_path, 'desc' => 'Kartu keluarga terbaru'],
                ['label' => 'Scan SKU Penggalang', 'path' => $participant->doc_sku_path, 'desc' => 'Syarat Kecakapan Umum'],
                ['label' => 'Scan SKK Penggalang', 'path' => $participant->doc_skk_path, 'desc' => 'Syarat Kecakapan Khusus'],
            ];
        @endphp

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Berkas &amp; Dokumen Persyaratan Yang Diunggah</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Klik tombol pratinjau untuk melihat file langsung di dalam modal (mendukung zoom foto &amp; preview PDF)</p>
                </div>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $uploadedCount === 9 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' }}">
                    {{ $uploadedCount }}/9 Berkas
                </span>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                @foreach($docsList as $doc)
                    @php
                        $ext = strtolower(pathinfo($doc['path'] ?? '', PATHINFO_EXTENSION));
                        $hasFile = !empty($doc['path']);
                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    @endphp
                    <div class="flex flex-col justify-between rounded-xl border {{ $hasFile ? 'border-emerald-200 bg-emerald-50/20 dark:border-emerald-800/40 dark:bg-emerald-950/10' : 'border-zinc-200 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-800/30' }} p-4">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate" title="{{ $doc['label'] }}">{{ $doc['label'] }}</span>
                                @if($hasFile)
                                    <span class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 uppercase">
                                        {{ $ext ?: 'Ada' }}
                                    </span>
                                @else
                                    <span class="rounded-md bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        Kosong
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                {{ $doc['desc'] }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between gap-1.5">
                            @if($hasFile)
                                <flux:button size="xs" variant="primary" icon="eye" class="flex-1 justify-center"
                                             @click="openDocModal('{{ asset('storage/' . $doc['path']) }}', '{{ addslashes($participant->full_name) }} — {{ addslashes($doc['label']) }}', '{{ $ext }}')">
                                    Pratinjau File
                                </flux:button>
                                <flux:button href="{{ asset('storage/' . $doc['path']) }}" target="_blank" size="xs" variant="subtle" icon="arrow-top-right-on-square" title="Buka di tab baru">
                                </flux:button>
                            @else
                                <span class="text-[11px] text-zinc-400 italic">Belum diunggah</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 2: Biodata Pribadi & Kontak --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Biodata Pribadi &amp; Kontak</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <div>
                    <span class="text-zinc-400 block text-[11px]">NTA Pramuka:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $participant->scout_id ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">Jenis Kelamin:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">Tempat, Tanggal Lahir:</span>
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
                    <span class="text-zinc-400 block text-[11px]">No. HP / WhatsApp:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 font-mono">{{ $participant->phone ?: '-' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-zinc-400 block text-[11px]">Alamat Rumah Lengkap:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->home_address ?: '-' }}, {{ $participant->city ?: '-' }} {{ $participant->postal_code ?: '' }}</span>
                </div>
            </div>
        </div>

        {{-- Section 3: Data Orang Tua / Wali --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Data Orang Tua / Wali</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-zinc-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <div>
                    <span class="text-zinc-400 block text-[11px]">Nama Orang Tua / Wali:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->parent_name ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">Pekerjaan:</span>
                    <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $participant->parent_job ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-zinc-400 block text-[11px]">No. HP / WA Orang Tua:</span>
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

        {{-- Section 4: Riwayat Pendidikan Formal & Kepramukaan --}}
        @php
            $formalEdu = $participant->formal_education ?: \App\Models\Participant::defaultFormalEducation();
            $scoutSiaga = $participant->scout_siaga ?: \App\Models\Participant::defaultScoutSiaga();
            $scoutPenggalang = $participant->scout_penggalang ?: \App\Models\Participant::defaultScoutPenggalang();
        @endphp

        <div class="grid grid-cols-1 gap-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Pendidikan Formal</h3>
                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                            <tr>
                                <th class="px-3 py-2 font-semibold">Tingkat</th>
                                <th class="px-3 py-2 font-semibold">Nama Sekolah</th>
                                <th class="px-3 py-2 font-semibold">Kota</th>
                                <th class="px-3 py-2 font-semibold">Tahun</th>
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

        </div>

        {{-- Riwayat Kepramukaan Lengkap: Giat & TKK Siaga / Penggalang --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-3">Riwayat Kepramukaan, Giat &amp; Tanda Kecakapan Khusus</h3>
            @include('admin.participants.partials.scout-history', ['scoutSiaga' => $scoutSiaga, 'scoutPenggalang' => $scoutPenggalang])
        </div>

