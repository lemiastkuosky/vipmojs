// sw.js - Service Worker

const CACHE_NAME = 'vip-emojis-cache-v1'; // Um nome para o cache
const urlsToCache = [
  '/',
  '/index.html'
  // Adicione aqui outros arquivos essenciais se tiver, como um CSS externo.
];

// Evento de instalação: baixa e armazena os arquivos principais.
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache aberto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Evento de ativação: limpa caches antigos para evitar conflitos.
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache.startsWith('vip-emojis-cache-') && cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
});