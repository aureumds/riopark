@extends('operator-lite.layout')

@section('title', 'Início - Rio Park Operador')

@section('content')
<div class="lite-card">
    <p class="lite-label">Estacionamento</p>
    <p class="lite-value">{{ $user->parkingLot?->name ?? '—' }}</p>
    <p class="lite-label">Turno</p>
    <p class="lite-value">{{ $shift ? 'Aberto' : 'Fechado' }}</p>
</div>

<nav class="lite-nav">
    <a href="{{ route('operator-lite.entry') }}" class="lite-btn lite-btn-primary lite-btn-lg">Entrada</a>
    <a href="{{ route('operator-lite.exit') }}" class="lite-btn lite-btn-accent lite-btn-lg">Saída</a>
    <a href="{{ route('operator-lite.yard') }}" class="lite-btn lite-btn-outline lite-btn-lg">
        Veículos no pátio ({{ $activeCount }})
    </a>
    <a href="{{ route('operator-lite.shift') }}" class="lite-btn lite-btn-outline">Gerenciar turno</a>
    <a href="{{ route('operator-lite.closing') }}" class="lite-btn lite-btn-outline">Fechamento local</a>
</nav>

<form method="POST" action="{{ route('operator-lite.logout') }}" class="lite-logout-form">
    @csrf
    <button type="submit" class="lite-btn-link">Sair</button>
</form>
@endsection
