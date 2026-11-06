@extends('layouts.public')

@section('title', $store->name)

@section('content')
    @php $logo = $store->logo_url; @endphp

    <div class="border-b border-mist bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8">
            <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
                <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('stores.index') }}" class="hover:text-brand">Toko UMKM</a>
                <span aria-hidden="true"> / </span>
                <span class="text-ink">{{ $store->name }}</span>
            </nav>

            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo {{ $store->name }}" class="h-20 w-20 rounded-card border border-mist object-cover">
                @else
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-card bg-brand-soft text-3xl font-extrabold text-brand">
                        {{ mb_substr($store->name, 0, 1) }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-extrabold text-ink">{{ $store->name }}</h1>
                        <x-status-badge status="approved" />
                    </div>
                    <p class="mt-2 max-w-2xl text-ink-muted">{{ $store->description ?: 'UMKM lokal di UMKMHub.' }}</p>
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-ink-muted">
                        <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="h-4 w-4" /> {{ $store->city }}</span>
                        @if ($store->category)
                            <span class="inline-flex items-center gap-1"><x-icon name="tag" class="h-4 w-4" /> {{ $store->category->name }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1"><x-icon name="box" class="h-4 w-4" /> {{ $store->products_count }} produk</span>
                        @if ($store->phone)
                            <span class="inline-flex items-center gap-1"><x-icon name="phone" class="h-4 w-4" /> {{ $store->phone }}</span>
                        @endif
                    </div>
                    @if ($store->address)
                        <p class="mt-1 text-sm text-ink-muted">{{ $store->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-ink">Produk toko</h2>
                <p class="text-sm text-ink-muted">{{ $products->total() }} produk tayang</p>
            </div>
            <a href="{{ route('products.index', ['store' => $store->slug]) }}" class="text-sm font-semibold text-brand hover:underline">Lihat di katalog</a>
        </div>

        @if ($products->isEmpty())
            <x-empty-state class="mt-4" title="Belum ada produk" description="Toko ini belum menayangkan produk. Silakan cek kembali nanti." />
        @else
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif

        <x-simulation-banner class="mt-8" message="MODE SIMULASI: transaksi di toko ini tidak melibatkan uang atau pengiriman sungguhan." />
    </div>
@endsection
