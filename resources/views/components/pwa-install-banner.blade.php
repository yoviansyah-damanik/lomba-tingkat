<div
    x-data="{
        deferredPrompt: null,
        showInstallBanner: false,
        isInstalled: false,
        pushSubscribed: false,
        notificationPermission: ('Notification' in window) ? Notification.permission : 'denied',

        init() {
            // Cek apakah aplikasi sudah berjalan dalam mode standalone PWA
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            if (isStandalone) {
                this.isInstalled = true;
                this.showInstallBanner = false;
                return;
            }

            // Cek apakah sebelumnya pernah di-dismiss
            const dismissedAt = localStorage.getItem('pwa_prompt_dismissed_at');
            const isDismissed = dismissedAt && (Date.now() - parseInt(dismissedAt, 10) < 3 * 24 * 60 * 60 * 1000);

            // Tangkap event sebelum instalasi browser
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                window.pwaDeferredPrompt = e;
                if (!isDismissed && !this.isInstalled) {
                    this.showInstallBanner = true;
                }
            });

            // Hubungkan trigger instalasi global (misal dari Footer)
            window.addEventListener('trigger-pwa-install', () => {
                this.installPwa();
            });
            window.installPwaApp = () => {
                this.installPwa();
            };

            // Tampilkan notifikasi mengambang secara halus setelah jeda pembukaan halaman
            if (!isDismissed && !this.isInstalled) {
                setTimeout(() => {
                    if (!this.isInstalled) {
                        this.showInstallBanner = true;
                    }
                }, 2500);
            }

            if ('serviceWorker' in navigator && 'PushManager' in window) {
                navigator.serviceWorker.ready.then((reg) => {
                    reg.pushManager.getSubscription().then((sub) => {
                        this.pushSubscribed = !(sub === null);
                    });
                });
            }
        },

        installPwa() {
            if (this.deferredPrompt) {
                this.deferredPrompt.prompt();
                this.deferredPrompt.userChoice.then((choice) => {
                    if (choice.outcome === 'accepted') {
                        this.showInstallBanner = false;
                        this.isInstalled = true;
                    }
                    this.deferredPrompt = null;
                });
            } else {
                const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
                if (isIos) {
                    alert('Untuk memasang di iPhone/iPad:\n1. Ketuk ikon Bagikan (Share ⎋) di Safari\n2. Gulir ke bawah lalu pilih \'Tambahkan ke Layar Utama\' (+)');
                } else {
                    alert('Untuk memasang aplikasi:\nBuka menu browser Anda (ikon ⋮ atau ⋯) dan pilih \'Pasang Aplikasi\' atau \'Tambahkan ke Layar Utama\'.');
                }
            }
        },

        dismissBanner() {
            this.showInstallBanner = false;
            localStorage.setItem('pwa_prompt_dismissed_at', Date.now().toString());
        },

        async enablePushNotifications() {
            if (!('Notification' in window) || !('serviceWorker' in navigator)) {
                alert('Perangkat Anda tidak mendukung Web Push Notification.');
                return;
            }

            try {
                const permission = await Notification.requestPermission();
                this.notificationPermission = permission;

                if (permission !== 'granted') {
                    alert('Izin notifikasi ditolak. Anda dapat mengaktifkannya melalui setelan browser.');
                    return;
                }

                const keyRes = await fetch('/api/push/vapid-public-key');
                const keyData = await keyRes.json();
                const publicKey = keyData.publicKey;

                const reg = await navigator.serviceWorker.ready;
                const convertedVapidKey = this.urlBase64ToUint8Array(publicKey);

                const subscription = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: convertedVapidKey
                });

                const subJson = subscription.toJSON();
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

                await fetch('/api/push-subscriptions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        endpoint: subJson.endpoint,
                        keys: subJson.keys,
                        content_encoding: 'aes128gcm'
                    })
                });

                this.pushSubscribed = true;
                alert('Notifikasi resmi LT III berhasil diaktifkan!');
            } catch (error) {
                console.error('Push subscription error:', error);
                alert('Gagal mengaktifkan notifikasi: ' + error.message);
            }
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    }"
    x-effect="window.__pwaBannerVisible = showInstallBanner; window.dispatchEvent(new CustomEvent('pwa-banner-change'))"
    class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 max-w-sm sm:max-w-md w-[calc(100%-2rem)] sm:w-auto pointer-events-none print:hidden"
>
    {{-- Notifikasi Mengambang Pasang Aplikasi PWA --}}
    <div
        x-show="showInstallBanner"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="relative pointer-events-auto overflow-hidden rounded-3xl border border-amber-300/80 bg-white/95 p-4 sm:p-5 shadow-2xl backdrop-blur-md dark:border-amber-500/30 dark:bg-zinc-900/95 ring-1 ring-black/5 dark:ring-white/10"
    >
        {{-- Efek Ambient Glow --}}
        <div class="absolute -right-8 -top-8 size-28 rounded-full bg-amber-400/15 blur-2xl pointer-events-none"></div>

        {{-- Tombol Tutup Silang di Sudut --}}
        <button
            type="button"
            x-on:click="dismissBanner()"
            class="absolute top-3.5 right-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition cursor-pointer p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800"
            title="Tutup notifikasi"
        >
            <flux:icon name="x-mark" class="size-4" />
        </button>

        <div class="flex items-start gap-3.5 pr-6">
            {{-- Ikon Aplikasi --}}
            <div class="flex size-11 sm:size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/20">
                <flux:icon name="device-phone-mobile" class="size-6" />
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-amber-900 dark:bg-amber-950/80 dark:text-amber-300">
                        <flux:icon name="bolt" class="size-3" />
                        Aplikasi Resmi
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-900 dark:bg-emerald-950/80 dark:text-emerald-300">
                        Mode Offline
                    </span>
                </div>
                <h4 class="text-xs sm:text-sm font-extrabold text-zinc-900 dark:text-zinc-100 leading-snug">
                    Pasang Aplikasi LT III di Layar Utama (Homescreen)
                </h4>
                <p class="mt-1 text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Akses lebih cepat dengan mode offline terintegrasi tanpa perlu membuka browser berulang kali.
                </p>
            </div>
        </div>

        {{-- Bilah Tombol Aksi --}}
        <div class="mt-3.5 flex items-center justify-end gap-2 pt-2.5 border-t border-zinc-100 dark:border-zinc-800/80">
            <flux:button
                variant="subtle"
                size="sm"
                x-on:click="dismissBanner()"
            >
                Nanti Saja
            </flux:button>
            <flux:button
                icon="arrow-down-tray"
                variant="primary"
                size="sm"
                x-on:click="installPwa()"
            >
                Pasang Sekarang
            </flux:button>
        </div>
    </div>
</div>
