@props(['product'])

@php
    $stock = (int) ($product->stock->quantity ?? 0);
    $image = $product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : null;
@endphp

<article class="card group flex flex-col overflow-hidden">
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="aspect-square w-full overflow-hidden bg-mist/30">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-105">
            @else
                <div class="flex h-full w-full items-center justify-center text-3xl font-bold text-brand/50">
                    {{ mb_substr($product->name, 0, 1) }}
                </div>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-1 p-4">
        <a href="{{ route('products.show', $product) }}" class="line-clamp-2 text-sm font-semibold text-ink hover:text-brand">
            {{ $product->name }}
        </a>
        <p class="text-xs text-ink-muted">
            {{ $product->store?->name ?? 'Toko' }} &middot; {{ $product->store?->city ?? '-' }}
        </p>
        <p class="mt-1 text-xl font-bold text-brand">{{ rupiah($product->price) }}</p>

        <div class="mt-auto pt-3">
            @if ($stock <= 0)
                <span class="inline-flex h-11 w-full items-center justify-center rounded-control bg-canvas text-sm font-semibold text-ink-muted">Stok habis</span>
            @elseif (auth()->check() && auth()->user()->isCustomer())
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                    <button type="submit" class="btn-primary w-full">
                        <x-icon name="cart" class="h-4 w-4" /> Keranjang
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-primary w-full">Beli</a>
            @endif
        </div>
    </div>
</article>
