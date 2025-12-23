const CACHE_NAME = 'minimarket-v2';
const CACHE_VERSION = '2.0.0';

// Files to cache for offline use
const urlsToCache = [
  './',
  './index.php',
  './assets/css/style.css',
  './assets/css/responsive.css',
  './assets/js/main.js',
  './assets/js/chart.min.js',
  './manifest.json',
  './assets/css/bootstrap.min.css',
  './assets/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install Event - Cache files
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.log('[Service Worker] Cache failed:', error);
      })
  );
  self.skipWaiting();
});

// Activate Event - Clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch Event - Network First, then Cache
self.addEventListener('fetch', (event) => {
  // Skip caching for non-GET requests (POST, PUT, DELETE, etc.)
  if (event.request.method !== 'GET') {
    event.respondWith(fetch(event.request));
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Clone the response
        const responseToCache = response.clone();
        
        // Cache the fetched response for future use (only for GET requests)
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        
        return response;
      })
      .catch(() => {
        // Network failed, try cache
        return caches.match(event.request).then((response) => {
          if (response) {
            return response;
          }
          
          // If not in cache, return offline page
          if (event.request.mode === 'navigate') {
            return caches.match('/sera/offline.html');
          }
        });
      })
  );
});

// Background Sync (for offline transactions)
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Background sync:', event.tag);
  
  if (event.tag === 'sync-transactions') {
    event.waitUntil(syncTransactions());
  }
});

// Push Notification
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push received');
  
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'Minimarket Notification';
  const options = {
    body: data.body || 'You have a new notification',
    icon: '/sera/assets/images/icon-192x192.png',
    badge: '/sera/assets/images/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/sera/'
    }
  };
  
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Notification Click
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked');
  event.notification.close();
  
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});

// Helper function to sync transactions
async function syncTransactions() {
  try {
    // Get pending transactions from IndexedDB
    const db = await openDB();
    const tx = db.transaction('pending-transactions', 'readonly');
    const store = tx.objectStore('pending-transactions');
    const pendingTx = await store.getAll();
    
    // Send each pending transaction to server
    for (const transaction of pendingTx) {
      await fetch('/sera/api/sync-transaction.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(transaction)
      });
      
      // Remove from pending after successful sync
      const deleteTx = db.transaction('pending-transactions', 'readwrite');
      const deleteStore = deleteTx.objectStore('pending-transactions');
      await deleteStore.delete(transaction.id);
    }
    
    console.log('[Service Worker] Transactions synced');
  } catch (error) {
    console.error('[Service Worker] Sync failed:', error);
  }
}

// IndexedDB helper
function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('minimarket-db', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
    
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('pending-transactions')) {
        db.createObjectStore('pending-transactions', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}
