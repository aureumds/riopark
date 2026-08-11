<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Fechamento diário — {{ $company->name }}</h1>
    <p>Data: {{ $closing->date->format('d/m/Y') }}</p>
    <p>Total: R$ {{ number_format($closing->total_amount, 2, ',', '.') }} · {{ $closing->total_sessions }} saídas</p>

    <h2>Turnos</h2>
    <table>
        <tr><th>Operador</th><th>Estacionamento</th><th>Abertura</th><th>Fechamento</th><th>Sessões</th></tr>
        @foreach($shifts as $shift)
            <tr>
                <td>{{ $shift->user->name }}</td>
                <td>{{ $shift->parkingLot->name }}</td>
                <td>{{ $shift->opened_at->format('d/m/Y H:i') }}</td>
                <td>{{ $shift->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>{{ $shift->parking_sessions_count }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Movimentação</h2>
    <table>
        <tr><th>Placa</th><th>Entrada</th><th>Saída</th><th>Valor</th></tr>
        @foreach($sessions as $session)
            <tr>
                <td>{{ $session->plate }}</td>
                <td>{{ $session->entry_at->format('d/m H:i') }}</td>
                <td>{{ $session->exit_at?->format('d/m H:i') }}</td>
                <td>R$ {{ number_format($session->amount ?? 0, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
