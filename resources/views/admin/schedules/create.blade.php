<x-layouts::app :title="__('Tambah Jadwal Sesi Lomba')">
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                <a href="{{ route('admin.schedules.index') }}" class="hover:text-amber-700">Jadwal & Penugasan Juri</a>
                <span>&rarr;</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">Jadwalkan Sesi Baru</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                Jadwalkan Sesi Lomba Baru
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                Tentukan waktu sesi pelaksanaan, arena pos perlombaan, dan jendela presensi barcode kehadiran regu peserta.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-300 bg-red-50 p-4 text-xs sm:text-sm text-red-900 dark:border-red-500/30 dark:bg-red-950/40 dark:text-red-200">
                <p class="font-bold mb-1">Periksa isian formulir:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Container --}}
        <div class="rounded-3xl border border-zinc-200/90 bg-white p-6 sm:p-8 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <form method="POST" action="{{ route('admin.schedules.store') }}" class="space-y-6">
                @csrf

                {{-- Pilih Cabang Lomba --}}
                <div>
                    <flux:select name="competition_id" label="Cabang Mata Lomba" required>
                        <flux:select.option value="">Pilih Mata Lomba...</flux:select.option>
                        @foreach($competitions as $comp)
                            <flux:select.option value="{{ $comp->id }}" :selected="old('competition_id') == $comp->id">
                                [{{ $comp->code }}] {{ $comp->title }} &mdash; ({{ $comp->categoryLabel() }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Nama Sesi --}}
                    <div>
                        <flux:input
                            name="session_name"
                            label="Nama Sesi Pelaksanaan"
                            :value="old('session_name')"
                            required
                            placeholder="Contoh: Sesi 1 - Babak Penyisihan Regu Putra"
                        />
                    </div>

                    {{-- Lokasi Arena Pos --}}
                    <div>
                        <flux:input
                            name="arena_location"
                            label="Lokasi Arena / Pos Kegiatan"
                            :value="old('arena_location')"
                            required
                            placeholder="Contoh: Pos Lapangan Utama / Tenda Utama"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Waktu Mulai --}}
                    <div>
                        <flux:input
                            name="start_time"
                            label="Waktu Mulai Sesi (WIB)"
                            type="datetime-local"
                            :value="old('start_time')"
                            required
                        />
                    </div>

                    {{-- Waktu Selesai --}}
                    <div>
                        <flux:input
                            name="end_time"
                            label="Waktu Selesai Sesi (WIB)"
                            type="datetime-local"
                            :value="old('end_time')"
                            required
                        />
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-200/80 bg-blue-50/70 p-4 dark:border-blue-900/50 dark:bg-blue-950/30">
                    <div class="flex items-start gap-3">
                        <flux:icon name="clock" class="size-4 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
                        <div class="text-xs text-blue-900 dark:text-blue-200 space-y-0.5">
                            <p class="font-bold text-blue-950 dark:text-blue-100">Jadwal Sesi Presensi Terotomatisasi</p>
                            <p>Presensi pos penilaian lomba dibuka otomatis <strong>paling cepat 30 menit sebelum waktu lomba dimulai</strong> dan ditutup saat waktu sesi lomba berakhir.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Status Sesi --}}
                    <div>
                        <flux:select name="status" label="Status Sesi" required>
                            <flux:select.option value="scheduled" :selected="old('status', 'scheduled') === 'scheduled'">Dijadwalkan</flux:select.option>
                            <flux:select.option value="in_progress" :selected="old('status') === 'in_progress'">Sedang Berlangsung</flux:select.option>
                            <flux:select.option value="completed" :selected="old('status') === 'completed'">Selesai</flux:select.option>
                            <flux:select.option value="rescheduled" :selected="old('status') === 'rescheduled'">Ditunda</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Format Peserta Sesi --}}
                    <div>
                        <flux:select name="participant_type" label="Tipe Format Peserta">
                            <flux:select.option value="" :selected="!old('participant_type')">Ikuti Standar Cabang Lomba</flux:select.option>
                            <flux:select.option value="patrol" :selected="old('participant_type') === 'patrol'">Regu Utuh (PINRU/WAPINRU Presensi)</flux:select.option>
                            <flux:select.option value="group" :selected="old('participant_type') === 'group'">Kelompok (Beberapa Anggota dalam Regu)</flux:select.option>
                            <flux:select.option value="individual" :selected="old('participant_type') === 'individual'">Individu (1 Perwakilan Regu)</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                {{-- Status Publikasi --}}
                <div class="pt-1">
                    <flux:checkbox
                        name="is_published"
                        label="Tampilkan jadwal ini di laman jadwal publik portal LT III"
                        :checked="old('is_published', true)"
                    />
                </div>

                {{-- Actions --}}
                <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <a href="{{ route('admin.schedules.index') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200">
                        &larr; Batal & Kembali
                    </a>

                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="check"
                    >
                        Simpan & Lanjutkan Penugasan Juri &rarr;
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
