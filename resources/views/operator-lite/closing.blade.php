@extends('operator-lite.layout')

@section('title', 'Fechamento - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Fechamento local</h1>
<p class="lite-muted">Resumo desta máquina (hoje). O fechamento oficial continua no painel web após o sync.</p>

<div class="lite-card" id="closing-summary">
    <p class="lite-label">Saídas hoje</p>
    <p class="lite-value lite-big" id="summary-exits">0</p>
    <p class="lite-label">Total recebido</p>
    <p class="lite-value lite-big" id="summary-total">R$ 0,00</p>
    <p class="lite-label">Ainda no pátio</p>
    <p class="lite-value" id="summary-yard">0</p>
</div>

<button type="button" class="lite-btn lite-btn-primary lite-btn-lg" id="btn-print-closing">Imprimir resumo</button>
@endsection

@push('scripts')
<script>
(function () {
    var summary = RioParkLite.closingSummary();
    document.getElementById('summary-exits').textContent = summary.exits;
    document.getElementById('summary-total').textContent = 'R$ ' + RioParkLite.formatMoney(summary.total);
    document.getElementById('summary-yard').textContent = summary.inYard;

    document.getElementById('btn-print-closing').addEventListener('click', function () {
        var company = RioParkLite.getCache().company;
        var lot = RioParkLite.getCache().parking_lot;
        var text = [
            company ? company.name : 'Rio Park',
            lot ? lot.name : '',
            'FECHAMENTO LOCAL',
            'Data: ' + new Date().toLocaleDateString('pt-BR'),
            'Saidas: ' + summary.exits,
            'Total: R$ ' + RioParkLite.formatMoney(summary.total),
            'Patio: ' + summary.inYard
        ].join('\n');
        if (window.RioParkBridge && window.RioParkBridge.printTicket) {
            window.RioParkBridge.printTicket(text);
        } else {
            alert(text);
        }
    });
})();
</script>
@endpush
