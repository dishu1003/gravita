const CACHE_NAME = 'perfume-v2';
const PRECACHE = [
  '/assets/css/main.css',
  '/assets/js/main.js',
  '/assets/js/checkout.js'
];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE)));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))))
  );
});

self.addEventListener('fetch', (e) => {
  const url = new URL(e.request.url);
  if (url.pathname.startsWith('/assets/images/') || url.pathname.startsWith('/uploads/')) {
    e.respondWith(
      caches.open(CACHE_NAME).then(cache =>
        fetch(e.request).then(resp => { cache.put(e.request, resp.clone()); return resp; })
          .catch(() => cache.match(e.request))
      )
    );
    return;
  }
  e.respondWith(caches.match(e.request).then(c => c || fetch(e.request)));
});