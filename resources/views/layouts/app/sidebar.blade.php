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
                            <flux:sidebar.item icon="user-group" :href="route('admin.officials.index')" :current="request()->routeIs('admin.officials.*')" wire:navigate>
                                {{ __('Aparatur Panitia & Juri') }}
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
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('admin.assessments.index')" :current="request()->routeIs('admin.assessments.*')" wire:navigate>
                                {{ __('Audit Penilaian & Log') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="pencil-square" :href="route('judge.index')" :current="request()->routeIs('judge.*')" wire:navigate>
                                {{ __('Cockpit Penjurian') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="chart-bar" :href="route('admin.standings.index')" :current="request()->routeIs('admin.standings.*') || request()->routeIs('standings')" wire:navigate>
                                {{ __('Rekap Nilai & Klasemen') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="bell" :href="route('admin.push-broadcast.index')" :current="request()->routeIs('admin.push-broadcast.*')" wire:navigate>
                                {{ __('Siaran Notifikasi (PWA)') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="document-text" :href="route('admin.reports.index')" :current="request()->routeIs('admin.reports.*')" wire:navigate>
                                {{ __('Pusat Laporan & Ekspor') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="megaphone" :href="route('admin.announcements.index')" :current="request()->routeIs('admin.announcements.*')" wire:navigate>
                                {{ __('Pengumuman Kegiatan') }}
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="user-group" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                                {{ __('Pengguna & Hak Akses') }}
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
                            <flux:sidebar.item icon="chart-bar" :href="route('standings')" :current="request()->routeIs('standings') || request()->routeIs('admin.standings.*')" wire:navigate>
                                {{ __('Rekap Nilai & Klasemen') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>
                    @endif

                    {{-- Navigasi Khusus Peserta / Regu --}}
                    @if(auth()->user()->isParticipant())
                        <flux:sidebar.group :heading="__('Peserta Regu')" class="grid">
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
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
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

        <flux:footer class="!py-3.5 border-t border-zinc-200/80 bg-white/60 dark:border-zinc-800 dark:bg-zinc-900/60 backdrop-blur-sm">
            <x-official-footer :compact="true" />
        </flux:footer>

        @persist('toast')
            <flux:toast.group position="top end">
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

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
