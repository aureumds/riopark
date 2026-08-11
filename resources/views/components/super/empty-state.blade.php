@props([
    'title',
    'description' => null,
])

<div class="text-center py-12">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
        {{ $icon ?? '' }}
    </div>
    <h3 class="text-lg font-medium text-slate-700">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
    @endif
</div>
