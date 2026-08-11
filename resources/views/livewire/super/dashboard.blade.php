<x-layouts.super title="Dashboard" subtitle="Visão geral da plataforma Rio Park">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <x-super.stat-card 
            label="Total de Empresas" 
            :value="$companiesCount"
            icon-bg="bg-blue-50" 
            icon-color="text-blue-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </x-slot:icon>
        </x-super.stat-card>

        <x-super.stat-card 
            label="Empresas Ativas" 
            :value="$activeCompanies"
            icon-bg="bg-emerald-50" 
            icon-color="text-emerald-700"
            :hint="$activeCompanies > 0 ? round(($activeCompanies / max($companiesCount, 1)) * 100, 1) . '% do total' : null">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>

        <x-super.stat-card 
            label="Total de Usuários" 
            :value="$usersCount"
            icon-bg="bg-violet-50" 
            icon-color="text-violet-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>

        <x-super.stat-card 
            label="Entradas Hoje" 
            :value="$sessionsToday"
            icon-bg="bg-amber-50" 
            icon-color="text-amber-700">
            <x-slot:icon>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </x-slot:icon>
        </x-super.stat-card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="super-card">
            <x-super.card-header title="Atividade Recente" description="Últimas operações na plataforma" />
            <div class="super-card-body">
                <div class="space-y-4">
                    @forelse($recentCompanies as $company)
                        <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 transition">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-700 font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($company->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-900">{{ $company->name }}</p>
                                <p class="text-sm text-slate-500 mt-0.5">Cadastrado em {{ $company->created_at->format('d/m/Y') }}</p>
                            </div>
                            <span class="super-badge super-badge-green shrink-0">Ativo</span>
                        </div>
                    @empty
                        <x-super.empty-state title="Nenhuma atividade recente">
                            <x-slot:icon>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </x-slot:icon>
                        </x-super.empty-state>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="super-card">
            <x-super.card-header title="Resumo de Planos" description="Distribuição por planos" />
            <div class="super-card-body">
                <div class="space-y-3">
                    @forelse($planStats as $stat)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                <span class="font-medium text-slate-900">{{ $stat->name }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-slate-500">{{ $stat->companies_count }} empresas</span>
                                <span class="super-badge super-badge-blue">
                                    {{ $stat->companies_count > 0 ? round(($stat->companies_count / $companiesCount) * 100) : 0 }}%
                                </span>
                            </div>
                        </div>
                    @empty
                        <x-super.empty-state title="Sem planos cadastrados">
                            <x-slot:icon>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </x-slot:icon>
                        </x-super.empty-state>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.super>
