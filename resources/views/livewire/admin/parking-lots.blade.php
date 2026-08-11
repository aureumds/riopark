<div>
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        {{-- Formulário --}}
        <div class="2xl:col-span-5">
            <div class="super-card sticky top-24">
                <x-super.card-header 
                    :title="$editingId ? 'Editar Estacionamento' : 'Novo Estacionamento'" 
                    description="Configure os locais de estacionamento" 
                />
                <div class="super-card-body">
                    <form wire:submit="save" class="space-y-5">
                        <div>
                            <label class="super-label">Nome do estacionamento</label>
                            <input wire:model="name" type="text" placeholder="Ex: Estacionamento Centro" class="super-input" required>
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Endereço</label>
                            <input wire:model="address" type="text" placeholder="Rua, número, bairro" class="super-input">
                            @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="super-label">Capacidade de vagas</label>
                            <input wire:model="capacity" type="number" min="0" placeholder="Ex: 100" class="super-input" required>
                            @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Número máximo de veículos simultâneos</p>
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
                title="Estacionamentos Cadastrados" 
                :description="count($lots) . ' local(is) cadastrado(s)'"
            />

            <div class="space-y-4">
                @forelse($lots as $lot)
                    <div class="super-card hover:shadow-md transition-shadow">
                        <div class="p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex items-start gap-4 min-w-0 flex-1">
                                    <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-700 shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-lg text-slate-900">{{ $lot->name }}</h3>
                                        @if($lot->address)
                                            <p class="text-sm text-slate-500 mt-1">{{ $lot->address }}</p>
                                        @endif
                                        <div class="mt-3 flex items-center gap-2">
                                            <span class="super-badge super-badge-blue">
                                                {{ $lot->capacity }} vagas
                                            </span>
                                            @if($lot->active)
                                                <span class="super-badge super-badge-green">Ativo</span>
                                            @else
                                                <span class="super-badge super-badge-gray">Inativo</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2 shrink-0">
                                    <button wire:click="edit({{ $lot->id }})" class="super-btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Editar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="super-card">
                        <x-super.empty-state 
                            title="Nenhum estacionamento cadastrado" 
                            description="Cadastre o primeiro local de estacionamento"
                        >
                            <x-slot:icon>
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </x-slot:icon>
                        </x-super.empty-state>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
