@extends('operator-lite.layout')

@section('title', 'Entrada - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Entrada de veículo</h1>

<form method="POST" action="{{ route('operator-lite.entry') }}" id="lite-entry-form" class="lite-form" data-lite-offline="entry">
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

    <button type="submit" class="lite-btn lite-btn-primary lite-btn-lg" id="submit-entry">Registrar entrada</button>
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
    });

    plateInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('lite-entry-form').submit();
        }
    });

    LiteOfflineForms.initEntry(document.getElementById('lite-entry-form'));
})();
</script>
@endpush
