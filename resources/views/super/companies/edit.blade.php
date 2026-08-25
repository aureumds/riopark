<x-layouts.super :title="'Editar empresa'" :subtitle="$company->name">
    <div class="mb-6">
        <a href="{{ route('super.companies') }}" class="text-sm text-slate-500 hover:text-slate-800">← Voltar para empresas</a>
    </div>

    <div class="max-w-2xl">
        <div class="super-card">
            <x-super.card-header title="Editar empresa" :description="'Atualize os dados de '.$company->name" />
            <div class="super-card-body">
                <form method="POST" action="{{ route('super.companies.update', $company) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="super-label">Nome da empresa</label>
                        <input name="name" type="text" class="super-input" required value="{{ old('name', $company->name) }}">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="super-label">Identificador (slug)</label>
                        <input name="slug" type="text" class="super-input" required value="{{ old('slug', $company->slug) }}">
                        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="super-label">Telefone</label>
                            <input name="phone" type="tel" class="super-input" value="{{ old('phone', $company->phone) }}">
                        </div>
                        <div>
                            <label class="super-label">Nome do pagador</label>
                            <input name="payer_name" type="text" class="super-input" value="{{ old('payer_name', $company->payer_name) }}">
                        </div>
                    </div>

                    <div>
                        <label class="super-label">Plano de cobrança</label>
                        <select name="plan_id" class="super-input">
                            <option value="">Sem plano</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id', $company->plan_id) == $plan->id)>
                                    {{ $plan->name }} — Ativação: R$ {{ number_format($plan->activation_fee, 2, ',', '.') }} | Mensal/máq: R$ {{ number_format($plan->monthly_per_machine, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="super-label">Cor principal</label>
                            <input name="primary_color" type="color" class="w-full h-12 rounded-xl border border-slate-200 cursor-pointer" value="{{ old('primary_color', $company->primary_color ?: '#1e40af') }}">
                        </div>
                        <div>
                            <label class="super-label">Cor de destaque</label>
                            <input name="accent_color" type="color" class="w-full h-12 rounded-xl border border-slate-200 cursor-pointer" value="{{ old('accent_color', $company->accent_color ?: '#f59e0b') }}">
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate-200 space-y-4">
                        <p class="text-sm font-semibold text-slate-700">Administrador da empresa</p>
                        <div>
                            <label class="super-label">E-mail do administrador</label>
                            <input name="admin_email" type="email" class="super-input" value="{{ old('admin_email', $admin?->email) }}">
                            @error('admin_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="super-label">Nova senha (em branco = não alterar)</label>
                            <input name="admin_password" type="password" class="super-input" autocomplete="new-password">
                            @error('admin_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="super-btn-primary">Salvar alterações</button>
                        <a href="{{ route('super.companies') }}" class="super-btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.super>
