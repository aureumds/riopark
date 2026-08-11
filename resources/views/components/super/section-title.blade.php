@props(['title', 'description' => null, 'action' => null])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
        @if($description)
            <p class="text-sm text-slate-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    @if($action)
        <div>{{ $action }}</div>
    @endif
</div>
