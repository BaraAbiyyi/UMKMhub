@extends('layouts.public')

@section('title', $product->name)

@section('content')
    @php
        $stock = (int) ($product->stock->quantity ?? 0);
        $threshold = (int) config('umkmhub.low_stock_threshold');
        $image = $product->image_url;
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ route('products.index') }}" class="hover:text-brand">Produk</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">{{ $product->name }}</span>
        </nav>

        <div class="mt-4 grid gap-6 md:grid-cols-2">
            <div class="card overflow-hidden">
                <div class="aspect-square w-full bg-mist/30">
                    @if ($image)
                        <img src="{{ $image }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-6xl font-extrabold text-brand/30">{{ mb_substr($product->name, 0, 1) }}</div>
                    @endif
                </div>
            </div>

            <div>
                @if ($product->category)
                    <a href="{{ route('products.index', ['category_id' => $product->category->id]) }}" class="chip">{{ $product->category->name }}</a>
                @endif

                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-ink">{{ $product->name }}</h1>

                <p class="mt-3 text-4xl font-extrabold text-brand">{{ rupiah($product->price) }}</p>

                <div class="mt-2 text-sm text-ink-muted">
                    @if ($stock <= 0)
                        <span class="inline-flex items-center gap-1 font-semibold text-brand"><x-icon name="alert" class="h-4 w-4" /> Stok habis</span>
                    @elseif ($stock <= $threshold)
                        <span class="inline-flex items-center gap-1 font-semibold text-ink"><x-icon name="clock" class="h-4 w-4 text-sand" /> Stok menipis &middot; tersisa {{ $stock }}</span>
                    @else
                        <span class="inline-flex items-center gap-1 font-semibold text-ok"><x-icon name="check" class="h-4 w-4" /> Tersedia &middot; {{ $stock }} unit</span>
                    @endif
                </div>

                <div class="mt-4 rounded-control border border-mist bg-white p-4">
                    <a href="{{ route('stores.show', $product->store->slug) }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-soft font-bold text-brand">
                            {{ mb_substr($product->store->name, 0, 1) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-ink">{{ $product->store->name }}</span>
                            <span class="flex items-center gap-1 text-xs text-ink-muted"><x-icon name="map-pin" class="h-3.5 w-3.5" /> {{ $product->store->city }}</span>
                        </span>
                        <x-icon name="chevron-right" class="ml-auto h-5 w-5 text-ink-muted" />
                    </a>
                </div>

                @if ($stock > 0)
                    @auth
                        @if (auth()->user()->isCustomer())
                            <form method="POST" action="{{ route('cart.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="redirect" value="{{ route('products.show', $product) }}">

                                <div class="sm:w-32">
                                    <label for="quantity" class="label">Jumlah</label>
                                    <input id="quantity" name="quantity" type="number" min="1" max="{{ $stock }}" value="1" required class="field">
                                </div>
                                <button type="submit" class="btn-primary flex-1">
                                    <x-icon name="cart" class="h-4 w-4" /> Tambah ke keranjang
                                </button>
                            </form>
                        @else
                            <a href="{{ auth()->user()->isUmkmAdmin() ? route('umkm.dashboard') : route('admin.dashboard') }}" class="btn-secondary mt-4 w-full">Kembali ke dashboard</a>
                        @endif
                    @else
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('login') }}" class="btn-primary flex-1">Masuk untuk membeli</a>
                            <a href="{{ route('register') }}" class="btn-secondary flex-1">Daftar akun</a>
                        </div>
                    @endauth
                @else
                    <button type="button" disabled class="btn-secondary mt-4 w-full">Stok habis</button>
                @endif

                <x-simulation-banner class="mt-4" message="MODE SIMULASI: pembelian di UMKMHub adalah simulasi tanpa transaksi uang sungguhan." />

                @if ($product->description)
                    <div class="mt-6">
                        <h2 class="text-lg font-bold text-ink">Deskripsi produk</h2>
                        <div class="mt-2 text-sm leading-relaxed text-ink-muted">{!! nl2br(e($product->description)) !!}</div>
                    </div>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xl font-bold text-ink">Produk serupa</h2>
                <div class="mt-3 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
