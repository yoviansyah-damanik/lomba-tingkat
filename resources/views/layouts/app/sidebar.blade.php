<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Utama')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('agenda.index')" :current="request()->routeIs('agenda.*')" wire:navigate>
                        {{ __('Jadwal Kegiatan') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @auth
                    {{-- Navigasi Khusus Administrator --}}
                    @if(auth()->user()->isAdmin())
                        <flux:sidebar.group :heading="__('Administrator')" class="grid">
                            <flux:sidebar.item icon="shield-check" :href="route('admin.scout-groups.index')" :current="request()->routeIs('admin.scout-groups.*')" wire:navigate>
                                {{ __('Verifikasi Pendaftaran') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="academic-cap" :href="route('admin.participants.index')" :current="request()->routeIs('admin.participants.*')" wire:navigate>
                                {{ __('Direktori Peserta') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="user-circle" :href="route('admin.chaperones.index')" :current="request()->routeIs('admin.chaperones.*')" wire:navigate>
                                {{ __('Pembina Pendamping') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="trophy" :href="route('admin.competitions.index')" :current="request()->routeIs('admin.competitions.*')" wire:navigate>
                                {{ __('Mata Lomba & Rubrik') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="calendar" :href="route('admin.schedules.index')" :current="request()->routeIs('admin.schedules.*')" wire:navigate>
                                {{ __('Jadwal & Penugasan Juri') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="qr-code" :href="route('attendance.index')" :current="request()->routeIs('attendance.index') || request()->routeIs('attendance.scanner')" wire:navigate>
                                {{ __('Scan Presensi Pos') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('attendance.recap')" :current="request()->routeIs('attendance.recap*')" wire:navigate>
                                {{ __('Rekapitulasi Presensi') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="pencil-square" :href="route('judge.index')" :current="request()->routeIs('judge.*')" wire:navigate>
                                {{ __('Cockpit Penjurian') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('admin.standings.index')" :current="request()->routeIs('admin.standings.*') || request()->routeIs('standings')" wire:navigate>
                                {{ __('Rekap Nilai & Klasemen') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="document-text" :href="route('admin.reports.index')" :current="request()->routeIs('admin.reports.*')" wire:navigate>
                                {{ __('Pusat Laporan & Ekspor') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="megaphone" :href="route('admin.announcements.index')" :current="request()->routeIs('admin.announcements.*')" wire:navigate>
                                {{ __('Pengumuman Kegiatan') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="user-group" :href="route('admin.officials.index')" :current="request()->routeIs('admin.officials.*')" wire:navigate>
                                {{ __('Aparatur Panitia & Juri') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Superadmin --}}
                    @if(auth()->user()->isSuperadmin())
                        <flux:sidebar.group :heading="__('Superadmin')" class="grid">
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('admin.assessments.index')" :current="request()->routeIs('admin.assessments.*')" wire:navigate>
                                {{ __('Audit Penilaian & Log') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="user-group" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                                {{ __('Pengguna & Hak Akses') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="bell" :href="route('admin.push-broadcast.index')" :current="request()->routeIs('admin.push-broadcast.*')" wire:navigate>
                                {{ __('Siaran Notifikasi (PWA)') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings.system')" :current="request()->routeIs('admin.settings.system')" wire:navigate>
                                {{ __('Konfigurasi Sistem') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Panitia (Monitor Operasional) --}}
                    @if(auth()->user()->isCommittee())
                        <flux:sidebar.group :heading="__('Operasional Lapangan')" class="grid">
                            <flux:sidebar.item icon="academic-cap" :href="route('admin.participants.index')" :current="request()->routeIs('admin.participants.*')" wire:navigate>
                                {{ __('Direktori Peserta') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="user-circle" :href="route('admin.chaperones.index')" :current="request()->routeIs('admin.chaperones.*')" wire:navigate>
                                {{ __('Pembina Pendamping') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="shield-check" :href="route('admin.scout-groups.index')" :current="request()->routeIs('admin.scout-groups.*')" wire:navigate>
                                {{ __('Pangkalan Gugusdepan') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="qr-code" :href="route('attendance.index')" :current="request()->routeIs('attendance.index') || request()->routeIs('attendance.scanner')" wire:navigate>
                                {{ __('Scanner Presensi Kehadiran') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('attendance.recap')" :current="request()->routeIs('attendance.recap*')" wire:navigate>
                                {{ __('Rekapitulasi Presensi') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('standings')" :current="request()->routeIs('standings') || request()->routeIs('admin.standings.*')" wire:navigate>
                                {{ __('Rekap Nilai & Klasemen') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Juri (Penilaian) --}}
                    @if(auth()->user()->isJudge())
                        <flux:sidebar.group :heading="__('Dewan Juri')" class="grid">
                            <flux:sidebar.item icon="pencil-square" :href="route('judge.index')" :current="request()->routeIs('judge.*')" wire:navigate>
                                {{ __('Cockpit Penjurian') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="academic-cap" :href="route('admin.participants.index')" :current="request()->routeIs('admin.participants.*')" wire:navigate>
                                {{ __('Direktori Peserta') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="qr-code" :href="route('attendance.index')" :current="request()->routeIs('attendance.index') || request()->routeIs('attendance.scanner')" wire:navigate>
                                {{ __('Scanner Presensi Lomba') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('attendance.recap')" :current="request()->routeIs('attendance.recap*')" wire:navigate>
                                {{ __('Rekapitulasi Presensi') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('standings', ['tab' => 'competitions'])" :current="request()->routeIs('standings') || request()->routeIs('admin.standings.*')" wire:navigate>
                                {{ __('Rekap Nilai & Klasemen') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Gugus Depan (Pangkalan Sekolah) --}}
                    @if(auth()->user()->isScoutGroup())
                        <flux:sidebar.group :heading="__('Kontingen Gudep')" class="grid">
                            <flux:sidebar.item icon="user-group" :href="route('scout-group.index')" :current="request()->routeIs('scout-group.index') || request()->routeIs('scout-group.patrols*') || request()->routeIs('scout-group.participants*')" wire:navigate>
                                {{ __('Regu & Peserta') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="identification" :href="route('scout-group.chaperones')" :current="request()->routeIs('scout-group.chaperones*')" wire:navigate>
                                {{ __('Bindamping') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('scout-group.review')" :current="request()->routeIs('scout-group.review')" wire:navigate>
                                {{ __('Periksa & Ajukan') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="trophy" :href="route('team-assessment.index')" :current="request()->routeIs('team-assessment.*')" wire:navigate>
                                {{ __('Penilaian Regu') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('standings')" :current="request()->routeIs('standings') || request()->routeIs('admin.standings.*')" wire:navigate>
                                {{ __('Rekap Nilai & Klasemen') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Peserta / Regu --}}
                    @if(auth()->user()->isParticipant())
                        <flux:sidebar.group :heading="__('Peserta Regu')" class="grid">
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('team-assessment.index')" :current="request()->routeIs('team-assessment.*')" wire:navigate>
                                {{ __('Penilaian Regu') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('standings')" :current="request()->routeIs('standings') || request()->routeIs('admin.standings.*')" wire:navigate>
                                {{ __('Klasemen & Nilai') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Bindamping --}}
                    @if(auth()->user()->isChaperone())
                        <flux:sidebar.group :heading="__('Bindamping')" class="grid">
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('team-assessment.index')" :current="request()->routeIs('team-assessment.*')" wire:navigate>
                                {{ __('Penilaian Regu') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('standings')" :current="request()->routeIs('standings') || request()->routeIs('admin.standings.*')" wire:navigate>
                                {{ __('Klasemen & Nilai') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif
                @endauth
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="px-2 py-2 text-[11px] text-zinc-500 dark:text-zinc-400">
                <span class="font-semibold text-amber-700 dark:text-amber-400">LT III Padangsidimpuan</span>
                <p class="text-[10px] text-zinc-400">Pramuka Penggalang</p>
            </div>

            {{-- Dark Mode Switcher di Sidebar Desktop --}}
            <div class="hidden lg:flex items-center justify-between px-2.5 py-1.5 mb-2 rounded-xl border border-zinc-200/70 bg-white/70 dark:border-zinc-700/60 dark:bg-zinc-800/60">
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Mode Tampilan</span>
                <x-theme-toggle />
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <x-theme-toggle class="mr-2" />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->username }}</flux:text>
                                    <span class="text-[10px] font-medium text-amber-600 dark:text-amber-400">{{ auth()->user()->roleLabel() }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        {{-- Global Fullscreen Download Loading Overlay with Real Progress Bar --}}
        <div id="download-overlay"
             x-data="downloadOverlay()"
             x-show="isLoading"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-black/75 backdrop-blur-md px-4"
             style="display: none;">

            <div class="flex w-full max-w-md flex-col items-center gap-5 rounded-3xl border border-zinc-700/60 bg-zinc-900/95 p-7 sm:p-8 text-center shadow-2xl">
                {{-- Radial / Circular Progress Indicator --}}
                <div class="relative size-16 sm:size-18">
                    <svg class="size-16 sm:size-18 -rotate-90 transform" viewBox="0 0 72 72">
                        <circle cx="36" cy="36" r="30" stroke="#27272a" stroke-width="5" fill="none"/>
                        <circle cx="36" cy="36" r="30" stroke="#f59e0b" stroke-width="5" fill="none"
                                stroke-dasharray="188.5"
                                :stroke-dashoffset="188.5 - (188.5 * progress / 100)"
                                stroke-linecap="round"
                                class="transition-all duration-300 ease-out"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <template x-if="progress < 100">
                            <span class="font-mono text-xs sm:text-sm font-bold text-amber-400" x-text="progress + '%'"></span>
                        </template>
                        <template x-if="progress >= 100">
                            <flux:icon name="check" class="size-6 text-emerald-400" />
                        </template>
                    </div>
                </div>

                {{-- Text Title --}}
                <div class="w-full">
                    <h3 class="text-base font-bold text-zinc-100" x-text="loadingLabel">Memproses Unduhan...</h3>
                    <p class="mt-1 text-xs text-zinc-400">Mohon tunggu, penyiapan dokumen sedang diproses.</p>
                </div>

                {{-- Horizontal Progress Bar & Step Tracker --}}
                <div class="w-full space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-zinc-300 font-medium truncate text-left max-w-[260px]" x-text="stepText"></span>
                        <span class="font-mono font-bold text-amber-400 shrink-0 ml-2" x-text="progress + '%'"></span>
                    </div>

                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-800 border border-zinc-700/50 p-0.5">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-300 transition-all duration-300 ease-out shadow-xs"
                             :style="`width: ${progress}%`"></div>
                    </div>
                </div>

                {{-- Security Warning / Note --}}
                <div class="flex items-center gap-2 text-[11px] text-zinc-400">
                    <flux:icon name="lock-closed" class="size-3.5 text-zinc-500" />
                    <span>Jangan tutup peramban selama proses berlangsung</span>
                </div>
            </div>
        </div>


        <flux:footer class="!py-3.5 border-t border-zinc-200/80 bg-white/60 dark:border-zinc-800 dark:bg-zinc-900/60 backdrop-blur-sm">
            <x-official-footer :compact="true" />
        </flux:footer>

        @persist('toast')
            <flux:toast.group position="top end">
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        {{-- Global Download Loading Overlay Logic with Real Progress Bar & SSE Streaming --}}
        <script>
            function downloadOverlay() {
                return {
                    isLoading: false,
                    loadingLabel: 'Memproses...',
                    stepText: 'Menghubungi server...',
                    progress: 0,
                    _timer: null,
                    _simInterval: null,

                    init() {
                        document.addEventListener('click', (e) => {
                            const el = e.target.closest('[data-loading]');
                            if (!el) return;

                            const label = el.getAttribute('data-loading') || 'Memproses...';
                            const href = el.getAttribute('href');

                            // 1. Ekspor arsip ZIP kontingen via real-time SSE Stream
                            if (href && href.includes('/download-zip') && !href.includes('/download-zip-file')) {
                                e.preventDefault();
                                const streamUrl = href.replace('/download-zip', '/download-zip-stream');
                                this.startZipStream(streamUrl, label);
                                return;
                            }

                            // 2. Berkas tunggal (PDF Formulir 01/02/03, Bukti Registrasi, Kartu Digital)
                            if (href && (href.includes('/print') || href.includes('/form-01') || href.includes('/registration-certificate') || href.includes('/id-cards'))) {
                                // Tab baru: biarkan navigasi biasa agar tampil di halaman pratinjau PDF
                                if (el.getAttribute('target') === '_blank') return;

                                e.preventDefault();
                                this.startSingleDownload(href, label, false);
                                return;
                            }

                            // 3. Fallback umum dengan simulated smooth progress
                            this.show(label);
                            this.simulate(15, 95);
                        });

                        window.addEventListener('focus', () => {
                            if (this.isLoading && this.progress >= 95) {
                                setTimeout(() => { this.hide(); }, 600);
                            }
                        });
                    },

                    show(label = 'Memproses...') {
                        this.loadingLabel = label;
                        this.stepText = 'Menghubungi server...';
                        this.progress = 5;
                        this.isLoading = true;
                        clearInterval(this._simInterval);
                        clearTimeout(this._timer);
                        this._timer = setTimeout(() => { this.hide(); }, 60000);
                    },

                    hide() {
                        this.isLoading = false;
                        clearInterval(this._simInterval);
                        clearTimeout(this._timer);
                        setTimeout(() => {
                            this.progress = 0;
                            this.stepText = '';
                        }, 300);
                    },

                    simulate(start = 10, max = 92) {
                        this.progress = start;
                        clearInterval(this._simInterval);
                        this._simInterval = setInterval(() => {
                            if (this.progress < max) {
                                const step = Math.max(1, Math.floor((max - this.progress) / 6));
                                this.progress += step;
                                if (this.progress > 30 && this.progress <= 60) {
                                    this.stepText = 'Merender lembar dokumen A4...';
                                } else if (this.progress > 60) {
                                    this.stepText = 'Mengalirkan data berkas...';
                                }
                            }
                        }, 250);
                    },

                    async startSingleDownload(href, label, isNewTab = false) {
                        this.show(label);
                        this.progress = 15;
                        this.stepText = 'Menghubungi server...';

                        this._simInterval = setInterval(() => {
                            if (this.progress < 75) {
                                this.progress += Math.floor(Math.random() * 8) + 5;
                                if (this.progress >= 35 && this.progress < 65) {
                                    this.stepText = 'Merender dokumen PDF standar A4...';
                                } else if (this.progress >= 65) {
                                    this.stepText = 'Menyiapkan berkas unduhan...';
                                }
                            }
                        }, 200);

                        try {
                            // Tanpa download=1 server mengembalikan halaman pratinjau, bukan berkas PDF
                            const fileUrl = new URL(href, window.location.href);
                            if (!fileUrl.searchParams.has('download')) fileUrl.searchParams.set('download', '1');

                            const response = await fetch(fileUrl.toString(), {
                                method: 'GET',
                                headers: { 'Accept': 'application/pdf,application/zip,*/*' },
                                credentials: 'same-origin'
                            });

                            clearInterval(this._simInterval);

                            if (!response.ok) {
                                this.progress = 100;
                                this.stepText = 'Gagal mengunduh berkas (' + response.status + ')';
                                setTimeout(() => { this.hide(); }, 2000);
                                return;
                            }

                            this.progress = 85;
                            this.stepText = 'Menerima data berkas...';

                            const blob = await response.blob();
                            this.progress = 100;
                            this.stepText = 'Selesai! Mengunduh berkas...';

                            // Ekstrak nama file dari header Content-Disposition
                            let filename = 'dokumen.pdf';
                            const disp = response.headers.get('content-disposition');
                            if (disp) {
                                const match = disp.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                                if (match && match[1]) {
                                    filename = match[1].replace(/['"]/g, '').trim();
                                }
                            }

                            const blobUrl = window.URL.createObjectURL(blob);

                            if (isNewTab && response.headers.get('content-type')?.includes('pdf')) {
                                window.open(blobUrl, '_blank');
                            } else {
                                const a = document.createElement('a');
                                a.style.display = 'none';
                                a.href = blobUrl;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                                setTimeout(() => {
                                    a.remove();
                                    window.URL.revokeObjectURL(blobUrl);
                                }, 1000);
                            }

                            setTimeout(() => { this.hide(); }, 700);
                        } catch (err) {
                            clearInterval(this._simInterval);
                            console.error('Download error:', err);
                            window.location.href = href;
                            setTimeout(() => { this.hide(); }, 1200);
                        }
                    },

                    async startZipStream(streamUrl, label) {
                        this.show(label);
                        this.progress = 5;
                        this.stepText = 'Memulai proses kompresi berkas kontingen...';

                        try {
                            const response = await fetch(streamUrl, {
                                method: 'GET',
                                headers: { 'Accept': 'text/event-stream' },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                this.stepText = 'Gagal menghubungi server (' + response.status + ')';
                                setTimeout(() => { this.hide(); }, 2500);
                                return;
                            }

                            const reader = response.body.getReader();
                            const decoder = new TextDecoder();
                            let buffer = '';

                            while (true) {
                                const { done, value } = await reader.read();
                                if (done) break;

                                buffer += decoder.decode(value, { stream: true });
                                const lines = buffer.split('\n\n');
                                buffer = lines.pop();

                                for (const line of lines) {
                                    const trimmed = line.trim();
                                    if (!trimmed.startsWith('data:')) continue;
                                    const jsonStr = trimmed.slice(5).trim();
                                    try {
                                        const data = JSON.parse(jsonStr);
                                        if (data.percent !== undefined) {
                                            this.progress = data.percent;
                                        }
                                        if (data.step) {
                                            this.stepText = data.step;
                                        }
                                        if (data.status === 'completed' && data.download_url) {
                                            this.progress = 100;
                                            window.location.href = data.download_url;
                                            setTimeout(() => { this.hide(); }, 1000);
                                            return;
                                        }
                                        if (data.status === 'error') {
                                            this.stepText = data.step || 'Terjadi kesalahan.';
                                            setTimeout(() => { this.hide(); }, 3000);
                                            return;
                                        }
                                    } catch (e) {
                                        console.warn('SSE parse error:', e);
                                    }
                                }
                            }
                        } catch (err) {
                            console.error('SSE Stream error:', err);
                            this.stepText = 'Mengalihkan ke unduhan langsung...';
                            window.location.href = streamUrl.replace('-stream', '');
                            setTimeout(() => { this.hide(); }, 1500);
                        }
                    }
                };
            }
        </script>

        {{-- PWA Service Worker & Push Notification Integration --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js').then(function(registration) {
                        console.log('LT3 PWA ServiceWorker registered with scope: ', registration.scope);
                    }).catch(function(err) {
                        console.warn('LT3 PWA ServiceWorker registration failed: ', err);
                    });
                });
            }
        </script>
    </body>
</html>
