@extends('operator-lite.layout')

@section('title', 'Detalhe — {{ $session->plate }}')

@php
    $hours   = intdiv($durationMinutes, 60);
    $minutes = $durationMinutes % 60;
    $duration = $hours > 0
        ? $hours . 'h ' . $minutes . 'min'
        : $minutes . ' min';
@endphp

@section('content')
<div class="lite-yard-detail-plate">{{ $session->plate }}</div>

<div class="lite-card">
    <p class="lite-label">Entrada</p>
    <p class="lite-value">{{ $session->entry_at->format('d/m/Y H:i') }}</p>

    <p class="lite-label">Tempo estacionado</p>
    <p class="lite-value">{{ $duration }}</p>

    <p class="lite-label">Valor atual</p>
    <p class="lite-value lite-amount-green">R$ {{ number_format($amount, 2, ',', '.') }}</p>
</div>

<div class="lite-form">
    <a href="{{ route('operator-lite.exit', ['plate' => $session->plate]) }}?plate={{ urlencode($session->plate) }}"
       class="lite-btn lite-btn-accent lite-btn-lg">
        Encerrar / Saída
    </a>
    <a href="{{ route('operator-lite.yard') }}" class="lite-btn lite-btn-outline">
        Voltar ao pátio
    </a>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Live update of amount every 60 seconds while on this page.
    var plate = @json($session->plate_normalized);

    function refreshAmount() {
        if (!navigator.onLine) {
            var s = RioParkLite.findActiveSession(plate);
            if (s) {
                var a = RioParkLite.calculateAmount(s.entry_at, new Date().toISOString());
                document.querySelector('.lite-amount-green').textContent =
                    'R$ ' + RioParkLite.formatMoney(a);
            }
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '{{ route('operator-lite.exit.preview') }}?plate=' + encodeURIComponent(plate));
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var d = JSON.parse(xhr.responseText);
                document.querySelector('.lite-amount-green').textContent =
                    'R$ ' + RioParkLite.formatMoney(d.amount);
            }
        };
        xhr.send();
    }

    setInterval(refreshAmount, 60000);
})();
</script>
@endpush
