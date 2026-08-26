/* Rio Park Operador Lite — localStorage store (ES5) */
var RioParkLite = (function () {
    var PREFIX = 'riopark_lite_';
    var KEYS = {
        deviceUid: PREFIX + 'device_uid',
        cache: PREFIX + 'cache',
        syncQueue: PREFIX + 'sync_queue',
        completedToday: PREFIX + 'completed_today'
    };

    function uuid() {
        var d = new Date().getTime();
        if (typeof performance !== 'undefined' && performance.now) {
            d += performance.now();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (d + Math.random() * 16) % 16 | 0;
            d = Math.floor(d / 16);
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    function getDeviceUid() {
        var uid = localStorage.getItem(KEYS.deviceUid);
        if (!uid) {
            uid = uuid();
            localStorage.setItem(KEYS.deviceUid, uid);
        }
        return uid;
    }

    function getCache() {
        try {
            return JSON.parse(localStorage.getItem(KEYS.cache) || '{}') || {};
        } catch (e) {
            return {};
        }
    }

    function setCache(data) {
        var existing = getCache();
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                existing[key] = data[key];
            }
        }
        localStorage.setItem(KEYS.cache, JSON.stringify(existing));
    }

    function getSyncQueue() {
        try {
            return JSON.parse(localStorage.getItem(KEYS.syncQueue) || '[]') || [];
        } catch (e) {
            return [];
        }
    }

    function setSyncQueue(queue) {
        localStorage.setItem(KEYS.syncQueue, JSON.stringify(queue));
    }

    function queueEvent(type, payload) {
        var event = { type: type };
        for (var key in payload) {
            if (payload.hasOwnProperty(key)) {
                event[key] = payload[key];
            }
        }
        var queue = getSyncQueue();
        queue.push({
            type: type,
            payload: event,
            created_at: Date.now()
        });
        setSyncQueue(queue);
        updateSyncIndicator();
    }

    function pendingCount() {
        return getSyncQueue().length;
    }

    function normalizePlate(plate) {
        return (plate || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
    }

    function formatPlate(plate) {
        var n = normalizePlate(plate);
        if (n.length === 7) {
            return n.slice(0, 3) + '-' + n.slice(3);
        }
        return n;
    }

    function formatMoney(value) {
        var n = parseFloat(value) || 0;
        return n.toFixed(2).replace('.', ',');
    }

    function calculateAmount(entryAt, exitAt) {
        var cache = getCache();
        var tariff = cache.tariff;
        if (!tariff) return 0;

        var entry = new Date(entryAt);
        var exit = exitAt ? new Date(exitAt) : new Date();
        var minutes = Math.floor((exit - entry) / 60000);

        if (minutes <= tariff.grace_minutes) return 0;

        var billable = minutes - tariff.grace_minutes;
        var fractionMinutes = Math.max(1, tariff.fraction_minutes || 30);

        if (parseFloat(tariff.fraction_price) > 0) {
            var fractions = Math.ceil(billable / fractionMinutes);
            return Math.round(fractions * parseFloat(tariff.fraction_price) * 100) / 100;
        }

        var hours = Math.ceil(billable / 60);
        return Math.round(hours * parseFloat(tariff.price_per_hour) * 100) / 100;
    }

    function isLicenseValid() {
        var cache = getCache();
        var license = cache.license;
        if (!license || !license.expires_at) return false;

        var graceDays = license.grace_days || 3;
        var expires = new Date(license.expires_at);
        expires.setDate(expires.getDate() + graceDays);
        return new Date() <= expires;
    }

    function getActiveSessions() {
        var cache = getCache();
        return cache.active_sessions || [];
    }

    function setActiveSessions(sessions) {
        setCache({ active_sessions: sessions });
    }

    function findActiveSession(plate) {
        var normalized = normalizePlate(plate);
        var sessions = getActiveSessions();
        for (var i = 0; i < sessions.length; i++) {
            if (normalizePlate(sessions[i].plate_normalized || sessions[i].plate) === normalized) {
                return sessions[i];
            }
        }
        return null;
    }

    function addActiveSession(session) {
        var sessions = getActiveSessions();
        sessions.unshift(session);
        setActiveSessions(sessions);
    }

    function removeActiveSession(plate) {
        var normalized = normalizePlate(plate);
        var sessions = getActiveSessions();
        var filtered = [];
        for (var i = 0; i < sessions.length; i++) {
            if (normalizePlate(sessions[i].plate_normalized || sessions[i].plate) !== normalized) {
                filtered.push(sessions[i]);
            }
        }
        setActiveSessions(filtered);
    }

    function mergeActiveSessions(serverSessions) {
        if (!serverSessions || !serverSessions.length) return;
        var map = {};
        var existing = getActiveSessions();
        var i;

        for (i = 0; i < existing.length; i++) {
            var key = existing[i].local_uuid || existing[i].plate_normalized;
            map[key] = existing[i];
        }
        for (i = 0; i < serverSessions.length; i++) {
            var s = serverSessions[i];
            map[s.local_uuid || s.plate_normalized] = s;
        }

        var merged = [];
        for (var k in map) {
            if (map.hasOwnProperty(k)) merged.push(map[k]);
        }
        setActiveSessions(merged);
    }

    function getCurrentShift() {
        return getCache().current_shift || null;
    }

    function setCurrentShift(shift) {
        setCache({ current_shift: shift });
    }

    function todayKey() {
        var d = new Date();
        return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
    }

    function getCompletedToday() {
        try {
            var data = JSON.parse(localStorage.getItem(KEYS.completedToday) || '{}') || {};
            var key = todayKey();
            return data[key] || { exits: 0, total: 0 };
        } catch (e) {
            return { exits: 0, total: 0 };
        }
    }

    function addCompletedExit(amount) {
        var data = {};
        try {
            data = JSON.parse(localStorage.getItem(KEYS.completedToday) || '{}') || {};
        } catch (e) {
            data = {};
        }
        var key = todayKey();
        var day = data[key] || { exits: 0, total: 0 };
        day.exits += 1;
        day.total = Math.round((day.total + (parseFloat(amount) || 0)) * 100) / 100;
        data[key] = day;
        localStorage.setItem(KEYS.completedToday, JSON.stringify(data));
    }

    function closingSummary() {
        var completed = getCompletedToday();
        return {
            exits: completed.exits,
            total: completed.total,
            inYard: getActiveSessions().length
        };
    }

    function printTicket(text) {
        try {
            if (window.RioParkBridge && window.RioParkBridge.printTicket) {
                window.RioParkBridge.printTicket(String(text || ''));
            }
        } catch (e) {
            // Never let print failures break entry/exit flow on POS WebView.
        }
    }

    function handleServerEvent(event) {
        if (!event || !event.type) return;

        if (event.type === 'session_entry' && event.session) {
            addActiveSession(event.session);
            if (event.print_ticket) {
                printTicket([
                    getCache().company ? getCache().company.name : 'Rio Park',
                    'ENTRADA',
                    event.session.plate,
                    'Entrada: ' + new Date(event.session.entry_at).toLocaleString('pt-BR')
                ].join('\n'));
            }
        }

        if (event.type === 'session_exit' && event.session) {
            removeActiveSession(event.session.plate);
            addCompletedExit(event.amount || event.session.amount || 0);
            if (event.print_ticket) {
                printTicket([
                    getCache().company ? getCache().company.name : 'Rio Park',
                    'SAIDA',
                    event.session.plate,
                    'Valor: R$ ' + formatMoney(event.amount || event.session.amount || 0)
                ].join('\n'));
            }
        }

        if (event.type === 'shift_open' && event.shift) {
            setCurrentShift(event.shift);
        }

        if (event.type === 'shift_close') {
            setCurrentShift(null);
        }
    }

    function init() {
        if (window.__LITE_BOOTSTRAP__) {
            setCache(window.__LITE_BOOTSTRAP__);
        }
        if (window.__LITE_EVENT__) {
            handleServerEvent(window.__LITE_EVENT__);
        }
        updateOnlineIndicator();
        updateSyncIndicator();

        window.addEventListener('online', function () {
            updateOnlineIndicator();
        });
        window.addEventListener('offline', function () {
            updateOnlineIndicator();
        });
    }

    function updateOnlineIndicator() {
        var el = document.getElementById('lite-online-indicator');
        if (!el) return;
        if (navigator.onLine) {
            el.textContent = 'Online';
            el.className = 'lite-badge lite-badge-online';
        } else {
            el.textContent = 'Offline';
            el.className = 'lite-badge lite-badge-offline';
        }
    }

    function updateSyncIndicator() {
        var el = document.getElementById('lite-sync-indicator');
        if (!el) return;
        var count = pendingCount();
        if (count > 0) {
            el.style.display = 'inline';
            el.textContent = count + ' pendente(s)';
        } else {
            el.style.display = 'none';
        }
    }

    init();

    return {
        uuid: uuid,
        getDeviceUid: getDeviceUid,
        getCache: getCache,
        setCache: setCache,
        queueEvent: queueEvent,
        pendingCount: pendingCount,
        getSyncQueue: getSyncQueue,
        setSyncQueue: setSyncQueue,
        normalizePlate: normalizePlate,
        formatPlate: formatPlate,
        formatMoney: formatMoney,
        calculateAmount: calculateAmount,
        isLicenseValid: isLicenseValid,
        getActiveSessions: getActiveSessions,
        findActiveSession: findActiveSession,
        addActiveSession: addActiveSession,
        removeActiveSession: removeActiveSession,
        mergeActiveSessions: mergeActiveSessions,
        getCurrentShift: getCurrentShift,
        setCurrentShift: setCurrentShift,
        addCompletedExit: addCompletedExit,
        closingSummary: closingSummary,
        printTicket: printTicket,
        handleServerEvent: handleServerEvent,
        updateSyncIndicator: updateSyncIndicator
    };
})();
