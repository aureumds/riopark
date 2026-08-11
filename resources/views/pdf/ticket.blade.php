<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; text-align: center; }
        h1 { font-size: 14px; margin: 0; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>{{ $company->name }}</h1>
    <p>{{ $session->parkingLot->name }}</p>
    <p><strong>{{ $type === 'entry' ? 'ENTRADA' : 'SAÍDA' }}</strong></p>
    <p>Placa: <strong>{{ $session->plate }}</strong></p>
  @if($type === 'entry')
        <p>Entrada: {{ $session->entry_at->format('d/m/Y H:i') }}</p>
    @else
        <p>Entrada: {{ $session->entry_at->format('d/m/Y H:i') }}</p>
        <p>Saída: {{ $session->exit_at?->format('d/m/Y H:i') }}</p>
        <p>Total: R$ {{ number_format($session->amount ?? 0, 2, ',', '.') }}</p>
    @endif
    <p style="margin-top:12px; font-size:9px;">Rio Park</p>
</body>
</html>
