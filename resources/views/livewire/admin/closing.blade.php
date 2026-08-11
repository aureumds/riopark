<div>
    <div class="space-y-6">
        {{-- Card de Fechamento do Dia --}}
        <div class="super-card">
            <x-super.card-header 
                title="Fechamento de Hoje" 
                description="{{ now()->format('d/m/Y') }}" 
            />
            <div class="super-card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200">
                        <p class="text-sm font-medium text-blue-700">Saídas registradas</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">{{ $todayStats->total_sessions ?? 0 }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                        <p class="text-sm font-medium text-emerald-700">Receita total</p>
                        <p class="text-3xl font-bold text-emerald-900 mt-1">R$ {{ number_format($todayStats->total_amount ?? 0, 2, ',', '.') }}</p>
                    </div>
                </div>

                @if($todayClosed)
                    <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                        <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-medium text-emerald-900">Fechamento realizado</p>
                            <p class="text-sm text-emerald-700 mt-0.5">O fechamento do dia já foi concluído</p>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.closing.store') }}">
                        @csrf
                        <button type="submit" class="super-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Fechar dia
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Histórico de Fechamentos --}}
        <div class="super-card">
            <x-super.card-header 
                title="Histórico de Fechamentos" 
                description="Últimos 30 fechamentos realizados" 
            />
            <div class="super-card-body">
                @if(count($closings) > 0)
                    <div class="overflow-x-auto -mx-6 sm:mx-0">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-slate-200">
                                    <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Data</th>
                                    <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Saídas</th>
                                    <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                                    <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Fechado por</th>
                                    <th class="px-6 sm:px-0 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($closings as $closing)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 sm:px-0 py-4">
                                            <p class="font-medium text-slate-900">{{ $closing->date->format('d/m/Y') }}</p>
                                            <p class="text-xs text-slate-500">{{ $closing->date->format('l') }}</p>
                                        </td>
                                        <td class="px-6 sm:px-0 py-4">
                                            <span class="super-badge super-badge-blue">{{ $closing->total_sessions }}</span>
                                        </td>
                                        <td class="px-6 sm:px-0 py-4">
                                            <p class="font-semibold text-emerald-700">R$ {{ number_format($closing->total_amount, 2, ',', '.') }}</p>
                                        </td>
                                        <td class="px-6 sm:px-0 py-4 text-slate-600">
                                            {{ $closing->closedByUser->name }}
                                        </td>
                                        <td class="px-6 sm:px-0 py-4 text-right">
                                            <a href="{{ route('admin.closing.pdf', $closing->date->format('Y-m-d')) }}" 
                                               class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-super.empty-state 
                        title="Nenhum fechamento realizado" 
                        description="O histórico de fechamentos aparecerá aqui"
                    >
                        <x-slot:icon>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </x-slot:icon>
                    </x-super.empty-state>
                @endif
            </div>
        </div>
    </div>
</div>
