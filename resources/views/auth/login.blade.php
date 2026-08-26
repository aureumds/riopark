<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Rio Park</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm card bg-white border border-gray-200 rounded-xl p-8 shadow-sm">
        <h1 class="text-2xl font-semibold text-center mb-2" style="color: #1e40af">Rio Park</h1>
        <p class="text-center text-gray-500 text-sm mb-6">Gestão de estacionamentos</p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input w-full border rounded-lg px-3 py-2" autofocus>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input type="password" name="password" required class="input w-full border rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="w-full py-3 rounded-lg text-white font-medium" style="background: #1e40af">
                Entrar
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-6">
            É operador na maquininha?
            <a href="{{ url('/operador-lite/login') }}" class="text-blue-700 font-medium underline">
                Abrir Operador Lite
            </a>
        </p>
    </div>
</body>
</html>
