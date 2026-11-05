@props(['title' => 'Belum ada data', 'description' => null, 'action' => null, 'actionUrl' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-card border border-dashed border-mist bg-white px-6 py-12 text-center']) }}>
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-sand-soft">
        <x-icon name="inbox" class="h-6 w-6 text-sand" />
    </div>
    <h3 class="text-lg font-semibold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="max-w-md text-sm text-ink-muted">{{ $description }}</p>
    @endif
    @if ($action && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn-primary mt-2">{{ $action }}</a>
    @endif
</div>
