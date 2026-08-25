/* Rio Park Operador Lite — sync queue processor (ES5) */
var LiteSync = (function () {
    var syncing = false;

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function processSyncQueue(callback) {
        if (syncing || !navigator.onLine) {
            if (callback) callback({ ok: false });
            return;
        }

        var queue = RioParkLite.getSyncQueue();
        if (!queue.length) {
            if (callback) callback({ ok: true });
            return;
        }

        syncing = true;
        var events = [];
        for (var i = 0; i < queue.length; i++) {
            events.push(queue[i].payload);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/operador-lite/sync');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function () {
            syncing = false;
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                var results = data.results || [];
                var remaining = [];
                var synced = 0;

                for (var j = 0; j < queue.length; j++) {
                    var item = queue[j];
                    var localUuid = item.payload.local_uuid;
                    var matched = false;

                    for (var k = 0; k < results.length; k++) {
                        if (results[k].local_uuid === localUuid && results[k].status === 'synced') {
                            matched = true;
                            synced++;
                            break;
                        }
                    }

                    if (!matched) {
                        remaining.push(item);
                    }
                }

                RioParkLite.setSyncQueue(remaining);
                RioParkLite.updateSyncIndicator();
                if (callback) callback({ ok: true, synced: synced });
            } else {
                if (callback) callback({ ok: false });
            }
        };

        xhr.onerror = function () {
            syncing = false;
            if (callback) callback({ ok: false });
        };

        xhr.send(JSON.stringify({ events: events }));
    }

    function init() {
        window.addEventListener('online', function () {
            RioParkLite.updateSyncIndicator();
            setTimeout(function () {
                processSyncQueue();
            }, 500);
        });

        if (navigator.onLine) {
            setTimeout(function () {
                processSyncQueue();
            }, 1000);
        }

        setInterval(function () {
            if (navigator.onLine && RioParkLite.pendingCount() > 0) {
                processSyncQueue();
            }
        }, 30000);
    }

    init();

    return {
        processSyncQueue: processSyncQueue
    };
})();
