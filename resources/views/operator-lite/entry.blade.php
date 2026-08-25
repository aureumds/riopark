@extends('operator-lite.layout')

@section('title', 'Entrada - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Entrada de veículo</h1>

<form method="POST" action="{{ route('operator-lite.entry') }}" id="lite-entry-form" class="lite-form" data-lite-offline="entry">
    @csrf
    <input type="hidden" name="local_uuid" id="local_uuid" value="">
    <input type="hidden" name="plate" id="plate" value="">

    <div id="plate-display" class="lite-plate-display">———</div>
    <div id="plate-keyboard" class="lite-keyboard" data-plate-keyboard="plate-display" data-plate-input="plate"></div>

    <button type="submit" class="lite-btn lite-btn-primary lite-btn-lg" id="submit-entry">Registrar entrada</button>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/operator-lite/plate-keyboard.js') }}"></script>
<script src="{{ asset('js/operator-lite/offline-forms.js') }}"></script>
<script>
(function () {
    document.getElementById('local_uuid').value = RioParkLite.uuid();
    PlateKeyboard.init(document.getElementById('plate-keyboard'));
    LiteOfflineForms.initEntry(document.getElementById('lite-entry-form'));
})();
</script>
@endpush
