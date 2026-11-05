@props(['label', 'value', 'note' => null, 'accent' => false, 'icon' => 'chart'])

<div class="card p-5 {{ $accent ? 'border-brand' : '' }}">
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-ink-muted">{{ $label }}</p>
        <span class="flex h-9 w-9 items-center justify-center rounded-control bg-brand-soft text-brand">
            <x-icon :name="$icon" class="h-4 w-4" />
        </span>
    </div>
    <p class="mt-3 text-3xl font-bold text-ink">{{ $value }}</p>
    @if ($note)
        <p class="mt-1 text-sm text-ink-muted">{{ $note }}</p>
    @endif
</div>
