const cacheName = "DigitalerSchulhof";
self.addEventListener("install", function (event) {});
self.addEventListener("activate", (event) => {
  // caches.delete(cacheName);
});
self.addEventListener("fetch", function (event) {
  if (event.request.method === "GET" && event.request.url.indexOf("http") === 0) {
    event.respondWith(
      caches.open(cacheName).then(function (cache) {
        return cache.match(event.request).then(function (response) {
          if (response) {
            fetch(event.request).then(function (response) {
              cache.put(event.request, response.clone());
              return response;
            });
          }
          return (
            response ||
            fetch(event.request).then(function (response) {
              cache.put(event.request, response.clone());
              return response;
            })
          );
        });
      })
    );
  }
});
