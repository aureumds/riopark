<div>
    <div class="max-w-3xl">
        <div class="super-card">
            <x-super.card-header 
                title="Configurações Gerais" 
                description="Personalize o funcionamento do estacionamento" 
            />
            <div class="super-card-body">
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Impressão de tickets</h3>
                        <div class="space-y-3">
                            <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-slate-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50/50 transition">
                                <input type="checkbox" wire:model="print_ticket_on_entry" class="mt-0.5 w-4 h-4 text-blue-600 rounded">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-slate-900">Imprimir ticket na entrada</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Ticket físico será impresso ao registrar entrada do veículo</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-slate-200 cursor-pointer hover:border-blue-300 hover:bg-blue-50/50 transition">
                                <input type="checkbox" wire:model="print_ticket_on_exit" class="mt-0.5 w-4 h-4 text-blue-600 rounded">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-slate-900">Imprimir ticket na saída</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Comprovante será impresso ao finalizar pagamento</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-200">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Personalização visual</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="super-label">Cor principal</label>
                                <input wire:model="primary_color" type="color" class="w-full h-12 rounded-xl border border-slate-200 cursor-pointer">
                                <p class="mt-2 text-xs text-slate-500">Cor principal da interface do operador</p>
                            </div>
                            <div>
                                <label class="super-label">Cor de destaque</label>
                                <input wire:model="accent_color" type="color" class="w-full h-12 rounded-xl border border-slate-200 cursor-pointer">
                                <p class="mt-2 text-xs text-slate-500">Cor para botões e destaques</p>
                            </div>
                        </div>

                        <div class="mt-4 p-4 rounded-lg bg-slate-50 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg" style="background-color: {{ $primary_color }}"></div>
                            <div class="w-10 h-10 rounded-lg" style="background-color: {{ $accent_color }}"></div>
                            <span class="text-sm text-slate-600">Pré-visualização das cores</span>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="super-btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Salvar configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
