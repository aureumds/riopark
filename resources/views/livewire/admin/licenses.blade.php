<div class="space-y-6">
    <div class="super-card p-5 sm:p-6">
        <p class="text-sm text-slate-500">Mensalidade</p>
        <p class="text-lg font-semibold mt-1">
            @if($company?->paid_until && $company->paid_until->gte(now()->startOfDay()))
                Em dia até {{ $company->paid_until->format('d/m/Y') }}
            @else
                Vencida
            @endif
        </p>
        <p class="text-sm text-slate-500 mt-2">
            O token de 30 dias é liberado pelo Rio Park após o pagamento. Ligue a maquininha na internet para renovar.
        </p>
    </div>

    <div class="super-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold">Máquinas</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($devices as $device)
                @php $lic = $device->licenses->first(); @endphp
                <div class="p-5 flex flex-col sm:flex-row sm:justify-between gap-2">
                    <div>
                        <p class="font-medium">{{ $device->label ?: 'POS' }}</p>
                        <p class="text-sm text-slate-500">{{ $device->parkingLot?->name }}</p>
                    </div>
                    <div class="text-sm text-slate-600">
                        @if($lic && !$lic->revoked_at)
                            Token até {{ $lic->expires_at->format('d/m/Y') }}
                        @else
                            Sem token ativo
                        @endif
                        <p class="text-xs text-slate-400 mt-1">Última conexão: {{ $device->last_seen_at?->format('d/m/Y H:i') ?? 'nunca' }}</p>
                    </div>
                </div>
            @empty
                <p class="p-5 text-sm text-slate-500">Nenhuma máquina conectou ainda.</p>
            @endforelse
        </div>
    </div>
</div>
