@extends('layouts.public')

@section('title', 'Marketplace UMKM Lokal')

@section('content')
    {{-- Hero Section --}}
    <section class="relative overflow-hidden border-b border-gray-200/80 bg-[#FAF8F5]">
        {{-- Hero Background Image with Atmospheric Lighting --}}
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <img src="{{ asset('images/hero/hero-bg.jpg') }}"
                 alt="Suasana UMKM Lokal"
                 class="h-full w-full object-cover object-right sm:object-center opacity-50 transition-opacity duration-700">
            {{-- Dual directional gradients: ensures 100% text readability on left while keeping photo clear on right --}}
            <div class="absolute inset-0 bg-gradient-to-r from-[#FAF8F5] via-[#FAF8F5]/85 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#FAF8F5] via-transparent to-[#FAF8F5]/40"></div>
        </div>

        {{-- Subtle background decoration blobs --}}
        <div class="pointer-events-none absolute -top-40 right-0 z-0 h-[500px] w-[500px] rounded-full bg-gradient-to-bl from-rose-200/30 via-amber-100/20 to-transparent blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 left-0 z-0 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-rose-100/30 to-transparent blur-3xl"></div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:grid lg:grid-cols-12 lg:items-center lg:gap-12 lg:py-20 lg:px-8">
            {{-- Left Column --}}
            <div class="lg:col-span-7">
                <div class="inline-flex items-center gap-2 rounded-full border border-rose-200/80 bg-rose-50/80 px-3.5 py-1 text-xs font-semibold text-[#6B1D2F] shadow-2xs backdrop-blur-xs">
                    <x-icon name="star" class="h-3.5 w-3.5 text-[#6B1D2F]" />
                    <span>Marketplace UMKM lokal</span>
                </div>

                <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl leading-[1.12]">
                    Belanja dari banyak UMKM, <span class="text-[#6B1D2F]">satu keranjang.</span>
                </h1>

                <p class="mt-5 max-w-xl text-base sm:text-lg leading-relaxed text-gray-600">
                    Temukan produk lokal pilihan dan toko-toko UMKM terverifikasi. Checkout sekali, pesanan dipisah otomatis per toko.
                </p>

                <div class="mt-8 flex flex-col gap-3.5 sm:flex-row sm:items-center">
                    <a href="{{ route('stores.index') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#6B1D2F] px-6 text-sm font-semibold text-white shadow-md shadow-rose-900/10 transition-all hover:bg-[#5B1324] hover:shadow-lg active:scale-[0.98]">
                        <x-icon name="store" class="h-4.5 w-4.5" /> Lihat UMKM
                    </a>
                    <a href="{{ route('store-applications.create') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-6 text-sm font-semibold text-gray-800 shadow-xs transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-[#6B1D2F] active:scale-[0.98]">
                        Daftarkan UMKM Anda
                    </a>
                </div>

                <p class="mt-4 text-sm text-gray-500">
                    Ingin langsung cari produk? <a href="{{ route('products.index') }}" class="font-bold text-[#6B1D2F] hover:text-[#5B1324] hover:underline inline-flex items-center gap-1 group">Jelajahi katalog <x-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" /></a>
                </p>

                {{-- Trust Badges --}}
                <div class="mt-8 flex flex-wrap items-center gap-6 border-t border-gray-100 pt-6 text-sm font-medium text-gray-600">
                    <div class="inline-flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200/60">
                            <x-icon name="shield" class="h-3.5 w-3.5" />
                        </span>
                        <span>Toko terverifikasi</span>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-rose-50 text-[#6B1D2F] ring-1 ring-rose-200/60">
                            <x-icon name="cart" class="h-3.5 w-3.5" />
                        </span>
                        <span>Lintas toko</span>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200/60">
                            <x-icon name="info" class="h-3.5 w-3.5" />
                        </span>
                        <span>Mode simulasi</span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Floating Staggered Showcase Card Produk --}}
            <div class="relative mt-12 lg:col-span-5 lg:mt-0">
                {{-- Ambient glowing blob behind showcase --}}
                <div class="absolute -inset-4 -z-10 rounded-3xl bg-gradient-to-tr from-rose-100/50 via-amber-50/40 to-red-100/30 blur-2xl opacity-75"></div>

                {{-- Floating Tag Top Right --}}
                <div class="pointer-events-none absolute -top-4 -right-2 z-20 hidden sm:flex items-center gap-2 rounded-full border border-white/90 bg-white/95 px-3 py-1.5 shadow-md shadow-gray-200/60 backdrop-blur-md">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-800">100% Produk UMKM Asli</span>
                </div>

                {{-- Staggered Grid of 4 Products --}}
                <div class="grid grid-cols-2 gap-3.5 sm:gap-4">
                    @forelse ($products->take(4) as $index => $product)
                        @php
                            $productImage = $product->image_url;
                        @endphp
                        <a href="{{ route('products.show', $product) }}"
                           class="group relative flex flex-col overflow-hidden rounded-2xl border border-white/90 bg-white/90 p-2.5 sm:p-3 shadow-md shadow-gray-200/50 backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-rose-100 {{ $index % 2 === 1 ? 'sm:mt-8' : '' }}">
                            <div class="aspect-square w-full overflow-hidden rounded-xl bg-gradient-to-tr from-gray-100 to-rose-50/30 relative">
                                @if ($productImage)
                                    <img src="{{ $productImage }}"
                                         alt="{{ $product->name }}"
                                         class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full flex-col items-center justify-center p-3 text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-2xs ring-1 ring-rose-100/80">
                                            <span class="text-xl font-black text-[#6B1D2F]">{{ mb_substr($product->name, 0, 1) }}</span>
                                        </div>
                                        <span class="mt-2 text-[10px] font-medium text-gray-400 line-clamp-1">{{ $product->category?->name ?? 'Lokal' }}</span>
                                    </div>
                                @endif

                                <span class="absolute top-2 left-2 rounded-md bg-white/95 px-1.5 py-0.5 text-[10px] font-bold text-gray-700 shadow-2xs backdrop-blur-xs">
                                    {{ $product->store?->city ?? 'UMKM' }}
                                </span>
                            </div>

                            <div class="mt-2.5 flex flex-col p-1">
                                <span class="truncate text-[11px] font-medium text-gray-500">
                                    {{ $product->store?->name ?? 'Toko UMKM' }}
                                </span>
                                <h4 class="mt-0.5 line-clamp-1 text-xs sm:text-sm font-bold text-gray-900 group-hover:text-[#6B1D2F] transition-colors" title="{{ $product->name }}">
                                    {{ $product->name }}
                                </h4>
                                <p class="mt-1.5 text-xs sm:text-sm font-black text-[#6B1D2F]">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-2">
                            <x-empty-state title="Katalog segera hadir" description="Belum ada produk yang tayang. Silakan cek kembali nanti." />
                        </div>
                    @endforelse
                </div>

                {{-- Floating Feature Widget Bottom Left --}}
                <div class="pointer-events-none absolute -bottom-5 -left-4 z-20 hidden sm:flex items-center gap-3 rounded-2xl border border-white/90 bg-white/95 px-4 py-3 shadow-xl shadow-rose-950/10 backdrop-blur-md">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-[#6B1D2F] ring-1 ring-rose-200/60">
                        <x-icon name="cart" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-900">Satu Keranjang Otomatis</p>
                        <p class="text-[11px] text-gray-500">Checkout multi-toko dipisah instan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Kategori Section --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">Jelajahi kategori</h2>
                    <p class="mt-1 text-sm text-gray-500">Temukan produk sesuai kebutuhan Anda.</p>
                </div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#6B1D2F] hover:text-[#5B1324] hover:underline">
                    Semua kategori <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-6 flex flex-wrap gap-2.5">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-slate-100/90 px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-150 hover:border-red-200 hover:bg-red-50 hover:text-red-900 hover:shadow-xs {{ request('category_id') == $category->id ? 'border-[#6B1D2F] bg-rose-50 text-[#6B1D2F] font-semibold shadow-xs' : '' }}">
                        <span>{{ $category->name }}</span>
                        @if ($category->products_count > 0)
                            <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-gray-500 ring-1 ring-slate-200/70">{{ $category->products_count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk Terbaru Section --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">Produk terbaru</h2>
                <p class="mt-1 text-sm text-gray-500">Langsung dari toko UMKM terverifikasi.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#6B1D2F] hover:text-[#5B1324] hover:underline">
                Lihat semua <x-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>

        @if ($products->isEmpty())
            <x-empty-state class="mt-6" title="Belum ada produk" description="Produk dari UMKM akan muncul di sini setelah toko disetujui." />
        @else
            <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- UMKM Pilihan Section --}}
    <section class="border-y border-gray-200/80 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">UMKM pilihan</h2>
                    <p class="mt-1 text-sm text-gray-500">Kenali cerita di balik setiap toko.</p>
                </div>
                <a href="{{ route('stores.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#6B1D2F] hover:text-[#5B1324] hover:underline">
                    Semua toko <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            @if ($stores->isEmpty())
                <x-empty-state class="mt-6" title="Belum ada toko" description="Toko UMKM yang disetujui akan tampil di sini." />
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stores as $store)
                        <x-store-card :store="$store" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Belanja dalam 3 Langkah --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="text-center sm:text-left">
            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">Belanja dalam 3 langkah</h2>
            <p class="mt-1 text-sm text-gray-500">Pengalaman berbelanja lintas UMKM yang mudah, cepat, dan transparan.</p>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @foreach ([
                [
                    'step' => '01',
                    'icon' => 'search',
                    'title' => 'Pilih produk',
                    'text' => 'Jelajahi produk dari banyak toko UMKM dalam satu katalog.'
                ],
                [
                    'step' => '02',
                    'icon' => 'cart',
                    'title' => 'Satu keranjang',
                    'text' => 'Gabungkan produk dari beberapa toko. Pesanan dipisah otomatis per toko.'
                ],
                [
                    'step' => '03',
                    'icon' => 'shield',
                    'title' => 'Bayar simulasi',
                    'text' => 'Selesaikan pembayaran simulasi. Tidak ada uang sungguhan yang diproses.'
                ],
            ] as $step)
                <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-7 shadow-xs transition-all duration-200 hover:-translate-y-1 hover:border-gray-300 hover:shadow-md">
                    {{-- Big number watermark --}}
                    <span class="absolute right-4 top-4 select-none text-5xl font-black text-gray-100 transition-colors group-hover:text-rose-100/50">
                        {{ $step['step'] }}
                    </span>

                    <div class="relative z-10">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-[#6B1D2F] ring-1 ring-rose-200/60 transition-colors duration-200 group-hover:bg-[#6B1D2F] group-hover:text-white">
                            <x-icon :name="$step['icon']" class="h-5 w-5" />
                        </span>

                        <h3 class="mt-5 text-lg font-bold text-gray-900 transition-colors group-hover:text-[#6B1D2F]">
                            {{ $step['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed text-gray-600">
                            {{ $step['text'] }}
                        </p>
                    </div>

                    <div class="relative z-10 mt-6 flex items-center gap-1.5 text-xs font-bold text-[#6B1D2F]">
                        <span>Langkah {{ $step['step'] }}</span>
                        <x-icon name="arrow-right" class="h-3 w-3 transition-transform group-hover:translate-x-1" />
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Banner CTA UMKM --}}
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#450A17] via-[#6B1D2F] to-[#5B1324] px-6 py-10 sm:px-12 sm:py-14 text-white shadow-xl shadow-rose-950/20">
            {{-- Background Photo with Deep Maroon Overlay --}}
            <div class="absolute inset-0 z-0 pointer-events-none">
                <img src="{{ asset('images/hero/hero-bg.jpg') }}" alt="" class="h-full w-full object-cover object-center opacity-20 mix-blend-overlay filter blur-[0.4px]">
                <div class="absolute inset-0 bg-gradient-to-r from-[#450A17]/95 via-[#6B1D2F]/90 to-[#5B1324]/85"></div>
            </div>

            {{-- Decorative mesh gradient circles --}}
            <div class="pointer-events-none absolute -right-20 -top-20 z-0 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 z-0 h-64 w-64 rounded-full bg-rose-400/10 blur-2xl"></div>

            <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-rose-200 backdrop-blur-xs ring-1 ring-white/15">
                        <x-icon name="store" class="h-3.5 w-3.5 text-rose-200" />
                        Peluang Mitra Lokal
                    </span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-white sm:text-3xl lg:text-4xl">
                        Punya produk lokal? Bawa toko Anda ke UMKMhub.
                    </h2>
                    <p class="mt-2 text-sm sm:text-base text-rose-100/90 leading-relaxed">
                        Kelola produk, stok, dan pesanan dari satu dashboard.
                    </p>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('store-applications.create') }}"
                       class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-white px-7 text-sm font-bold text-[#6B1D2F] shadow-lg shadow-black/10 transition-all hover:bg-rose-50 hover:scale-[1.02] active:scale-[0.98]">
                        Daftar sebagai UMKM
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
