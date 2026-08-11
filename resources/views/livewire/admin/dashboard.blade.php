<div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <x-super.stat-card 
            label="No pátio" 
            :value="$activeInLot"
            icon-bg="bg-blue-50" 
            icon-color="text-blue-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>

        <x-super.stat-card 
            label="Receita hoje" 
            :value="'R$ ' . number_format($revenueToday, 2, ',', '.')"
            icon-bg="bg-emerald-50" 
            icon-color="text-emerald-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>

        <x-super.stat-card 
            label="Turnos abertos" 
            :value="$openShifts"
            icon-bg="bg-violet-50" 
            icon-color="text-violet-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>

        <x-super.stat-card 
            label="Último fechamento" 
            :value="$lastClosing?->date?->format('d/m/Y') ?? '—'"
            icon-bg="bg-amber-50" 
            icon-color="text-amber-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="super-card">
            <x-super.card-header title="Sessões Ativas" description="Veículos no estacionamento" />
            <div class="super-card-body">
                @if($activeSessions->count() > 0)
                    <div class="space-y-3">
                        @foreach($activeSessions as $session)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $session->plate }}</p>
                                    <p class="text-sm text-slate-500">{{ $session->entry_at->format('H:i') }} · {{ $session->entry_at->diffForHumans() }}</p>
                                </div>
                                <span class="super-badge super-badge-blue">Ativo</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-super.empty-state title="Nenhum veículo no pátio">
                        <x-slot:icon>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        </x-slot:icon>
                    </x-super.empty-state>
                @endif
            </div>
        </div>

        <div class="super-card">
            <x-super.card-header title="Turnos Ativos" description="Operadores em serviço" />
            <div class="super-card-body">
                @if($activeShifts->count() > 0)
                    <div class="space-y-3">
                        @foreach($activeShifts as $shift)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $shift->user->name }}</p>
                                    <p class="text-sm text-slate-500">Início: {{ $shift->opened_at->format('H:i') }}</p>
                                </div>
                                <span class="super-badge super-badge-green">Aberto</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-super.empty-state title="Nenhum turno ativo">
                        <x-slot:icon>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </x-slot:icon>
                    </x-super.empty-state>
                @endif
            </div>
        </div>
    </div>
</div>
