const CACHE = 'colegio-estaticos-v3';
const ESTATICOS = [
  './assets/css/output.css',
  './assets/css/app.css',
  './assets/css/inter.css',
  './assets/css/bootstrap-icons.min.css',
  './assets/js/alpine.min.js',
  './assets/js/html5-qrcode.min.js',
  './assets/js/asistencia-escanear.js'
];

self.addEventListener('install', (evento) => {
  evento.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(ESTATICOS)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (evento) => {
  evento.waitUntil(caches.keys().then((nombres) => Promise.all(nombres.filter((nombre) => nombre !== CACHE).map((nombre) => caches.delete(nombre)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', (evento) => {
  if (evento.request.method !== 'GET') return;
  // Las vistas PHP y sus datos nunca se sirven desde caché: siempre muestran la versión actual.
  if (evento.request.mode === 'navigate' || evento.request.url.includes('index.php')) {
    evento.respondWith(fetch(evento.request).catch(() => new Response('No se pudo conectar con el sistema. Actualice la página.', { status: 503, headers: { 'Content-Type': 'text/plain; charset=UTF-8' } })));
    return;
  }
  evento.respondWith(caches.match(evento.request).then((respuesta) => respuesta || fetch(evento.request)));
});
