<div>
    <div class="super-card">
        <x-super.card-header 
            title="Turnos Recentes" 
            description="Histórico de turnos dos operadores" 
        />
        <div class="super-card-body">
            @if(count($shifts) > 0)
                <div class="overflow-x-auto -mx-6 sm:mx-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-slate-200">
                                <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Operador</th>
                                <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Estacionamento</th>
                                <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Abertura</th>
                                <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Fechamento</th>
                                <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Sessões</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($shifts as $shift)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 sm:px-0 py-4">
                                        <p class="font-medium text-slate-900">{{ $shift->user->name }}</p>
                                    </td>
                                    <td class="px-6 sm:px-0 py-4 text-slate-600">{{ $shift->parkingLot->name }}</td>
                                    <td class="px-6 sm:px-0 py-4 text-slate-600">{{ $shift->opened_at->format('d/m H:i') }}</td>
                                    <td class="px-6 sm:px-0 py-4">
                                        @if($shift->closed_at)
                                            <span class="text-slate-600">{{ $shift->closed_at->format('d/m H:i') }}</span>
                                        @else
                                            <span class="super-badge super-badge-green">Aberto</span>
                                        @endif
                                    </td>
                                    <td class="px-6 sm:px-0 py-4 text-right">
                                        <span class="super-badge super-badge-blue">{{ $shift->parking_sessions_count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-super.empty-state 
                    title="Nenhum turno registrado" 
                    description="Os turnos aparecerão aqui quando os operadores iniciarem"
                >
                    <x-slot:icon>
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </x-slot:icon>
                </x-super.empty-state>
            @endif
        </div>
    </div>
</div>
