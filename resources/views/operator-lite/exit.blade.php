@extends('operator-lite.layout')

@section('title', 'Saída - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Saída de veículo</h1>

<form method="POST" action="{{ route('operator-lite.exit') }}" id="lite-exit-form" class="lite-form" data-lite-offline="exit">
    @csrf
    <input type="hidden" name="local_uuid" id="local_uuid" value="">
    <input type="hidden" name="plate" id="plate" value="">

    <div id="plate-display" class="lite-plate-display">———</div>
    <div id="plate-keyboard" class="lite-keyboard" data-plate-keyboard="plate-display" data-plate-input="plate"></div>

    <div id="exit-preview" class="lite-preview" style="display:none">
        <p class="lite-label">Valor estimado</p>
        <p class="lite-value lite-amount" id="preview-amount">R$ 0,00</p>
        <p class="lite-muted" id="preview-duration"></p>
    </div>

    <button type="button" class="lite-btn lite-btn-outline" id="btn-preview">Consultar valor</button>
    <button type="submit" class="lite-btn lite-btn-accent lite-btn-lg" id="submit-exit">Registrar saída</button>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/operator-lite/plate-keyboard.js') }}"></script>
<script src="{{ asset('js/operator-lite/offline-forms.js') }}"></script>
<script>
(function () {
    document.getElementById('local_uuid').value = RioParkLite.uuid();
    PlateKeyboard.init(document.getElementById('plate-keyboard'));
    LiteOfflineForms.initExit(document.getElementById('lite-exit-form'));

    document.getElementById('btn-preview').addEventListener('click', function () {
        var plate = document.getElementById('plate').value;
        if (plate.length < 4) return;

        if (!navigator.onLine) {
            var session = RioParkLite.findActiveSession(plate);
            if (!session) {
                alert('Veículo não encontrado no pátio');
                return;
            }
            var amount = RioParkLite.calculateAmount(session.entry_at, new Date().toISOString());
            showPreview(amount, session.entry_at);
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '{{ route('operator-lite.exit.preview') }}?plate=' + encodeURIComponent(plate));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                showPreview(data.amount, data.session.entry_at);
            } else {
                alert('Veículo não encontrado no pátio');
            }
        };
        xhr.send();
    });

    function showPreview(amount, entryAt) {
        document.getElementById('exit-preview').style.display = 'block';
        document.getElementById('preview-amount').textContent = 'R$ ' + RioParkLite.formatMoney(amount);
        var mins = Math.floor((new Date() - new Date(entryAt)) / 60000);
        document.getElementById('preview-duration').textContent = 'Tempo: ' + mins + ' min';
    }
})();
</script>
@endpush
