/* Rio Park Operador Lite — offline form handlers (ES5) */
var LiteOfflineForms = (function () {
    function blockIfLicenseExpired() {
        if (!RioParkLite.isLicenseValid()) {
            alert('Licença vencida. Conecte à internet para renovar em Licença.');
            return true;
        }
        return false;
    }

    function handleOfflineSubmit(form, buildPayload, onSuccess) {
        form.addEventListener('submit', function (e) {
            if (navigator.onLine) return;

            e.preventDefault();
            if (blockIfLicenseExpired()) return;

            var payload = buildPayload();
            if (!payload) return;

            RioParkLite.queueEvent(payload.type, payload);
            RioParkLite.handleServerEvent({
                type: payload.type,
                session: payload.session,
                shift: payload.shift,
                amount: payload.amount,
                print_ticket: payload.print_ticket
            });

            RioParkLite.updateSyncIndicator();
            alert(onSuccess || 'Registrado offline. Será sincronizado quando houver internet.');
            form.reset();
        });
    }

    function initEntry(form) {
        handleOfflineSubmit(form, function () {
            var plate = document.getElementById('plate').value;
            if (plate.length < 4) {
                alert('Informe a placa completa.');
                return null;
            }

            if (RioParkLite.findActiveSession(plate)) {
                alert('Placa já está no pátio.');
                return null;
            }

            var localUuid = document.getElementById('local_uuid').value || RioParkLite.uuid();
            var cache = RioParkLite.getCache();
            var now = new Date().toISOString();
            var shift = RioParkLite.getCurrentShift();

            return {
                type: 'session_entry',
                local_uuid: localUuid,
                plate: plate,
                plate_normalized: RioParkLite.normalizePlate(plate),
                parking_lot_id: cache.parking_lot ? cache.parking_lot.id : null,
                shift_local_uuid: shift ? shift.local_uuid : null,
                entry_at: now,
                session: {
                    local_uuid: localUuid,
                    plate: RioParkLite.formatPlate(plate),
                    plate_normalized: RioParkLite.normalizePlate(plate),
                    entry_at: now,
                    status: 'active'
                },
                print_ticket: cache.company ? cache.company.print_ticket_on_entry : false
            };
        }, 'Entrada registrada offline.');
    }

    function initExit(form) {
        handleOfflineSubmit(form, function () {
            var plate = document.getElementById('plate').value;
            if (plate.length < 4) {
                alert('Informe a placa completa.');
                return null;
            }

            var session = RioParkLite.findActiveSession(plate);
            if (!session) {
                alert('Veículo não encontrado no pátio.');
                return null;
            }

            var localUuid = session.local_uuid || document.getElementById('local_uuid').value || RioParkLite.uuid();
            var now = new Date().toISOString();
            var amount = RioParkLite.calculateAmount(session.entry_at, now);
            var cache = RioParkLite.getCache();

            return {
                type: 'session_exit',
                local_uuid: localUuid,
                plate: plate,
                exit_at: now,
                amount: amount,
                payment_method: 'cash',
                session: {
                    local_uuid: localUuid,
                    plate: session.plate,
                    amount: amount,
                    exit_at: now,
                    status: 'completed'
                },
                print_ticket: cache.company ? cache.company.print_ticket_on_exit : false
            };
        }, 'Saída registrada offline.');
    }

    function initShiftOpen(form) {
        handleOfflineSubmit(form, function () {
            var localUuidInput = form.querySelector('[name="local_uuid"]');
            var localUuid = localUuidInput ? localUuidInput.value : RioParkLite.uuid();
            var balanceInput = form.querySelector('[name="opening_balance"]');
            var balance = balanceInput ? parseFloat(balanceInput.value) || 0 : 0;
            var cache = RioParkLite.getCache();
            var now = new Date().toISOString();

            return {
                type: 'shift_open',
                local_uuid: localUuid,
                parking_lot_id: cache.parking_lot ? cache.parking_lot.id : null,
                opened_at: now,
                opening_balance: balance,
                shift: {
                    local_uuid: localUuid,
                    opened_at: now,
                    opening_balance: balance
                }
            };
        }, 'Turno aberto offline.');
    }

    function initShiftClose(form) {
        handleOfflineSubmit(form, function () {
            var localUuidInput = form.querySelector('[name="local_uuid"]');
            var localUuid = localUuidInput ? localUuidInput.value : '';
            var balanceInput = form.querySelector('[name="closing_balance"]');
            var balance = balanceInput ? parseFloat(balanceInput.value) || 0 : 0;
            var now = new Date().toISOString();

            return {
                type: 'shift_close',
                local_uuid: localUuid,
                closed_at: now,
                closing_balance: balance,
                shift: {
                    local_uuid: localUuid,
                    closed_at: now,
                    closing_balance: balance
                }
            };
        }, 'Turno fechado offline.');
    }

    return {
        initEntry: initEntry,
        initExit: initExit,
        initShiftOpen: initShiftOpen,
        initShiftClose: initShiftClose
    };
})();
