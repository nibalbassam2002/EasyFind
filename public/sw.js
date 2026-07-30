// 1. عرّف الملفات أولاً
const filesToCache = [
    '/',
    '/offline.html',
];

const cacheName = 'offline-v2'; // *** غيّرنا الإصدار لإجبار التحديث ***

// عند التثبيت (Install)
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(cacheName).then((cache) => {
            console.log("Service Worker: Caching essential files");
            return cache.addAll(filesToCache);
        })
    );
    self.skipWaiting(); // *** تفعيل فوري بدون انتظار ***
});

// عند التفعيل (Activate)
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== cacheName) {
                        console.log("Service Worker: Deleting old cache:", name);
                        return caches.delete(name);
                    }
                })
            );
        })
    );
    self.clients.claim(); // *** تطبيق فوري على جميع الصفحات المفتوحة ***
});

// عند طلب أي ملف (Fetch)
self.addEventListener("fetch", (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    const isHTMLRequest = event.request.headers.get('accept')?.includes('text/html');
    const isAuthPage = url.pathname.includes('/login') || 
                       url.pathname.includes('/register') ||
                       url.pathname.includes('/logout');

    // صفحات HTML والمصادقة: شبكة فقط بدون كاش
    if (isHTMLRequest || isAuthPage) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // باقي الملفات (CSS, JS, Images): شبكة أولاً ثم كاش
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(cacheName).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request).then((response) => {
                    return response || caches.match('/offline.html');
                });
            })
    );
});