<x-layouts.super title="Planos" subtitle="Configure os planos de cobrança">
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        {{-- Formulário --}}
        <div class="2xl:col-span-5">
            <div class="super-card sticky top-24">
                <x-super.card-header 
                    :title="$editingId ? 'Editar Plano' : 'Novo Plano'" 
                    description="Planos com cobrança por ativação + mensalidade por maquininha" 
                />
                <div class="super-card-body">
                    <form wire:submit="save" class="space-y-5">
                        <div>
                            <label class="super-label">Nome do plano</label>
                            <input wire:model="name" type="text" placeholder="Ex: Profissional" class="super-input" required>
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Identificador (slug)</label>
                            <input wire:model="slug" type="text" placeholder="profissional" class="super-input" required>
                            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Descrição</label>
                            <textarea wire:model="description" rows="3" placeholder="Detalhes sobre o plano..." class="super-input"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="super-label">Cobrança por ativação (R$)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">R$</span>
                                    <input wire:model="activation_fee" type="number" step="0.01" min="0" placeholder="0,00" class="super-input pl-10" required>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Valor único no cadastro</p>
                            </div>
                            <div>
                                <label class="super-label">Valor mensal por máquina (R$)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">R$</span>
                                    <input wire:model="monthly_per_machine" type="number" step="0.01" min="0" placeholder="0,00" class="super-input pl-10" required>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Recorrência mensal</p>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="super-btn-primary flex-1 sm:flex-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $editingId ? 'Atualizar plano' : 'Cadastrar plano' }}
                            </button>
                            @if($editingId)
                                <button type="button" wire:click="resetForm" class="super-btn-secondary">
                                    Cancelar
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Listagem --}}
        <div class="2xl:col-span-7 space-y-6">
            <x-super.section-title 
                title="Planos Disponíveis" 
                :description="count($plans) . ' plano(s) no total'"
            />

            <div class="space-y-4">
                @forelse($plans as $plan)
                    <div class="super-card hover:shadow-md transition-shadow {{ !$plan->active ? 'opacity-60' : '' }}">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-semibold text-xl text-slate-900">{{ $plan->name }}</h3>
                                        @if($plan->active)
                                            <span class="super-badge super-badge-green">Ativo</span>
                                        @else
                                            <span class="super-badge super-badge-gray">Inativo</span>
                                        @endif
                                    </div>

                                    @if($plan->description)
                                        <p class="text-sm text-slate-600 mt-2">{{ $plan->description }}</p>
                                    @endif

                                    <div class="mt-4 grid grid-cols-2 gap-3 max-w-md">
                                        <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-200">
                                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Ativação</p>
                                            <p class="text-2xl font-bold text-blue-900 mt-1">
                                                R$ {{ number_format($plan->activation_fee, 2, ',', '.') }}
                                            </p>
                                        </div>
                                        <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-200">
                                            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Mensal/Máq</p>
                                            <p class="text-2xl font-bold text-amber-900 mt-1">
                                                R$ {{ number_format($plan->monthly_per_machine, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center gap-2 text-sm text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                        {{ $plan->companies_count }} empresa(s) neste plano
                                    </div>
                                </div>

                                <div class="flex gap-2 shrink-0">
                                    <button wire:click="edit({{ $plan->id }})" class="super-btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Editar
                                    </button>
                                    @if($plan->active)
                                        <button wire:click="toggleActive({{ $plan->id }})" class="super-btn-danger">
                                            Desativar
                                        </button>
                                    @else
                                        <button wire:click="toggleActive({{ $plan->id }})" class="super-btn-success">
                                            Ativar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="super-card">
                        <x-super.empty-state 
                            title="Nenhum plano cadastrado" 
                            description="Cadastre o primeiro plano de cobrança"
                        >
                            <x-slot:icon>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </x-slot:icon>
                        </x-super.empty-state>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.super>
