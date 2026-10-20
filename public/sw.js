// Progressive Web App (PWA) Service Worker — LT III Kwarcab Padangsidimpuan 2026
const CACHE_NAME = 'lt3-pwa-v1';
const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/favicon.png',
    '/logo.png'
];

// 1. Install Event: Pra-cache aset offline dan shell aplikasi
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event: Bersihkan cache versi lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event: Network-First dengan Fallback Offline untuk Navigasi Halaman
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Abaikan permintaan non-GET dan Livewire internal updates
    if (request.method !== 'GET' || request.url.includes('/livewire/update')) {
        return;
    }

    // Untuk navigasi halaman (HTML document)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    // Untuk aset statis (Gambar, font, icons)
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(request).then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return networkResponse;
            }).catch(() => {
                // Fallback jika aset tidak tersedia saat offline
                return cachedResponse;
            });
        })
    );
});

// 4. Push Event: Penerimaan Notifikasi Web Push (VAPID)
self.addEventListener('push', (event) => {
    let payload = {
        title: 'Pemberitahuan LT III 2026',
        body: 'Pembaruan informasi terkini dari Panitia Pelaksana.',
        icon: '/logo.png',
        badge: '/favicon.png',
        url: '/',
        tag: 'lt3-push-' + Date.now()
    };

    if (event.data) {
        try {
            const parsed = event.data.json();
            payload = Object.assign(payload, parsed);
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon,
        badge: payload.badge,
        data: {
            url: payload.url || '/'
        },
        tag: payload.tag,
        vibrate: [150, 50, 150],
        renotify: true
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// 5. Notification Click Event: Buka URL target saat notifikasi diklik pengguna
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
