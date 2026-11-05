@props(['message' => 'MODE SIMULASI: tidak ada uang sungguhan yang diproses.'])

<div {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-control border border-sand bg-sand-soft px-4 py-3 text-sm text-ink']) }} role="note">
    <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
    <span>{{ $message }}</span>
</div>
