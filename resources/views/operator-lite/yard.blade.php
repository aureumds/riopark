@extends('operator-lite.layout')

@section('title', 'Pátio - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Veículos no pátio</h1>

@if($sessions->isEmpty())
    <p class="lite-muted lite-center">Nenhum veículo no pátio.</p>
@else
    <ul class="lite-list">
        @foreach($sessions as $session)
            <li class="lite-list-item">
                <span class="lite-plate">{{ $session->plate }}</span>
                <span class="lite-muted">{{ $session->entry_at->format('H:i') }}</span>
            </li>
        @endforeach
    </ul>
@endif
@endsection

@push('scripts')
<script>
(function () {
    if (navigator.onLine && window.__LITE_BOOTSTRAP__) {
        RioParkLite.mergeActiveSessions(@json($sessions->values()->all()));
    }
})();
</script>
@endpush
