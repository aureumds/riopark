<div>
    <div class="max-w-3xl">
        <div class="super-card">
            <x-super.card-header 
                title="Configuração de Tarifa" 
                description="Defina os valores de cobrança do estacionamento rotativo" 
            />
            <div class="super-card-body">
                <form wire:submit="save" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="super-label">Preço por hora (R$)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">R$</span>
                                <input wire:model="price_per_hour" type="number" step="0.01" min="0" placeholder="0,00" class="super-input pl-10" required>
                            </div>
                            @error('price_per_hour') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Valor cobrado por hora cheia</p>
                        </div>

                        <div>
                            <label class="super-label">Tolerância (minutos)</label>
                            <input wire:model="grace_minutes" type="number" min="0" placeholder="Ex: 15" class="super-input" required>
                            @error('grace_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Período sem cobrança</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="super-label">Fração (minutos)</label>
                            <input wire:model="fraction_minutes" type="number" min="1" placeholder="Ex: 15" class="super-input" required>
                            @error('fraction_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Tempo de cada fração</p>
                        </div>

                        <div>
                            <label class="super-label">Preço por fração (R$)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">R$</span>
                                <input wire:model="fraction_price" type="number" step="0.01" min="0" placeholder="0,00" class="super-input pl-10" required>
                            </div>
                            @error('fraction_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Valor de cada fração de hora</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200">
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">Exemplo de cálculo</h4>
                            <p class="text-sm text-blue-800">
                                Com <strong>R$ {{ number_format($price_per_hour ?: 0, 2, ',', '.') }}/hora</strong> e 
                                <strong>R$ {{ number_format($fraction_price ?: 0, 2, ',', '.') }}/{{ $fraction_minutes ?: 15 }}min</strong>:<br>
                                • 1h 30min = R$ {{ number_format(($price_per_hour ?: 0) + (($fraction_price ?: 0) * 2), 2, ',', '.') }}<br>
                                • 2h = R$ {{ number_format(($price_per_hour ?: 0) * 2, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="super-btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Salvar tarifa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
