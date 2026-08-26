@extends('operator-lite.layout')

@section('title', 'Login - Rio Park Operador')

@section('content')
<div class="lite-card lite-login">
    <h1 class="lite-title">Rio Park</h1>
    <p class="lite-subtitle">Operador Lite</p>

    <form method="POST" action="{{ route('operator-lite.login') }}" id="lite-login-form" class="lite-form">
        @csrf
        <input type="hidden" name="device_uid" id="device_uid" value="">
        <div class="lite-field">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="lite-field">
            <label for="password">Senha</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="lite-btn lite-btn-primary" id="btn-login">Entrar</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var uid = RioParkLite.getDeviceUid();
    document.getElementById('device_uid').value = uid;

    document.getElementById('lite-login-form').addEventListener('submit', function () {
        var btn = document.getElementById('btn-login');
        if (btn) {
            btn.textContent = 'Aguarde...';
            btn.disabled = true;
        }
    });
})();
</script>
@endpush
