// =========================================================================
// 1. IMPORTAÇÃO E CONFIGURAÇÃO DO FIREBASE (Notificações em Segundo Plano)
// =========================================================================
importScripts('https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyB7tyWt4UlivjnJVevkXDZ1wwgPrzV1hvc",
    authDomain: "meus-recibos-vip.firebaseapp.com",
    projectId: "meus-recibos-vip",
    storageBucket: "meus-recibos-vip.firebasestorage.app",
    messagingSenderId: "254959368683",
    appId: "1:254959368683:web:80676a26f614040df6c1ae"
});

const messaging = firebase.messaging();

// Esta função "ouve" as mensagens quando o App está fechado (Celular no bolso)
messaging.onBackgroundMessage(function(payload) {
  console.log('[ServiceWorker] Notificação recebida em segundo plano: ', payload);

  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/icon.png', // Opcional: Caminho para a logo do seu app
    badge: '/icon.png' // Opcional: Ícone pequenininho preto e branco do Android
  };

  return self.registration.showNotification(notificationTitle, notificationOptions);
});


// =========================================================================
// 2. CÓDIGO ORIGINAL DO SEU APP (MODO OFFLINE E CACHE)
// =========================================================================
const CACHE_NAME = 'vipmojs-cache-v2'; // Mudado para v2 para forçar a atualização nos clientes!

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
  self.skipWaiting(); // Força o novo ServiceWorker a assumir imediatamente
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
  self.clients.claim(); // Garante que a página atual já use este ServiceWorker
});

// INTERCEPTAÇÃO: O "Cérebro" do modo offline
self.addEventListener('fetch', (event) => {
  
  // 1. Ignora requisições do Firebase (Deixa o banco e as notificações trabalharem livremente)
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
