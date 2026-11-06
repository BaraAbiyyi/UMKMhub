@props(['product'])

@php
    $stock = (int) ($product->stock->quantity ?? 0);
    $image = null;
    if ($product->image_path) {
        if (filter_var($product->image_path, FILTER_VALIDATE_URL)) {
            $image = $product->image_path;
        } elseif (file_exists(public_path($product->image_path))) {
            $image = asset($product->image_path);
        } elseif (file_exists(public_path('storage/' . $product->image_path))) {
            $image = asset('storage/' . $product->image_path);
        } elseif (file_exists(public_path('images/' . $product->image_path))) {
            $image = asset('images/' . $product->image_path);
        } else {
            $image = \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path);
        }
    }
    if (!$image) {
        $lowerName = strtolower($product->name ?? '');
        if (str_contains($lowerName, 'drip')) $image = asset('images/products/drip-coffee.jpg');
        elseif (str_contains($lowerName, 'arabika')) $image = asset('images/products/kopi-arabika.jpg');
        elseif (str_contains($lowerName, 'robusta')) $image = asset('images/products/kopi-robusta.jpg');
        elseif (str_contains($lowerName, 'singkong')) $image = asset('images/products/keripik-singkong.jpg');
        elseif (str_contains($lowerName, 'tempe')) $image = asset('images/products/keripik-tempe.jpg');
    }
@endphp

<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md">
    <a href="{{ route('products.show', $product) }}" class="relative block overflow-hidden bg-gray-50" aria-label="{{ $product->name }}">
        <div class="aspect-square w-full overflow-hidden bg-gradient-to-tr from-gray-100 to-gray-50">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
                <div class="flex h-full w-full flex-col items-center justify-center bg-rose-50/40 p-4 text-center transition-transform duration-300 group-hover:scale-105">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-xs ring-1 ring-rose-100">
                        <span class="text-2xl font-black text-[#6B1D2F]">{{ mb_substr($product->name, 0, 1) }}</span>
                    </div>
                    <span class="mt-2 text-[11px] font-medium text-gray-400 line-clamp-1">{{ $product->category?->name ?? 'Produk Lokal' }}</span>
                </div>
            @endif
        </div>

        @if ($stock <= 0)
            <div class="absolute left-2.5 top-2.5 rounded-md bg-gray-900/80 px-2 py-0.5 text-[11px] font-semibold text-white backdrop-blur-xs">
                Habis
            </div>
        @elseif ($stock <= 3)
            <div class="absolute left-2.5 top-2.5 rounded-md bg-amber-600/90 px-2 py-0.5 text-[11px] font-semibold text-white backdrop-blur-xs">
                Sisa {{ $stock }}
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <div class="mb-1 flex items-center gap-1.5 text-xs text-gray-500">
            <x-icon name="store" class="h-3.5 w-3.5 shrink-0 text-gray-400" />
            <span class="truncate font-medium">{{ $product->store?->name ?? 'Toko' }} &middot; {{ $product->store?->city ?? '-' }}</span>
        </div>

        <a href="{{ route('products.show', $product) }}" class="line-clamp-2 text-sm font-bold text-gray-900 transition-colors group-hover:text-[#6B1D2F]" title="{{ $product->name }}">
            {{ $product->name }}
        </a>

        <div class="mt-3 flex items-baseline justify-between gap-2">
            <div>
                <span class="text-xs font-medium text-gray-400 block">Harga</span>
                <p class="text-lg font-black text-[#6B1D2F]">{{ rupiah($product->price) }}</p>
            </div>
        </div>

        <div class="mt-auto pt-3">
            @if ($stock <= 0)
                <span class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-gray-100 text-xs font-semibold text-gray-400 cursor-not-allowed">
                    Stok habis
                </span>
            @elseif (auth()->check() && auth()->user()->isCustomer())
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="redirect" value="{{ url()->current() }}">
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#6B1D2F] px-4 text-sm font-semibold text-white shadow-xs transition-all hover:bg-[#5B1324] hover:shadow active:scale-[0.98]">
                        <x-icon name="cart" class="h-4 w-4" /> Beli
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-[#6B1D2F] px-4 text-sm font-semibold text-white shadow-xs transition-all hover:bg-[#5B1324] hover:shadow active:scale-[0.98]">
                    Beli
                </a>
            @endif
        </div>
    </div>
</article>
