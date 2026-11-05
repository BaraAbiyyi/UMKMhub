@extends('layouts.public')

@section('title', 'Marketplace UMKM Lokal')

@section('content')
    {{-- Hero --}}
    <section class="border-b border-mist bg-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-2 md:items-center md:py-16">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-sand bg-sand-soft px-3 py-1 text-xs font-semibold text-ink">
                    <x-icon name="star" class="h-4 w-4 text-brand" /> Marketplace UMKM lokal
                </p>
                <h1 class="mt-4 text-4xl font-extrabold leading-tight text-ink sm:text-5xl">
                    Belanja dari banyak UMKM, <span class="text-brand">satu keranjang.</span>
                </h1>
                <p class="mt-4 max-w-xl text-base text-ink-muted">
                    Temukan produk lokal pilihan dari toko-toko UMKM terverifikasi. Checkout sekali, pesanan dipisah otomatis per toko.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('stores.index') }}" class="btn-primary">
                        <x-icon name="store" class="h-4 w-4" /> Lihat UMKM
                    </a>
                    <a href="{{ route('store-applications.create') }}" class="btn-secondary">Daftarkan UMKM Anda</a>
                </div>
                <p class="mt-3 text-sm text-ink-muted">
                    Ingin langsung cari produk? <a href="{{ route('products.index') }}" class="font-semibold text-brand hover:underline">Jelajahi katalog</a>
                </p>
                <div class="mt-6 flex flex-wrap gap-4 text-sm text-ink-muted">
                    <span class="inline-flex items-center gap-1"><x-icon name="shield" class="h-4 w-4 text-ok" /> Toko terverifikasi</span>
                    <span class="inline-flex items-center gap-1"><x-icon name="cart" class="h-4 w-4 text-ok" /> Lintas toko</span>
                    <span class="inline-flex items-center gap-1"><x-icon name="info" class="h-4 w-4 text-ok" /> Mode simulasi</span>
                </div>
            </div>

            <div class="relative">
                <div class="grid grid-cols-2 gap-3">
                    @forelse ($products->take(4) as $index => $product)
                        <a href="{{ route('products.show', $product) }}"
                           class="card overflow-hidden {{ $index % 2 === 1 ? 'mt-6' : '' }}">
                            <div class="aspect-square bg-mist/30">
                                @if ($product->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-3xl font-bold text-brand/40">{{ mb_substr($product->name, 0, 1) }}</div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="line-clamp-1 text-sm font-semibold text-ink">{{ $product->name }}</p>
                                <p class="text-sm font-bold text-brand">{{ rupiah($product->price) }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-2">
                            <x-empty-state title="Katalog segera hadir" description="Belum ada produk yang tayang. Silakan cek kembali nanti." />
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Kategori --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-10">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-ink">Jelajahi kategori</h2>
                    <p class="mt-1 text-sm text-ink-muted">Temukan produk sesuai kebutuhan Anda.</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="chip {{ request('category_id') == $category->id ? 'chip-active' : '' }}">
                        {{ $category->name }}
                        <span class="text-xs text-ink-muted">{{ $category->products_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk terbaru --}}
    <section class="mx-auto max-w-6xl px-4 pb-10">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-ink">Produk terbaru</h2>
                <p class="mt-1 text-sm text-ink-muted">Langsung dari toko UMKM terverifikasi.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">
                Lihat semua <x-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>

        @if ($products->isEmpty())
            <x-empty-state class="mt-4" title="Belum ada produk" description="Produk dari UMKM akan muncul di sini setelah toko disetujui." />
        @else
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Toko --}}
    <section class="border-y border-mist bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-ink">UMKM pilihan</h2>
                    <p class="mt-1 text-sm text-ink-muted">Kenali cerita di balik setiap toko.</p>
                </div>
                <a href="{{ route('stores.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">
                    Semua toko <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            @if ($stores->isEmpty())
                <x-empty-state class="mt-4" title="Belum ada toko" description="Toko UMKM yang disetujui akan tampil di sini." />
            @else
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach ($stores as $store)
                        <x-store-card :store="$store" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Cara kerja --}}
    <section class="mx-auto max-w-6xl px-4 py-10">
        <h2 class="text-2xl font-bold text-ink">Belanja dalam 3 langkah</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['icon' => 'search', 'title' => 'Pilih produk', 'text' => 'Jelajahi produk dari banyak toko UMKM dalam satu katalog.'],
                ['icon' => 'cart', 'title' => 'Satu keranjang', 'text' => 'Gabungkan produk dari beberapa toko. Pesanan dipisah otomatis per toko.'],
                ['icon' => 'shield', 'title' => 'Bayar simulasi', 'text' => 'Selesaikan pembayaran simulasi. Tidak ada uang sungguhan yang diproses.'],
            ] as $step)
                <div class="card p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-control bg-brand-soft text-brand">
                        <x-icon :name="$step['icon']" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-3 font-bold text-ink">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm text-ink-muted">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA UMKM --}}
    <section class="mx-auto max-w-6xl px-4 pb-12">
        <div class="rounded-card border border-brand/20 bg-brand px-6 py-8 text-white sm:px-10">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Punya produk lokal? Bawa toko Anda ke UMKMHub.</h2>
                    <p class="mt-1 text-sm text-white/80">Kelola produk, stok, dan pesanan dari satu dashboard.</p>
                </div>
                <a href="{{ route('store-applications.create') }}" class="btn bg-white text-brand hover:bg-white/90">Daftar sebagai UMKM</a>
            </div>
        </div>
    </section>
@endsection
