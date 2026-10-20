<section
    x-data="{
        supported: ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window),
        permission: ('Notification' in window) ? Notification.permission : 'denied',
        subscribed: false,
        loading: false,
        message: '',
        messageType: 'info',
        isIos: /iphone|ipad|ipod/i.test(navigator.userAgent),
        isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,

        async init() {
            if (!this.supported) return;
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.getSubscription();
            this.subscribed = sub !== null;
            if (sub && this.permission === 'granted') {
                const json = sub.toJSON();
                this.request('POST', '/api/push-subscriptions', { endpoint: json.endpoint, keys: json.keys, content_encoding: 'aes128gcm' }).catch(() => {});
            }
        },

        csrf() {
            return document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
        },

        notify(text, type = 'info') {
            this.message = text;
            this.messageType = type;
        },

        async request(method, url, body) {
            return fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                body: JSON.stringify(body),
            });
        },

        async enable() {
            this.loading = true;
            try {
                const permission = await Notification.requestPermission();
                this.permission = permission;
                if (permission !== 'granted') {
                    this.notify('Izin notifikasi ditolak. Aktifkan melalui setelan situs di browser Anda.', 'error');
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
                this.notify('Notifikasi aktif di perangkat ini.', 'success');
            } catch (e) {
                this.notify('Gagal mengaktifkan notifikasi: ' + e.message, 'error');
            } finally {
                this.loading = false;
            }
        },

        async disable() {
            this.loading = true;
            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    await this.request('DELETE', '/api/push-subscriptions', { endpoint: sub.endpoint });
                    await sub.unsubscribe();
                }
                this.subscribed = false;
                this.notify('Notifikasi dinonaktifkan di perangkat ini.', 'info');
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
    class="border-t border-amber-200/50 bg-amber-50/60 dark:border-amber-500/20 dark:bg-zinc-900/60 print:hidden"
>
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
        <div class="flex items-start gap-3">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                <flux:icon name="bell" class="size-5" />
            </div>
            <div>
                <h4 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Notifikasi Resmi LT III</h4>
                <p class="text-xs text-zinc-600 dark:text-zinc-400">
                    Terima pengumuman dan pengingat jadwal langsung di perangkat Anda, tanpa perlu masuk akun.
                </p>
                <template x-if="isIos && !isStandalone">
                    <p class="mt-1 text-[11px] font-medium text-amber-700 dark:text-amber-400">
                        iPhone/iPad: pasang aplikasi ke Layar Utama (Bagikan &rsaquo; Tambahkan ke Layar Utama) lalu buka dari ikonnya agar notifikasi dapat diaktifkan.
                    </p>
                </template>
                <p x-show="!supported" x-cloak class="mt-1 text-[11px] font-medium text-rose-600">
                    Browser ini belum mendukung Web Push.
                </p>
                <p x-show="message" x-cloak x-text="message"
                   class="mt-1 text-[11px] font-medium"
                   :class="{ 'text-emerald-600': messageType === 'success', 'text-rose-600': messageType === 'error', 'text-zinc-500': messageType === 'info' }"></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2" x-show="supported" x-cloak>
            <flux:button x-show="!subscribed" icon="bell-alert" variant="primary" size="sm" x-on:click="enable()" x-bind:disabled="loading">
                Aktifkan Notifikasi
            </flux:button>
            <flux:button x-show="subscribed" x-cloak icon="bell-slash" size="sm" x-on:click="disable()" x-bind:disabled="loading">
                Nonaktifkan
            </flux:button>
        </div>
    </div>
</section>
