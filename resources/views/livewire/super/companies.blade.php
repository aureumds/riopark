<div>
    <div class="grid grid-cols-1 2xl:grid-cols-12 gap-6">
        <div class="2xl:col-span-5">
            <div class="super-card sticky top-24">
                <x-super.card-header
                    :title="'Nova empresa'"
                    :description="'Cadastre uma nova empresa cliente'"
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
                            @error('plan_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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

                        <div class="pt-5 border-t border-slate-200 space-y-4">
                            <p class="text-sm font-semibold text-slate-700">Criar admin da empresa (opcional)</p>
                            <div>
                                <label class="super-label">E-mail do administrador</label>
                                <input wire:model="admin_email" type="email" placeholder="admin@empresa.com" class="super-input">
                                @error('admin_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="super-label">Senha</label>
                                <input wire:model="admin_password" type="password" placeholder="Mínimo 6 caracteres" class="super-input">
                                @error('admin_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="super-btn-primary flex-1 sm:flex-none">
                                Cadastrar empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="2xl:col-span-7 space-y-6">
            <x-super.section-title
                title="Empresas cadastradas"
                :description="count($companies) . ' empresa(s)'"
            />

            <div class="space-y-4">
                @forelse($companies as $company)
                    <div wire:key="company-{{ $company->id }}" class="super-card hover:shadow-md transition-shadow {{ !$company->active ? 'opacity-60' : '' }}">
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
                                                <span class="text-slate-600">{{ $company->phone }}</span>
                                            @endif
                                            @if($company->payer_name)
                                                <span class="text-slate-600">{{ $company->payer_name }}</span>
                                            @endif
                                            @if($company->plan)
                                                <span class="super-badge super-badge-blue">{{ $company->plan->name }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-400 mt-3">
                                            {{ $company->users_count }} usuário(s)
                                            · {{ $company->paid_until ? 'Pago até '.$company->paid_until->format('d/m/Y') : 'Sem mensalidade' }}
                                            · {{ $company->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <a href="{{ route('super.companies.edit', $company) }}" class="super-btn-ghost">Editar</a>
                                    @if($company->active)
                                        <button type="button" wire:click="toggleActive({{ $company->id }})" class="super-btn-danger">Desativar</button>
                                    @else
                                        <button type="button" wire:click="toggleActive({{ $company->id }})" class="super-btn-success">Ativar</button>
                                    @endif
                                    <button type="button" wire:click="delete({{ $company->id }})" wire:confirm="Excluir esta empresa e todos os dados vinculados?" class="super-btn-danger">Excluir</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="super-card">
                        <x-super.empty-state
                            title="Nenhuma empresa cadastrada"
                            description="Cadastre a primeira empresa cliente para começar"
                        />
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
