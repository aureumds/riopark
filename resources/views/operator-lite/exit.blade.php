@extends('operator-lite.layout')

@section('title', 'Saída - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Saída de veículo</h1>

<form method="POST" action="{{ route('operator-lite.exit') }}" id="lite-exit-form" class="lite-form" data-lite-offline="exit">
    @csrf
    <input type="hidden" name="local_uuid" id="local_uuid" value="">

    <div class="lite-field">
        <label for="plate-input">Placa do veículo</label>
        <input type="text"
               id="plate-input"
               name="plate"
               maxlength="8"
               autocomplete="off"
               autocorrect="off"
               autocapitalize="characters"
               spellcheck="false"
               placeholder="Ex: ABC1234"
               class="lite-plate-input"
               autofocus>
    </div>

    <div id="exit-preview" class="lite-preview" style="display:none">
        <p class="lite-label">Placa</p>
        <p class="lite-value lite-plate" id="preview-plate">—</p>
        <p class="lite-label">Entrada</p>
        <p class="lite-value" id="preview-entry">—</p>
        <p class="lite-label">Tempo estacionado</p>
        <p class="lite-value" id="preview-duration">—</p>
        <p class="lite-label">Valor a cobrar</p>
        <p class="lite-value lite-amount" id="preview-amount">R$ 0,00</p>
    </div>

    <button type="button" class="lite-btn lite-btn-outline" id="btn-preview">Consultar valor</button>
    <button type="submit" class="lite-btn lite-btn-accent lite-btn-lg" id="submit-exit">Registrar saída</button>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/operator-lite/offline-forms.js') }}"></script>
<script>
(function () {
    document.getElementById('local_uuid').value = RioParkLite.uuid();

    var plateInput = document.getElementById('plate-input');

    plateInput.addEventListener('input', function () {
        var v = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 7);
        this.value = v;
        document.getElementById('exit-preview').style.display = 'none';
    });

    plateInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btn-preview').click();
        }
    });

    // Pre-fill plate from query string (coming from yard detail).
    var params = new URLSearchParams(window.location.search);
    var pre = params.get('plate');
    if (pre) {
        plateInput.value = pre.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 7);
        setTimeout(function () {
            document.getElementById('btn-preview').click();
        }, 400);
    }

    document.getElementById('btn-preview').addEventListener('click', function () {
        var plate = plateInput.value;
        if (plate.length < 4) return;

        if (!navigator.onLine) {
            var session = RioParkLite.findActiveSession(plate);
            if (!session) {
                alert('Veículo não encontrado no pátio');
                return;
            }
            var amount = RioParkLite.calculateAmount(session.entry_at, new Date().toISOString());
            showPreview(plate, amount, session.entry_at);
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '{{ route('operator-lite.exit.preview') }}?plate=' + encodeURIComponent(plate));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                showPreview(plate, data.amount, data.session.entry_at);
            } else {
                alert('Veículo não encontrado no pátio');
            }
        };
        xhr.send();
    });

    function showPreview(plate, amount, entryAt) {
        var entry = new Date(entryAt);
        var mins = Math.floor((new Date() - entry) / 60000);
        var h = Math.floor(mins / 60);
        var m = mins % 60;
        var durStr = h > 0 ? h + 'h ' + m + 'min' : m + ' min';
        var entryStr = entry.toLocaleString('pt-BR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'});

        document.getElementById('exit-preview').style.display = 'block';
        document.getElementById('preview-plate').textContent = RioParkLite.formatPlate(plate);
        document.getElementById('preview-entry').textContent = entryStr;
        document.getElementById('preview-duration').textContent = durStr;
        document.getElementById('preview-amount').textContent = 'R$ ' + RioParkLite.formatMoney(amount);
    }

    LiteOfflineForms.initExit(document.getElementById('lite-exit-form'));
})();
</script>
@endpush
