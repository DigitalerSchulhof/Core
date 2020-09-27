const cacheName = "DigitalerSchulhof";
self.addEventListener("install", function (event) {});
self.addEventListener("activate", (event) => {
  // caches.delete(cacheName);
});
self.addEventListener("fetch", function (event) {
  if (
    event.request.method === "GET" &&
    event.request.url.indexOf("http") === 0
  ) {
    event.respondWith(
      caches.open(cacheName).then(async function (cache) {
        const response = await cache.match(event.request);
        if (response) {
          fetch(event.request).then(function (response_1) {
            cache.put(event.request, response_1.clone());
            return response_1;
          });
          return response;
        }
        return fetch(event.request).then(function (response_2) {
          cache.put(event.request, response_2.clone());
          return response_2;
        });
      })
    );
  }
});
