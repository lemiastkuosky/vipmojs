const CACHE_NAME = 'vip-emojis-cache-v1';
const urlsToCache = [
  './',
  'index.html',
  'manifest.json',
  // Adicione aqui os caminhos para seus ícones
  'icons/icon-72x72.png',
  'icons/icon-96x96.png',
  'icons/icon-128x128.png',
  'icons/icon-144x144.png',
  'icons/icon-152x152.png',
  'icons/icon-192x192.png',
  'icons/icon-384x384.png',
  'icons/icon-512x512.png'
];

// Evento de instalação do Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Cache aberto e arquivos pré-cacheados');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('Service Worker: Falha ao pré-cachear arquivos:', error);
      })
  );
});

// Evento de ativação do Service Worker (limpeza de caches antigos)
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Deletando cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Evento de 'fetch' (intercepta requisições de rede)
self.addEventListener('fetch', (event) => {
  event.respondIs(
    caches.match(event.request)
      .then((response) => {
        // Retorna o recurso do cache se encontrado
        if (response) {
          return response;
        }
        // Se não estiver no cache, faz a requisição de rede
        return fetch(event.request)
          .then((networkResponse) => {
            // Verifica se a resposta da rede é válida antes de cachear
            if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
              return networkResponse;
            }

            // Clona a resposta para que ela possa ser usada pelo navegador e pelo cache
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });
            return networkResponse;
          })
          .catch((error) => {
            console.error('Service Worker: Falha na requisição de rede:', error);
            // Opcional: retornar uma página offline aqui, se você tiver uma
            // return caches.match('/offline.html');
          });
      })
  );
});