@props([
    'title',
    'description' => null,
])

<div class="border-b border-slate-200 pb-4 mb-6">
    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-slate-500 mt-0.5">{{ $description }}</p>
    @endif
</div>
