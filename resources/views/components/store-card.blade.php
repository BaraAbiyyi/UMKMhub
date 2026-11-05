@props(['store'])

@php
    $logo = $store->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($store->logo_path) : null;
@endphp

<article class="card flex flex-col gap-3 p-5">
    <div class="flex items-center gap-3">
        @if ($logo)
            <img src="{{ $logo }}" alt="Logo {{ $store->name }}" class="h-12 w-12 rounded-full border border-mist object-cover">
        @else
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-lg font-bold text-brand">
                {{ mb_substr($store->name, 0, 1) }}
            </div>
        @endif
        <div class="min-w-0">
            <h3 class="truncate font-semibold text-ink">{{ $store->name }}</h3>
            <p class="truncate text-sm text-ink-muted">{{ $store->city }}@if ($store->category) &middot; {{ $store->category->name }}@endif</p>
        </div>
    </div>

    <p class="line-clamp-2 text-sm text-ink-muted">{{ $store->description ?: 'Toko UMKM di UMKMHub.' }}</p>

    <div class="mt-auto flex items-center justify-between pt-2 text-sm">
        <span class="text-ink-muted">{{ $store->products_count ?? 0 }} produk</span>
        <a href="{{ route('stores.show', $store->slug) }}" class="font-semibold text-brand hover:underline">Kunjungi toko</a>
    </div>
</article>
