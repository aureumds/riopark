<x-layouts.super title="Empresas" subtitle="Gerencie os clientes da plataforma">
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        {{-- Formulário --}}
        <div class="2xl:col-span-5">
            <div class="super-card sticky top-24">
                <x-super.card-header 
                    :title="$editingId ? 'Editar Empresa' : 'Nova Empresa'" 
                    :description="$editingId ? 'Atualize os dados da empresa' : 'Cadastre uma nova empresa cliente'" 
                />
                <div class="super-card-body">
                    <form wire:submit="save" class="space-y-5">
                        <div>
                            <label class="super-label">Nome da empresa</label>
                            <input wire:model="name" type="text" placeholder="Ex: Estacionamento Centro" class="super-input" required>
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Identificador (slug)</label>
                            <input wire:model="slug" type="text" placeholder="estacionamento-centro" class="super-input" required>
                            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">URL amigável para a empresa</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="super-label">Telefone</label>
                                <input wire:model="phone" type="tel" placeholder="(21) 99999-9999" class="super-input">
                            </div>
                            <div>
                                <label class="super-label">Nome do pagador</label>
                                <input wire:model="payer_name" type="text" placeholder="Responsável financeiro" class="super-input">
                            </div>
                        </div>

                        <div>
                            <label class="super-label">Plano de cobrança</label>
                            <select wire:model="plan_id" class="super-input">
                                <option value="">Sem plano</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">
                                        {{ $plan->name }} — Ativação: R$ {{ number_format($plan->activation_fee, 2, ',', '.') }} | Mensal/máq: R$ {{ number_format($plan->monthly_per_machine, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="super-label">Cor principal</label>
                                <input wire:model="primary_color" type="color" class="w-full h-12 rounded-xl border border-slate-200 cursor-pointer">
                            </div>
                            <div>
                                <label class="super-label">Cor de destaque</label>
                                <input wire:model="accent_color" type="color" class="w-full h-12 rounded-xl border border-slate-200 cursor-pointer">
                            </div>
                        </div>

                        @if(!$editingId)
                            <div class="pt-5 border-t border-slate-200 space-y-4">
                                <p class="text-sm font-semibold text-slate-700">Criar admin da empresa (opcional)</p>
                                <div>
                                    <label class="super-label">E-mail do administrador</label>
                                    <input wire:model="admin_email" type="email" placeholder="admin@empresa.com" class="super-input">
                                </div>
                                <div>
                                    <label class="super-label">Senha do administrador</label>
                                    <input wire:model="admin_password" type="password" placeholder="Mínimo 6 caracteres" class="super-input">
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="super-btn-primary flex-1 sm:flex-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $editingId ? 'Atualizar empresa' : 'Cadastrar empresa' }}
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
                title="Empresas Cadastradas" 
                :description="count($companies) . ' empresa(s) no total'"
            />

            <div class="space-y-4">
                @forelse($companies as $company)
                    <div class="super-card hover:shadow-md transition-shadow {{ !$company->active ? 'opacity-60' : '' }}">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex items-start gap-4 min-w-0 flex-1">
                                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white font-bold text-lg shrink-0">
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-semibold text-lg text-slate-900">{{ $company->name }}</h3>
                                            @if($company->active)
                                                <span class="super-badge super-badge-green">Ativo</span>
                                            @else
                                                <span class="super-badge super-badge-gray">Inativo</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-500 mt-1">{{ $company->slug }}</p>
                                        
                                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                                            @if($company->phone)
                                                <span class="flex items-center gap-1.5 text-slate-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    {{ $company->phone }}
                                                </span>
                                            @endif
                                            @if($company->payer_name)
                                                <span class="flex items-center gap-1.5 text-slate-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    {{ $company->payer_name }}
                                                </span>
                                            @endif
                                            @if($company->plan)
                                                <span class="super-badge super-badge-blue">
                                                    📋 {{ $company->plan->name }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-xs text-slate-400 mt-3">{{ $company->users_count }} usuário(s) · Cadastrado em {{ $company->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>

                                <div class="flex gap-2 shrink-0">
                                    <button wire:click="edit({{ $company->id }})" class="super-btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Editar
                                    </button>
                                    @if($company->active)
                                        <button wire:click="toggleActive({{ $company->id }})" class="super-btn-danger">
                                            Desativar
                                        </button>
                                    @else
                                        <button wire:click="toggleActive({{ $company->id }})" class="super-btn-success">
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
                            title="Nenhuma empresa cadastrada" 
                            description="Cadastre a primeira empresa cliente para começar"
                        >
                            <x-slot:icon>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </x-slot:icon>
                        </x-super.empty-state>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.super>
