<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rio Park Operador')</title>
    <link rel="stylesheet" href="{{ asset('css/operator-lite.css') }}">
    @stack('head')
</head>
<body class="lite-body">
    <header class="lite-header">
        <div class="lite-status">
            <span id="lite-online-indicator" class="lite-badge lite-badge-offline">Offline</span>
            <span id="lite-sync-indicator" class="lite-sync-count" style="display:none"></span>
        </div>
        @hasSection('back')
            @yield('back')
        @else
            @auth
                @if(request()->routeIs('operator-lite.home'))
                    <span class="lite-header-title">Rio Park</span>
                @else
                    <a href="{{ route('operator-lite.home') }}" class="lite-back">&larr; Voltar</a>
                @endif
            @endauth
        @endif
    </header>

    @if(session('success'))
        <div class="lite-alert lite-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="lite-alert lite-alert-warning">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="lite-alert lite-alert-error">{{ $errors->first() }}</div>
    @endif

    <main class="lite-main">
        @yield('content')
    </main>

    @isset($bootstrap)
        <script>window.__LITE_BOOTSTRAP__ = @json($bootstrap);</script>
    @endif
    @if(session('lite_bootstrap'))
        <script>window.__LITE_BOOTSTRAP__ = @json(session('lite_bootstrap'));</script>
    @endif
    @if(session('lite_event'))
        <script>window.__LITE_EVENT__ = @json(session('lite_event'));</script>
    @endif

    <script src="{{ asset('js/operator-lite/store.js') }}"></script>
    <script src="{{ asset('js/operator-lite/sync.js') }}"></script>
    @stack('scripts')
</body>
</html>
