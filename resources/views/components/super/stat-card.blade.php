@props([
    'label',
    'value',
    'iconBg' => 'bg-blue-50',
    'iconColor' => 'text-blue-700',
    'hint' => null,
])

<div class="super-card hover:shadow-md transition-shadow">
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-500 mb-2">{{ $label }}</p>
                <p class="text-3xl font-bold text-slate-900 mb-1">{{ $value }}</p>
                @if($hint)
                    <p class="text-xs text-slate-500">{{ $hint }}</p>
                @endif
            </div>
            <div class="shrink-0">
                <div class="w-12 h-12 rounded-lg {{ $iconBg }} {{ $iconColor }} flex items-center justify-center">
                    {{ $icon ?? '' }}
                </div>
            </div>
        </div>
    </div>
</div>
