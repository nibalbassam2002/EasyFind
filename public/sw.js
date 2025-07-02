// 1. عرّف الملفات أولاً
const filesToCache = [
    '/',
    '/offline.html',
    // أضف هنا ملفات CSS و JS الرئيسية إذا أردت
    // مثلاً: '/frontend/style.css'
];

const cacheName = 'offline-v1'; // استخدم اسماً ذا إصدار لتسهيل التحديثات

// عند التثبيت (Install): قم بتخزين الملفات الأساسية
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(cacheName).then((cache) => {
            console.log("Service Worker: Caching essential files");
            return cache.addAll(filesToCache);
        })
    );
});

// عند التفعيل (Activate): قم بحذف الـ Cache القديم
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== cacheName) {
                        return caches.delete(name);
                    }
                })
            );
        })
    );
});

// عند طلب أي ملف (Fetch): استراتيجية "الشبكة أولاً، ثم الكاش"
self.addEventListener("fetch", (event) => {
    // لا تتعامل مع الطلبات التي ليست من نوع GET
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // إذا كان الطلب ناجحاً، قم بتخزين نسخة منه في الكاش وأعد الأصل للمتصفح
                if (response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(cacheName).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                // إذا فشل طلب الشبكة (لا يوجد إنترنت)، ابحث في الكاش
                return caches.match(event.request).then((response) => {
                    // إذا وجدته في الكاش، أعده. إذا لم تجده، أعد صفحة الأوفلاين
                    return response || caches.match('/offline.html');
                });
            })
    );
});