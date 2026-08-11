<div>
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        {{-- Formulário --}}
        <div class="2xl:col-span-5">
            <div class="super-card sticky top-24">
                <x-super.card-header 
                    :title="$editingId ? 'Editar Operador' : 'Novo Operador'" 
                    description="Cadastre operadores para os terminais" 
                />
                <div class="super-card-body">
                    <form wire:submit="save" class="space-y-5">
                        <div>
                            <label class="super-label">Nome completo</label>
                            <input wire:model="name" type="text" placeholder="Ex: João Silva" class="super-input" required>
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">E-mail</label>
                            <input wire:model="email" type="email" placeholder="joao@exemplo.com" class="super-input" required>
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Senha{{ $editingId ? ' (deixe em branco para não alterar)' : '' }}</label>
                            <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres" class="super-input" {{ $editingId ? '' : 'required' }}>
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Estacionamento</label>
                            <select wire:model="parking_lot_id" class="super-input">
                                <option value="">Selecione um estacionamento</option>
                                @foreach($lots as $lot)
                                    <option value="{{ $lot->id }}">{{ $lot->name }}</option>
                                @endforeach
                            </select>
                            @error('parking_lot_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="super-btn-primary flex-1 sm:flex-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $editingId ? 'Atualizar' : 'Cadastrar' }}
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
                title="Operadores Cadastrados" 
                :description="count($operators) . ' operador(es) no total'"
            />

            <div class="space-y-4">
                @forelse($operators as $op)
                    <div class="super-card hover:shadow-md transition-shadow {{ !$op->active ? 'opacity-60' : '' }}">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex items-start gap-4 min-w-0 flex-1">
                                    <div class="w-12 h-12 rounded-full bg-violet-50 flex items-center justify-center text-violet-700 font-semibold shrink-0">
                                        {{ strtoupper(substr($op->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-semibold text-lg text-slate-900">{{ $op->name }}</h3>
                                            @if($op->active)
                                                <span class="super-badge super-badge-green">Ativo</span>
                                            @else
                                                <span class="super-badge super-badge-gray">Inativo</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-500 mt-1">{{ $op->email }}</p>
                                        @if($op->parkingLot)
                                            <div class="mt-2 flex items-center gap-1.5 text-sm text-slate-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                {{ $op->parkingLot->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex gap-2 shrink-0">
                                    <button wire:click="edit({{ $op->id }})" class="super-btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Editar
                                    </button>
                                    @if($op->active)
                                        <button wire:click="toggleActive({{ $op->id }})" class="super-btn-danger">
                                            Desativar
                                        </button>
                                    @else
                                        <button wire:click="toggleActive({{ $op->id }})" class="super-btn-success">
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
                            title="Nenhum operador cadastrado" 
                            description="Cadastre o primeiro operador para os terminais"
                        >
                            <x-slot:icon>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </x-slot:icon>
                        </x-super.empty-state>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
