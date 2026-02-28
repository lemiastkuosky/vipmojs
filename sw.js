const CACHE_NAME = 'vipmojs-cache-v1';

// Arquivos principais que o app precisa para abrir sem internet
const APP_SHELL = [
  '/',
  '/index.html',
  '/manifest.json',
  'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css',
  'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js'
];

// INSTALAÇÃO: Salva os arquivos essenciais no celular do usuário
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[ServiceWorker] Salvando App Shell (Modo Offline)');
      return cache.addAll(APP_SHELL);
    })
  );
});

// ATIVAÇÃO: Limpa caches velhos quando você atualiza a versão do app
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[ServiceWorker] Apagando cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// INTERCEPTAÇÃO: O "Cérebro" do modo offline
self.addEventListener('fetch', (event) => {
  
  // 1. Ignora requisições do Firebase (Deixa o banco de dados trabalhar livremente)
  if (event.request.url.includes('firestore') || 
      event.request.url.includes('firebase') || 
      event.request.url.includes('googleapis')) {
    return;
  }

  // 2. Estratégia "Network First, falling back to cache" (Rede primeiro, Cache como plano B)
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Se tem internet e carregou com sucesso, atualiza o backup salvo no celular
        if (response && response.status === 200 && response.type === 'basic') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Se FALHOU (Usuário está Offline ou 3G caiu), busca o arquivo salvo no celular!
        console.log('[ServiceWorker] Sem internet! Carregando do cache:', event.request.url);
        return caches.match(event.request).then(cachedResponse => {
            // Se não achar o arquivo específico, devolve o index.html principal
            return cachedResponse || caches.match('/index.html');
        });
      })
  );
});