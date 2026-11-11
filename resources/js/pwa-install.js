/**
 * Pengelola global instalasi Progressive Web App (PWA) LT III.
 *
 * Menangani:
 * - Penangkapan event `beforeinstallprompt` & `appinstalled` lintas navigasi Livewire (`wire:navigate`)
 * - Pendaftaran Service Worker `/sw.js`
 * - Pemicu instalasi aplikasi (`window.installPwaApp()` dan event `trigger-pwa-install`)
 * - Dialog panduan instalasi kontekstual sesuai perangkat (iOS/iPadOS, Android, atau Desktop)
 *   apabila browser tidak menyediakan prompt otomatis `beforeinstallprompt`.
 */

window.pwaDeferredPrompt = window.pwaDeferredPrompt || null;
window.__pwaIsInstalled = window.__pwaIsInstalled || false;

/**
 * Periksa apakah aplikasi sudah berjalan dalam mode standalone (terpasang di perangkat)
 */
export function isPwaStandalone() {
    return (
        window.__pwaIsInstalled === true ||
        window.matchMedia('(display-mode: standalone)').matches ||
        window.matchMedia('(display-mode: fullscreen)').matches ||
        window.matchMedia('(display-mode: minimal-ui)').matches ||
        window.navigator.standalone === true
    );
}

window.isPwaStandalone = isPwaStandalone;

// Tangkap event beforeinstallprompt bila belum ditangkap oleh skrip inline di <head>
window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    window.pwaDeferredPrompt = event;
    window.dispatchEvent(new CustomEvent('pwa-prompt-ready'));
});

// Tangkap event saat aplikasi berhasil dipasang ke perangkat
window.addEventListener('appinstalled', () => {
    window.pwaDeferredPrompt = null;
    window.__pwaIsInstalled = true;
    window.dispatchEvent(new CustomEvent('pwa-app-installed'));

    window.dispatchEvent(
        new CustomEvent('toast-show', {
            detail: {
                duration: 5000,
                slots: {
                    heading: 'Aplikasi Berhasil Dipasang',
                    text: 'Aplikasi LT III Kwarcab Padangsidimpuan kini tersedia di Layar Utama perangkat Anda.',
                },
                dataset: { variant: 'success' },
            },
        })
    );
});

// Pastikan Service Worker terdaftar agar syarat instalasi PWA terpenuhi di seluruh halaman
if ('serviceWorker' in navigator && !window.__lt3SwRegistered) {
    window.__lt3SwRegistered = true;
    const registerSw = () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.warn('LT3 PWA ServiceWorker registration failed:', err);
        });
    };

    if (document.readyState === 'complete') {
        registerSw();
    } else {
        window.addEventListener('load', registerSw, { once: true });
    }
}

/**
 * Deteksi platform perangkat pengguna untuk menampilkan panduan instalasi yang sesuai
 */
function detectPlatformGuide() {
    const ua = (window.navigator.userAgent || '').toLowerCase();
    const isIos =
        /iphone|ipad|ipod/.test(ua) ||
        (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
    const isAndroid = /android/.test(ua);

    if (isIos) {
        return {
            badge: 'iPhone / iPad (iOS)',
            subtitle: 'Pasang aplikasi ke Layar Utama melalui menu Bagikan di Safari:',
            steps: [
                'Ketuk ikon <strong>Bagikan (Share)</strong> di bilah bawah atau atas Safari.',
                'Gulir daftar menu ke bawah lalu pilih <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong>.',
                'Ketuk tombol <strong>"Tambah" (Add)</strong> di pojok kanan atas untuk menyelesaikan.',
            ],
        };
    }

    if (isAndroid) {
        return {
            badge: 'Perangkat Android',
            subtitle: 'Pasang aplikasi langsung ke Layar Utama ponsel Anda:',
            steps: [
                'Ketuk ikon menu browser <strong>(⋮)</strong> di pojok kanan atas layar.',
                'Pilih menu <strong>"Pasang aplikasi" (Install app)</strong> atau <strong>"Tambahkan ke Layar utama"</strong>.',
                'Ketuk <strong>"Pasang" (Install)</strong> pada dialog konfirmasi yang muncul.',
            ],
        };
    }

    return {
        badge: 'Komputer / Desktop',
        subtitle: 'Pasang aplikasi LT III di komputer Anda untuk akses cepat:',
        steps: [
            'Klik ikon <strong>Pasang (Install)</strong> di sisi kanan bilah alamat (address bar) Chrome / Edge, atau buka menu browser <strong>(⋮ / ⋯)</strong>.',
            'Pilih menu <strong>"Simpan dan bagikan" / "Aplikasi"</strong> &rarr; <strong>"Pasang LT III Kwarcab Padangsidimpuan"</strong>.',
            'Klik tombol <strong>"Pasang" (Install)</strong> pada jendela konfirmasi.',
        ],
    };
}

/**
 * Tampilkan modal panduan instalasi PWA di lapisan teratas (#top-layer)
 */
function showInstallGuideModal() {
    const existing = document.getElementById('lt3-pwa-install-guide-dialog');
    if (existing) {
        existing.remove();
    }

    const guide = detectPlatformGuide();
    const dialog = document.createElement('dialog');
    dialog.id = 'lt3-pwa-install-guide-dialog';
    dialog.className =
        'm-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-zinc-200 bg-white p-0 text-zinc-900 shadow-2xl backdrop:bg-zinc-950/60 backdrop:backdrop-blur-xs dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';

    const stepsHtml = guide.steps
        .map(
            (step, index) => `
            <li class="flex items-start gap-3 rounded-xl border border-zinc-200/80 bg-zinc-50/80 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white shadow-xs">${index + 1}</span>
                <span class="text-xs leading-relaxed text-zinc-700 dark:text-zinc-300">${step}</span>
            </li>`
        )
        .join('');

    dialog.innerHTML = `
        <div class="p-6">
            <div class="flex items-start gap-3.5">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-md">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                        ${guide.badge}
                    </span>
                    <h3 class="mt-1 text-base font-extrabold text-zinc-900 dark:text-zinc-100">
                        Pasang Aplikasi LT III
                    </h3>
                    <p class="mt-1 text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                        ${guide.subtitle}
                    </p>
                </div>
            </div>
            <ol class="mt-4 space-y-2.5">
                ${stepsHtml}
            </ol>
        </div>
        <div class="flex justify-end border-t border-zinc-100 bg-zinc-50 px-6 py-3.5 dark:border-zinc-800 dark:bg-zinc-900/60">
            <button type="button" data-close-pwa-guide class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-amber-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 cursor-pointer">
                Mengerti
            </button>
        </div>
    `;

    document.body.append(dialog);

    const closeDialog = () => {
        if (dialog.open) {
            dialog.close();
        }
        dialog.remove();
    };

    dialog.querySelector('[data-close-pwa-guide]')?.addEventListener('click', closeDialog);
    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) {
            closeDialog();
        }
    });
    dialog.addEventListener('close', closeDialog);
    dialog.addEventListener('cancel', closeDialog);

    dialog.showModal();
}

/**
 * Jalankan proses instalasi PWA ke perangkat
 */
export async function installPwaApp() {
    if (isPwaStandalone()) {
        if (typeof window.appAlert === 'function') {
            window.appAlert('Aplikasi LT III sudah terpasang di perangkat Anda.', {
                title: 'Aplikasi Sudah Terpasang',
                tone: 'info',
            });
        }
        return;
    }

    const promptEvent = window.pwaDeferredPrompt;

    if (promptEvent && typeof promptEvent.prompt === 'function') {
        try {
            await promptEvent.prompt();
            const choice = await promptEvent.userChoice;
            window.pwaDeferredPrompt = null;

            if (choice && choice.outcome === 'accepted') {
                window.__pwaIsInstalled = true;
                window.dispatchEvent(new CustomEvent('pwa-app-installed'));
            } else {
                window.dispatchEvent(new CustomEvent('pwa-prompt-consumed'));
            }
            return;
        } catch (err) {
            console.warn('PWA prompt invocation failed:', err);
            window.pwaDeferredPrompt = null;
        }
    }

    showInstallGuideModal();
}

window.installPwaApp = installPwaApp;

if (!window.__lt3PwaTriggerBound) {
    window.__lt3PwaTriggerBound = true;
    window.addEventListener('trigger-pwa-install', () => {
        installPwaApp();
    });
}

