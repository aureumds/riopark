@extends('operator-lite.layout')

@section('title', 'Turno - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Gerenciar turno</h1>

@if($shift)
    <div class="lite-card">
        <p class="lite-label">Status</p>
        <p class="lite-value">Aberto desde {{ $shift->opened_at->format('H:i') }}</p>
        <p class="lite-label">Veículos no turno</p>
        <p class="lite-value">{{ $shift->parking_sessions_count }}</p>
    </div>

    <form method="POST" action="{{ route('operator-lite.shift.close') }}" id="lite-shift-close-form" class="lite-form" data-lite-offline="shift_close">
        @csrf
        <input type="hidden" name="local_uuid" value="{{ $shift->local_uuid }}">
        <div class="lite-field">
            <label for="closing_balance">Saldo de fechamento (R$)</label>
            <input type="number" name="closing_balance" id="closing_balance" step="0.01" min="0" value="0">
        </div>
        <button type="submit" class="lite-btn lite-btn-accent lite-btn-lg">Fechar turno</button>
    </form>
@else
    <p class="lite-muted">Nenhum turno aberto.</p>

    <form method="POST" action="{{ route('operator-lite.shift.open') }}" id="lite-shift-open-form" class="lite-form" data-lite-offline="shift_open">
        @csrf
        <input type="hidden" name="local_uuid" id="shift_local_uuid" value="">
        <div class="lite-field">
            <label for="opening_balance">Saldo inicial (R$)</label>
            <input type="number" name="opening_balance" id="opening_balance" step="0.01" min="0" value="0">
        </div>
        <button type="submit" class="lite-btn lite-btn-primary lite-btn-lg">Abrir turno</button>
    </form>
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/operator-lite/offline-forms.js') }}"></script>
<script>
(function () {
    var openForm = document.getElementById('lite-shift-open-form');
    if (openForm) {
        document.getElementById('shift_local_uuid').value = RioParkLite.uuid();
        LiteOfflineForms.initShiftOpen(openForm);
    }
    var closeForm = document.getElementById('lite-shift-close-form');
    if (closeForm) {
        LiteOfflineForms.initShiftClose(closeForm);
    }
})();
</script>
@endpush
