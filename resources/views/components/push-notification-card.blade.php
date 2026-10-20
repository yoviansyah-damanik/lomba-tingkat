@props(['sidebar' => false])

{{-- Notifikasi mengambang Aktifkan Notifikasi (gaya sama dengan banner Pasang Aplikasi, posisi kiri-bawah) --}}
<div
    x-data="{
        supported: ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window),
        permission: ('Notification' in window) ? Notification.permission : 'denied',
        subscribed: true,
        dismissed: false,
        ready: false,
        installBannerVisible: false,
        loading: false,
        message: '',
        messageType: 'info',
        isIos: /iphone|ipad|ipod/i.test(navigator.userAgent),
        isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,

        get visible() {
            const mobile = window.innerWidth < 640;
            const eligible = this.supported || (this.isIos && !this.isStandalone);
            return this.ready && eligible && !this.subscribed && !this.dismissed
                && this.permission !== 'denied' && !(mobile && this.installBannerVisible);
        },

        async init() {
            try {
                const at = localStorage.getItem('push_prompt_dismissed_at');
                this.dismissed = !!at && (Date.now() - parseInt(at, 10) < 3 * 24 * 60 * 60 * 1000);
            } catch (e) {}

            window.addEventListener('pwa-banner-change', () => {
                this.installBannerVisible = window.__pwaBannerVisible === true;
            });

            if (this.supported) {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                this.subscribed = sub !== null;
                if (sub && this.permission === 'granted') {
                    const json = sub.toJSON();
                    this.request('POST', '/api/push-subscriptions', { endpoint: json.endpoint, keys: json.keys, content_encoding: 'aes128gcm' }).catch(() => {});
                }
            } else {
                this.subscribed = false;
            }

            setTimeout(() => this.ready = true, 6000);
        },

        csrf() {
            return document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
        },

        async request(method, url, body) {
            return fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                body: JSON.stringify(body),
            });
        },

        dismiss() {
            this.dismissed = true;
            try { localStorage.setItem('push_prompt_dismissed_at', Date.now().toString()); } catch (e) {}
        },

        async enable() {
            this.loading = true;
            this.message = '';
            try {
                const permission = await Notification.requestPermission();
                this.permission = permission;
                if (permission !== 'granted') {
                    this.messageType = 'error';
                    this.message = 'Izin notifikasi ditolak. Aktifkan melalui setelan situs di browser Anda.';
                    return;
                }

                const keyData = await (await fetch('/api/push/vapid-public-key')).json();
                if (!keyData.publicKey) throw new Error('Kunci VAPID server belum dikonfigurasi.');

                const reg = await navigator.serviceWorker.ready;
                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: this.urlBase64ToUint8Array(keyData.publicKey),
                    });
                }

                const json = sub.toJSON();
                const res = await this.request('POST', '/api/push-subscriptions', { endpoint: json.endpoint, keys: json.keys, content_encoding: 'aes128gcm' });
                if (!res.ok) throw new Error('Server menolak pendaftaran (' + res.status + ').');

                this.subscribed = true;
            } catch (e) {
                this.messageType = 'error';
                this.message = 'Gagal mengaktifkan notifikasi: ' + e.message;
            } finally {
                this.loading = false;
            }
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const raw = window.atob((base64String + padding).replace(/-/g, '+').replace(/_/g, '/'));
            return Uint8Array.from(raw, (c) => c.charCodeAt(0));
        },
    }"
    class="fixed bottom-4 left-4 sm:bottom-6 sm:left-6 {{ $sidebar ? 'lg:left-72' : '' }} z-50 max-w-sm sm:max-w-md w-[calc(100%-2rem)] sm:w-auto pointer-events-none print:hidden"
>
    <div
        x-show="visible"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="relative pointer-events-auto overflow-hidden rounded-3xl border border-amber-300/80 bg-white/95 p-4 sm:p-5 shadow-2xl backdrop-blur-md dark:border-amber-500/30 dark:bg-zinc-900/95 ring-1 ring-black/5 dark:ring-white/10"
    >
        <div class="absolute -right-8 -top-8 size-28 rounded-full bg-amber-400/15 blur-2xl pointer-events-none"></div>

        <button
            type="button"
            x-on:click="dismiss()"
            class="absolute top-3.5 right-3.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition cursor-pointer p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800"
            title="Tutup notifikasi"
        >
            <flux:icon name="x-mark" class="size-4" />
        </button>

        <div class="flex items-start gap-3.5 pr-6">
            <div class="flex size-11 sm:size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/20">
                <flux:icon name="bell" class="size-6" />
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-amber-900 dark:bg-amber-950/80 dark:text-amber-300">
                        <flux:icon name="bell-alert" class="size-3" />
                        Notifikasi Resmi
                    </span>
                </div>
                <h4 class="text-xs sm:text-sm font-extrabold text-zinc-900 dark:text-zinc-100 leading-snug">
                    Aktifkan Notifikasi Aplikasi LT III
                </h4>
                <p class="mt-1 text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Terima pengumuman dan pengingat jadwal langsung di perangkat Anda, tanpa perlu masuk akun.
                </p>
                <p x-show="isIos && !isStandalone" x-cloak class="mt-1 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                    iPhone/iPad: pasang aplikasi ke Layar Utama (Bagikan &rsaquo; Tambahkan ke Layar Utama), lalu buka dari ikonnya.
                </p>
                <p x-show="message" x-cloak x-text="message"
                   class="mt-1 text-[11px] font-medium"
                   :class="messageType === 'error' ? 'text-rose-600' : 'text-emerald-600'"></p>
            </div>
        </div>

        <div class="mt-3.5 flex items-center justify-end gap-2 pt-2.5 border-t border-zinc-100 dark:border-zinc-800/80">
            <flux:button variant="subtle" size="sm" x-on:click="dismiss()">
                Nanti Saja
            </flux:button>
            <flux:button x-show="supported" icon="bell-alert" variant="primary" size="sm" x-on:click="enable()" x-bind:disabled="loading">
                Aktifkan Sekarang
            </flux:button>
        </div>
    </div>
</div>
