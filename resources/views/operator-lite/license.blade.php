@extends('operator-lite.layout')

@section('title', 'Licença - Rio Park Operador')

@section('content')
<h1 class="lite-page-title">Renovar licença</h1>

@if($expiresAt)
    <p class="lite-muted">Validade atual: {{ \Carbon\Carbon::parse($expiresAt)->format('d/m/Y H:i') }}</p>
@else
    <p class="lite-muted">Nenhuma licença ativa nesta sessão.</p>
@endif

<p class="lite-muted">É necessário conexão com a internet para renovar.</p>

<form method="POST" action="{{ route('operator-lite.license') }}" class="lite-form" id="lite-license-form">
    @csrf
    <input type="hidden" name="device_uid" id="device_uid" value="">
    <div class="lite-field">
        <label for="password">Confirme sua senha</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit" class="lite-btn lite-btn-primary lite-btn-lg">Renovar licença</button>
</form>

@if($expiresAt)
    <a href="{{ route('operator-lite.home') }}" class="lite-btn lite-btn-outline lite-mt">Continuar (dentro da carência)</a>
@endif
@endsection

@push('scripts')
<script>
(function () {
    document.getElementById('device_uid').value = RioParkLite.getDeviceUid();
})();
</script>
@endpush
