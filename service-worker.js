/**
 * Service Worker para Dulcería POS
 * Versión 1.0.0
 */

const CACHE_NAME = 'dulceria-pos-v1.0.0';
const OFFLINE_URL = '/Dulcería/pages/offline.html';

// Archivos esenciales para cachear
const ESSENTIAL_FILES = [
    '/Dulcería/pages/offline.html',
    '/Dulcería/public/img/DulceriaConejos.png',
    '/Dulcería/manifest.json'
];

// Archivos de la aplicación para cachear
const APP_FILES = [
    '/Dulcería/pages/dashboard.php',
    '/Dulcería/pages/layout.php',
    '/Dulcería/pages/pos.php',
    '/Dulcería/pages/ventas.php',
    '/Dulcería/pages/productos.php',
    '/Dulcería/pages/inventario.php',
    '/Dulcería/pages/usuarios.php',
    '/Dulcería/pages/roles.php',
    '/Dulcería/pages/reportes.php',
    '/Dulcería/pages/configuracion.php'
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Instalando...');
    
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Cacheando archivos esenciales');
            return cache.addAll(ESSENTIAL_FILES);
        }).catch((error) => {
            console.error('[Service Worker] Error al cachear:', error);
        })
    );
    
    // Forzar activación inmediata
    self.skipWaiting();
});

// Activación del Service Worker
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activando...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Eliminando caché antigua:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    // Tomar control de todas las páginas inmediatamente
    return self.clients.claim();
});

// Estrategia de caché: Network First, falling back to Cache
self.addEventListener('fetch', (event) => {
    // Ignorar requests que no sean GET
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Ignorar requests a la API (siempre necesitan red)
    if (event.request.url.includes('/api/')) {
        return;
    }
    
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Si la respuesta es válida, cachearla
                if (response && response.status === 200) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                // Si no hay red, intentar obtener del caché
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    
                    // Si es una página HTML, mostrar página offline
                    if (event.request.headers.get('accept').includes('text/html')) {
                        return caches.match(OFFLINE_URL);
                    }
                });
            })
    );
});

// Escuchar mensajes del cliente
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(CACHE_NAME).then((cache) => {
                return cache.addAll(event.data.urls);
            })
        );
    }
});

// Sincronización en segundo plano
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-ventas') {
        event.waitUntil(syncVentas());
    }
});

async function syncVentas() {
    console.log('[Service Worker] Sincronizando ventas pendientes...');
    // Aquí puedes implementar lógica de sincronización
}

// Notificaciones push
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Dulcería POS';
    const options = {
        body: data.body || 'Nueva notificación',
        icon: '/Dulcería/icons/icon-192x192.png',
        badge: '/Dulcería/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: data.data || {}
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Click en notificación
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/Dulcería/pages/dashboard.php')
    );
});
