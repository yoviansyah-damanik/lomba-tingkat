<x-layouts::app :title="__('Atur Jadwal & Penugasan Juri')">
    <div class="space-y-6">
        {{-- Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                <a href="{{ route('admin.schedules.index') }}" class="hover:text-amber-700">Jadwal & Penugasan Juri</a>
                <span>&rarr;</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ $schedule->session_name }}</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Atur Jadwal, Juri & Pos Lomba
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-zinc-600 dark:text-zinc-400">
                        Kelola parameter waktu sesi, aktivasi pos presensi QR, dan tentukan aparatur dewan juri penilai.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 shadow-xs hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        <flux:icon name="arrow-left" class="size-3.5" />
                        <span>Daftar Jadwal</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- KOLOM KIRI: EDIT PARAMETER JADWAL (lg:col-span-5) --}}
            <div class="lg:col-span-5 rounded-3xl border border-zinc-200/90 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        Parameter Sesi Jadwal
                    </h2>
                    <p class="text-xs text-zinc-500">Ubah cabang lomba, interval waktu, dan arena pos.</p>
                </div>

                <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <flux:select name="competition_id" label="Cabang Mata Lomba" required>
                            @foreach($competitions as $comp)
                                <flux:select.option value="{{ $comp->id }}" :selected="old('competition_id', $schedule->competition_id) == $comp->id">
                                    [{{ $comp->code }}] {{ $comp->title }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:input
                            name="session_name"
                            label="Nama Sesi Lomba"
                            :value="old('session_name', $schedule->session_name)"
                            required
                        />
                    </div>

                    <div>
                        <flux:input
                            name="arena_location"
                            label="Lokasi Arena / Pos Kegiatan"
                            :value="old('arena_location', $schedule->arena_location)"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <flux:input
                                name="start_time"
                                label="Waktu Mulai"
                                type="datetime-local"
                                :value="old('start_time', $schedule->start_time->format('Y-m-d\TH:i'))"
                                required
                            />
                        </div>
                        <div>
                            <flux:input
                                name="end_time"
                                label="Waktu Selesai"
                                type="datetime-local"
                                :value="old('end_time', $schedule->end_time->format('Y-m-d\TH:i'))"
                                required
                            />
                        </div>
                    </div>

                    <div class="rounded-xl border border-blue-200/80 bg-blue-50/70 p-3.5 dark:border-blue-900/50 dark:bg-blue-950/30">
                        <div class="flex items-start gap-2.5">
                            <flux:icon name="clock" class="size-4 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
                            <div class="text-xs text-blue-900 dark:text-blue-200 space-y-0.5">
                                <p class="font-bold text-blue-950 dark:text-blue-100">Jadwal Sesi Presensi Terotomatisasi</p>
                                <p>Presensi dibuka otomatis <strong>paling cepat 30 menit sebelum jadwal dimulai</strong> ({{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->subMinutes(30)->format('H:i') : '-' }} WIB) dan ditutup saat lomba berakhir ({{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '-' }} WIB).</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <flux:select name="status" label="Status Sesi" required>
                                <flux:select.option value="scheduled" :selected="old('status', $schedule->status) === 'scheduled'">Dijadwalkan</flux:select.option>
                                <flux:select.option value="in_progress" :selected="old('status', $schedule->status) === 'in_progress'">Berlangsung</flux:select.option>
                                <flux:select.option value="completed" :selected="old('status', $schedule->status) === 'completed'">Selesai</flux:select.option>
                                <flux:select.option value="rescheduled" :selected="old('status', $schedule->status) === 'rescheduled'">Ditunda</flux:select.option>
                                <flux:select.option value="cancelled" :selected="old('status', $schedule->status) === 'cancelled'">Dibatalkan</flux:select.option>
                            </flux:select>
                        </div>
                        <div>
                            <flux:select name="participant_type" label="Tipe Format">
                                <flux:select.option value="" :selected="!old('participant_type', $schedule->participant_type)">Standar Lomba</flux:select.option>
                                <flux:select.option value="patrol" :selected="old('participant_type', $schedule->participant_type) === 'patrol'">Regu Utuh</flux:select.option>
                                <flux:select.option value="group" :selected="old('participant_type', $schedule->participant_type) === 'group'">Kelompok</flux:select.option>
                                <flux:select.option value="individual" :selected="old('participant_type', $schedule->participant_type) === 'individual'">Individu</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    <div class="pt-1">
                        <flux:checkbox
                            name="is_published"
                            label="Tampilkan di jadwal publik portal"
                            :checked="old('is_published', $schedule->is_published)"
                        />
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition hover:bg-zinc-800 dark:bg-amber-600 dark:hover:bg-amber-500 cursor-pointer"
                        >
                            <flux:icon name="check" class="size-4" />
                            <span>Perbarui Jadwal Sesi</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- KOLOM KANAN: KONTROL PRESENSI POS & PENUGASAN JURI (lg:col-span-7) --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- KONTROL SESI PRESENSI POS (Phase 5 -> Phase 6 preparation) --}}
                @php
                    $activeSession = $schedule->activeAttendanceSession();
                @endphp
                <div class="rounded-3xl border border-zinc-200/90 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:icon name="qr-code" class="size-4 text-amber-600 dark:text-amber-400" />
                                <span>Aktivasi Sesi Presensi Pos</span>
                            </h3>
                            <p class="text-xs text-zinc-500">
                                Buka sesi presensi agar aparatur scanner dapat memvalidasi kehadiran QR regu peserta di arena pos.
                            </p>
                        </div>

                        @php
                            $isWithinWindow = $schedule->isAttendanceOpen();
                            $isNotOpenYet = $schedule->isAttendanceNotOpenYet();
                            $isClosed = $schedule->isAttendanceClosed();
                            $isReadyToScan = $isWithinWindow && $activeSession;
                        @endphp
                        <div>
                            @if($isReadyToScan)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 animate-pulse">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    <span>AKTIF SCAN (OTOMATIS TERBUKA)</span>
                                </span>
                            @elseif($isWithinWindow && ! $activeSession)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                    <flux:icon name="lock-closed" class="size-3.5 text-rose-600 dark:text-rose-400" />
                                    <span>DITUTUP MANUAL SAAT LOMBA</span>
                                </span>
                            @elseif($isNotOpenYet)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                    <flux:icon name="clock" class="size-3.5 text-amber-600 dark:text-amber-400" />
                                    <span>OTOMATIS BUKA H-30M ({{ $schedule->attendance_open_time?->format('H:i') }} WIB)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon name="check-circle" class="size-3.5 text-zinc-400" />
                                    <span>PRESENSI DITUTUP (SELESAI)</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl">
                        <div class="space-y-1">
                            <div class="text-zinc-700 dark:text-zinc-300 font-semibold">
                                Lokasi: <span class="font-bold text-amber-700 dark:text-amber-400">{{ $schedule->arena_location }}</span>
                            </div>
                            <div class="text-[11px] text-zinc-500">
                                Waktu Server Resmi: <span class="font-mono font-bold">{{ now()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y • HH:mm') }} WIB</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.schedules.sessions.toggle', $schedule) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs transition cursor-pointer {{ ($isWithinWindow && $activeSession) ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                            >
                                <flux:icon name="{{ ($isWithinWindow && $activeSession) ? 'stop' : 'play' }}" class="size-3.5" />
                                <span>{{ ($isWithinWindow && $activeSession) ? 'Tutup Sesi Presensi Pos (Saat Lomba)' : ($isWithinWindow ? 'Buka Kembali Sesi Presensi' : ($isNotOpenYet ? ($activeSession ? 'Kunci / Nonaktifkan Auto-Buka' : 'Aktifkan Auto-Buka') : 'Buka Sesi Presensi Susulan')) }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- DAFTAR DEWAN JURI BERTUGAS --}}
                <div class="rounded-3xl border border-zinc-200/90 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                <flux:icon name="user-group" class="size-4 text-amber-600 dark:text-amber-400" />
                                <span>Dewan Juri Yang Ditugaskan</span>
                            </h3>
                            <p class="text-xs text-zinc-500">
                                Aparatur juri yang berwenang memberikan nilai dan menandatangani berita acara lomba.
                            </p>
                        </div>

                        <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $schedule->judgeAssignments->count() }} Juri
                        </span>
                    </div>

                    @if($schedule->judgeAssignments->isEmpty())
                        <div class="rounded-2xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-800">
                            <flux:icon name="user-minus" class="mx-auto size-8 text-zinc-400" />
                            <p class="mt-2 text-xs font-semibold text-zinc-600 dark:text-zinc-400">Belum Ada Dewan Juri Ditugaskan</p>
                            <p class="text-[11px] text-zinc-400">Pilih aparatur juri melalui formulir penugasan di bawah.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($schedule->judgeAssignments as $assignment)
                                <div class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-200/80 bg-zinc-50/70 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/50">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-600 font-bold text-white text-xs shadow-xs">
                                            {{ $assignment->judge?->initials() ?? 'J' }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                                    {{ $assignment->judge?->name }}
                                                </h4>
                                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                    {{ $assignment->roleLabel() }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-zinc-500">
                                                {{ $assignment->judge?->email }} &bull; {{ $assignment->judge?->institution ?: 'Kwarcab Padangsidimpuan' }}
                                            </p>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.schedules.judges.remove', [$schedule, $assignment]) }}" onsubmit="return confirm('Lepas penugasan juri {{ $assignment->judge?->name }} dari sesi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex size-7 items-center justify-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-950/40 cursor-pointer" title="Lepas Penugasan">
                                            <flux:icon name="x-mark" class="size-3.5" />
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Form Penugasan Juri Baru --}}
                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <form method="POST" action="{{ route('admin.schedules.judges.assign', $schedule) }}" class="space-y-3">
                            @csrf
                            <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                Tugaskan Aparatur Juri Baru:
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-6">
                                    <flux:select name="user_id" required>
                                        <flux:select.option value="">Pilih Aparatur Juri...</flux:select.option>
                                        @foreach($availableJudges as $judge)
                                            <flux:select.option value="{{ $judge->id }}">
                                                {{ $judge->name }} ({{ $judge->institution ?: 'Kwarcab' }})
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                <div class="sm:col-span-4">
                                    <flux:select name="role" required>
                                        <flux:select.option value="scoring_judge">Juri Penilai</flux:select.option>
                                        <flux:select.option value="lead_judge">Ketua Juri</flux:select.option>
                                        <flux:select.option value="companion_judge">Juri Pendamping</flux:select.option>
                                    </flux:select>
                                </div>
                                <div class="sm:col-span-2">
                                    <button
                                        type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-600 px-3 py-2 text-xs font-bold text-white shadow-xs hover:bg-amber-700 transition cursor-pointer"
                                    >
                                        <flux:icon name="user-plus" class="size-3.5" />
                                        <span>Tugaskan</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-layouts::app>
