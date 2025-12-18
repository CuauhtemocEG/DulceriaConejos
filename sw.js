/**
 * Service Worker para Dulcería POS
 * Versión: 1.0.0
 */

const CACHE_NAME = 'dulceria-pos-v2.0.0';
const ASSETS_TO_CACHE = [
    '/DulceriaConejos/',
    '/DulceriaConejos/pages/login.php',
    '/DulceriaConejos/pages/dashboard.php',
    '/DulceriaConejos/pages/pos.php',
    '/DulceriaConejos/pages/inventario.php',
    '/DulceriaConejos/pages/productos.php',
    '/DulceriaConejos/pages/ventas.php',
    '/DulceriaConejos/pages/reportes.php',
    '/DulceriaConejos/pages/usuarios.php',
    '/DulceriaConejos/pages/roles.php',
    '/DulceriaConejos/pages/configuracion.php',
    // CDN resources
    'https://cdn.tailwindcss.com',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
    console.log('[SW] Instalando Service Worker...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Cacheando archivos de la app');
                return cache.addAll(ASSETS_TO_CACHE);
            })
            .catch((error) => {
                console.error('[SW] Error al cachear archivos:', error);
            })
    );
    self.skipWaiting();
});

// Activación del Service Worker
self.addEventListener('activate', (event) => {
    console.log('[SW] Activando Service Worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[SW] Eliminando caché antigua:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Estrategia de caché: Network First, fallback to Cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorar peticiones que no sean del mismo origen o API
    if (url.origin !== location.origin && !url.origin.includes('cdn')) {
        return;
    }

    // Para peticiones API: siempre intentar red primero
    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(request)
                .catch(() => {
                    return new Response(
                        JSON.stringify({ 
                            success: false, 
                            message: 'Sin conexión a internet. Algunos datos pueden estar desactualizados.' 
                        }),
                        { 
                            headers: { 'Content-Type': 'application/json' },
                            status: 503
                        }
                    );
                })
        );
        return;
    }

    // Para recursos estáticos: Cache First, fallback to Network
    event.respondWith(
        caches.match(request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    // Retornar del cache pero actualizar en background
                    fetch(request).then((response) => {
                        if (response && response.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, response);
                            });
                        }
                    }).catch(() => {});
                    return cachedResponse;
                }

                // Si no está en cache, obtener de la red
                return fetch(request)
                    .then((response) => {
                        // Guardar en cache si es exitoso
                        if (response && response.status === 200) {
                            const responseClone = response.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, responseClone);
                            });
                        }
                        return response;
                    })
                    .catch(() => {
                        // Si falla, mostrar página offline
                        if (request.destination === 'document') {
                            return caches.match('/DulceriaConejos/pages/offline.html');
                        }
                    });
            })
    );
});

// Manejo de notificaciones push (futuro)
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Dulcería POS';
    const options = {
        body: data.body || 'Nueva notificación',
        icon: '/DulceriaConejos/icons/icon-192x192.png',
        badge: '/DulceriaConejos/icons/icon-96x96.png',
        vibrate: [200, 100, 200],
        data: data.url || '/DulceriaConejos/'
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Manejo de clicks en notificaciones
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data)
    );
});
