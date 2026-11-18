<div class="mx-auto max-w-4xl space-y-6">
    {{-- Header Dokumen --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">
                    Evaluasi Resmi Penyelenggaraan
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $event?->name ?: 'LT-III Kwarcab Padangsidimpuan' }}
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-zinc-900 sm:text-3xl dark:text-zinc-100 flex items-center gap-2.5">
                <flux:icon name="clipboard-document-check" class="size-8 text-amber-500" />
                <span>Kuisioner Evaluasi Pengalaman Aplikasi</span>
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Kuisioner ini ditujukan bagi <strong>Panitia Pelaksana</strong> dan <strong>Dewan Juri</strong> untuk mengevaluasi efektivitas aplikasi sebagai sarana penunjang penilaian lomba, presensi, dan operasional lapangan.
            </p>
        </div>
    </div>

    {{-- Banner Notifikasi Status Pengisian --}}
    @if ($hasSubmitted)
        <div class="rounded-2xl border border-emerald-300 bg-emerald-50/70 p-4 text-xs text-emerald-950 shadow-xs dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            <div class="flex items-start gap-3">
                <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                    <flux:icon name="check-badge" class="size-5" />
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-emerald-900 dark:text-emerald-100">
                        Tanggapan Kuisioner Anda Telah Tercatat
                    </h3>
                    <p class="mt-0.5 text-emerald-800 dark:text-emerald-300">
                        Terakhir dikirim pada: <strong>{{ $submittedAt }}</strong>. Anda tetap dapat memperbarui nilai atau saran di bawah ini kapan saja, lalu klik tombol <em>"Simpan Pembaruan Kuisioner"</em>.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session('success_message'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-100/60 p-4 text-xs font-semibold text-emerald-900 dark:border-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-200 flex items-center gap-2">
            <flux:icon name="check-circle" class="size-5 text-emerald-600" />
            <span>{{ session('success_message') }}</span>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">

        {{-- Card 1: Profil Responden --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
            <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex size-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <flux:icon name="identification" class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Profil &amp; Penugasan Responden</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Data akun Anda otomatis tersinkronisasi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 text-xs">
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/30">
                    <span class="text-zinc-400 font-medium">Nama Lengkap:</span>
                    <p class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mt-0.5">{{ auth()->user()->name }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/30">
                    <span class="text-zinc-400 font-medium">Peran Tugas:</span>
                    <p class="font-bold text-amber-700 dark:text-amber-400 text-sm mt-0.5">
                        {{ auth()->user()->isCommitteeAndJudge() ? 'Panitia & Dewan Juri' : (auth()->user()->isJudge() ? 'Dewan Juri LT-III' : 'Panitia Pelaksana') }}
                    </p>
                </div>
                <div class="rounded-xl border border-zinc-200/70 bg-zinc-50/50 p-3 dark:border-zinc-800 dark:bg-zinc-800/30 sm:col-span-2 lg:col-span-1">
                    <span class="text-zinc-400 font-medium">Nomor Tanda Anggota (NTA):</span>
                    <p class="font-mono font-bold text-zinc-800 dark:text-zinc-200 text-sm mt-0.5">
                        {{ auth()->user()->scout_id ?: 'NTA: -' }}
                    </p>
                </div>
            </div>

            <div>
                <label for="duty_assignment" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    Bidang Penugasan / Pos Kegiatan / Cabang Lomba:
                </label>
                <input type="text" id="duty_assignment" wire:model="duty_assignment"
                    placeholder="Contoh: Pos Presensi Utama / Juri Lomba Pionering / Seksi Acara"
                    class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2 text-xs text-zinc-900 shadow-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('duty_assignment') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- Card 2: Bagian I - Sistem Rating Bintang (Skala 1 - 5) --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 space-y-6">
            <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex size-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <flux:icon name="star" class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Bagian I: Penilaian Rating Bintang (Skala 1 s.d. 5)</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Klik pada bintang untuk memberikan penilaian performa aplikasi</p>
                </div>
            </div>

            @php
                $ratingItems = [
                    'rating_overall' => [
                        'title' => '1. Kepuasan Menyeluruh terhadap Aplikasi',
                        'desc' => 'Secara umum, seberapa puas Anda menggunakan aplikasi ini sebagai sarana penunjang LT-III?',
                    ],
                    'rating_ui_ux' => [
                        'title' => '2. Kemudahan Tampilan & Navigasi (UI/UX)',
                        'desc' => 'Keterbacaan teks, penataan menu, dan kemudahan pengoperasian di perangkat HP/layar Anda.',
                    ],
                    'rating_features' => [
                        'title' => '3. Keandalan Fitur Utama (Cockpit Penjurian / Presensi QR)',
                        'desc' => 'Kejelasan rubrik skor otomatis, kemudahan input nilai, dan kecepatan pemindaian presensi di lapangan.',
                    ],
                    'rating_speed' => [
                        'title' => '4. Kecepatan Respon & Kestabilan Sistem di Lapangan',
                        'desc' => 'Kelancaran akses dan minimnya kendala sistem saat digunakan di area perkemahan.',
                    ],
                    'rating_transparency' => [
                        'title' => '5. Objektivitas & Transparansi Rekapitulasi Data',
                        'desc' => 'Kemanfaatan sistem dalam menjaga keadilan penilaian (blind scoring NPP) dan akurasi klasemen.',
                    ],
                ];
            @endphp

            <div class="space-y-5">
                @foreach ($ratingItems as $field => $info)
                    <div class="rounded-xl border border-zinc-200/80 bg-zinc-50/40 p-4 dark:border-zinc-800 dark:bg-zinc-800/30">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="space-y-0.5">
                                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ $info['title'] }}</h3>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $info['desc'] }}</p>
                            </div>

                            <div class="flex flex-col items-start sm:items-end gap-1.5 shrink-0">
                                <div class="flex items-center gap-1">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <button type="button" wire:click="setRating('{{ $field }}', {{ $star }})"
                                            class="p-1 rounded-lg transition-transform hover:scale-110 focus:outline-hidden"
                                            title="{{ $star }} Bintang - {{ $ratingDescriptions[$star] }}">
                                            <flux:icon name="star"
                                                class="size-6 {{ ${$field} >= $star ? 'text-amber-500 fill-amber-500' : 'text-zinc-300 dark:text-zinc-600' }}" />
                                        </button>
                                    @endfor
                                </div>
                                <span class="text-[11px] font-bold {{ ${$field} >= 4 ? 'text-emerald-600 dark:text-emerald-400' : (${$field} === 3 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                    ★ {{ ${$field} }} / 5 &mdash; {{ $ratingDescriptions[${$field}] ?? '' }}
                                </span>
                            </div>
                        </div>
                        @error($field) <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Card 3: Bagian II - Pertanyaan Pilihan Ganda --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 space-y-6">
            <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex size-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <flux:icon name="list-bullet" class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Bagian II: Pertanyaan Pilihan Ganda</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Pilih opsi yang paling sesuai dengan pengalaman nyata Anda di lapangan</p>
                </div>
            </div>

            <div class="space-y-6">
                {{-- 1. Perangkat yang digunakan --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        1. Perangkat utama yang Anda gunakan saat bertugas di perkemahan/pos:
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach ($devices as $key => $label)
                            <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all {{ $device_used === $key ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}">
                                <input type="radio" wire:model.live="device_used" value="{{ $key }}" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('device_used') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 2. Efisiensi dibanding manual --}}
                <div class="space-y-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                    <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        2. Perbandingan efisiensi waktu &amp; tenaga dibanding sistem penilaian kertas manual:
                    </label>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach ($efficiencyLevels as $key => $label)
                            <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all {{ $efficiency_level === $key ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}">
                                <input type="radio" wire:model.live="efficiency_level" value="{{ $key }}" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('efficiency_level') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 3. Kendala utama di lapangan --}}
                <div class="space-y-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                    <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        3. Kendala operasional terbesar yang Anda temui selama bertugas:
                    </label>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach ($obstacles as $key => $label)
                            <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all {{ $main_obstacle === $key ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}">
                                <input type="radio" wire:model.live="main_obstacle" value="{{ $key }}" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('main_obstacle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 4. Fitur paling bermanfaat --}}
                <div class="space-y-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                    <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        4. Fitur dalam aplikasi yang dirasa paling bermanfaat dan membantu tugas:
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($mostUsefulFeatures as $key => $label)
                            <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all {{ $most_useful_feature === $key ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}">
                                <input type="radio" wire:model.live="most_useful_feature" value="{{ $key }}" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('most_useful_feature') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 5. Kejelasan rubrik penilaian (khusus juri/panitia) --}}
                <div class="space-y-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                    <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        5. Kejelasan panduan kriteria rubrik &amp; batasan skor saat input nilai (Dewan Juri):
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        @foreach ($rubricClarityOptions as $key => $label)
                            <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all {{ $rubric_clarity === $key ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}">
                                <input type="radio" wire:model.live="rubric_clarity" value="{{ $key }}" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('rubric_clarity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- 6. Kesiapan keberlanjutan aplikasi --}}
                <div class="space-y-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                    <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        6. Rekomendasi keberlanjutan aplikasi untuk kegiatan Lomba Tingkat berikutnya (LT-IV / LT selanjutnya):
                    </label>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach ($readinessOptions as $key => $label)
                            <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-all {{ $future_readiness === $key ? 'border-amber-500 bg-amber-50/60 ring-1 ring-amber-500 dark:border-amber-500/60 dark:bg-amber-950/30' : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700' }}">
                                <input type="radio" wire:model.live="future_readiness" value="{{ $key }}" class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('future_readiness') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Card 4: Bagian III - Pertanyaan Terbuka & Rekomendasi Fitur --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
            <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800">
                <div class="flex size-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <flux:icon name="chat-bubble-bottom-center-text" class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Bagian III: Rekomendasi &amp; Masukan Bebas</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Tuliskan ide dan masukan konkret Anda untuk pengembangan sistem</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="recommended_features" class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        1. Fitur rekomendasi apa yang paling penting / mendesak untuk dikembangkan ke depan?
                    </label>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-1">
                        Contoh: Mode offline penuh saat sinyal internet drop, notifikasi pengingat via WhatsApp saat sesi lomba dimulai, input multi-juri paralel dalam satu pos, unggah foto dokumentasi karya regu, dll.
                    </p>
                    <textarea id="recommended_features" wire:model="recommended_features" rows="3"
                        placeholder="Tuliskan rekomendasi fitur yang Anda harapkan..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 shadow-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    @error('recommended_features') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="experience_feedback" class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        2. Ceritakan kesan atau pengalaman Anda selama menggunakan aplikasi ini sebagai penunjang penilaian &amp; operasional:
                    </label>
                    <textarea id="experience_feedback" wire:model="experience_feedback" rows="3"
                        placeholder="Tuliskan pengalaman nyata Anda saat bertugas di lapangan..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 shadow-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    @error('experience_feedback') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="general_suggestions" class="block text-xs font-bold text-zinc-900 dark:text-zinc-100">
                        3. Kritik &amp; Saran Tambahan (Opsional):
                    </label>
                    <textarea id="general_suggestions" wire:model="general_suggestions" rows="2"
                        placeholder="Kritik atau masukan bebas lainnya untuk Panitia & Tim Pengembang..."
                        class="w-full rounded-xl border border-zinc-300 bg-white p-3 text-xs text-zinc-900 shadow-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                    @error('general_suggestions') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Action Bar --}}
        <div class="flex items-center justify-between rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                Seluruh tanggapan Anda bersifat rahasia dan dipakai untuk perbaikan sistem.
            </span>
            <flux:button type="submit" variant="primary" icon="paper-airplane" class="bg-amber-600 hover:bg-amber-700 text-white font-bold">
                {{ $hasSubmitted ? 'Simpan Pembaruan Kuisioner' : 'Kirim Tanggapan Kuisioner' }}
            </flux:button>
        </div>
    </form>
</div>
