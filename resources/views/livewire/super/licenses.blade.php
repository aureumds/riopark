    <div class="space-y-6">
        @forelse($companies as $company)
            @php
                $inDay = $company->paid_until && $company->paid_until->gte(now()->startOfDay());
            @endphp
            <div class="super-card {{ !$company->active ? 'opacity-60' : '' }}">
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold text-lg text-slate-900">{{ $company->name }}</h3>
                                @if($inDay)
                                    <span class="super-badge super-badge-green">Em dia</span>
                                @else
                                    <span class="super-badge super-badge-gray">Inadimplente</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                Status: {{ $company->subscription_status }}
                                · Válido até:
                                {{ $company->paid_until?->format('d/m/Y') ?? '—' }}
                                @if($company->plan)
                                    · {{ $company->plan->name }}
                                @endif
                            </p>
                        </div>
                        <button type="button" wire:click="markPaid({{ $company->id }})" class="super-btn-primary shrink-0">
                            Marcar pago (+30 dias)
                        </button>
                    </div>

                    @if($company->devices->isEmpty())
                        <p class="text-sm text-slate-500">Nenhuma máquina ativada ainda. O POS cria o cadastro na primeira conexão.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-slate-500 border-b">
                                        <th class="py-2 pr-3">Máquina</th>
                                        <th class="py-2 pr-3">Pátio</th>
                                        <th class="py-2 pr-3">Token até</th>
                                        <th class="py-2 pr-3">Visto</th>
                                        <th class="py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($company->devices as $device)
                                        @php $lic = $device->licenses->first(); @endphp
                                        <tr class="border-b border-slate-100">
                                            <td class="py-3 pr-3">
                                                <p class="font-medium">{{ $device->label ?: $device->device_uid }}</p>
                                                <p class="text-xs text-slate-400 font-mono">{{ \Illuminate\Support\Str::limit($device->device_uid, 16) }}</p>
                                                @unless($device->active)
                                                    <span class="super-badge super-badge-gray mt-1">Inativa</span>
                                                @endunless
                                            </td>
                                            <td class="py-3 pr-3">{{ $device->parkingLot?->name }}</td>
                                            <td class="py-3 pr-3">
                                                @if($lic && !$lic->revoked_at)
                                                    {{ $lic->expires_at->format('d/m/Y H:i') }}
                                                @elseif($lic?->revoked_at)
                                                    Revogada
                                                @else
                                                    Sem token
                                                @endif
                                            </td>
                                            <td class="py-3 pr-3">{{ $device->last_seen_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td class="py-3 flex flex-wrap gap-2 justify-end">
                                                <button type="button" wire:click="issue({{ $device->id }})" class="super-btn-ghost text-xs">Liberar token</button>
                                                <button type="button" wire:click="revoke({{ $device->id }})" class="super-btn-ghost text-xs">Revogar</button>
                                                <button type="button" wire:click="toggleDevice({{ $device->id }})" class="super-btn-ghost text-xs">
                                                    {{ $device->active ? 'Desativar' : 'Ativar' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="super-card">
                <x-super.empty-state title="Nenhuma empresa" description="Cadastre uma empresa primeiro" />
            </div>
        @endforelse
    </div>
