@extends('layouts.public')

@section('title', 'Katalog Produk')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Produk</span>
        </nav>

        <div class="mt-4">
            <h1 class="text-3xl font-extrabold text-ink">Katalog produk</h1>
            <p class="mt-1 text-ink-muted">Cari produk lokal dari UMKM terverifikasi di seluruh Indonesia.</p>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('products.index') }}" class="card mt-5 p-4">
            <div class="grid gap-3 md:grid-cols-12">
                <div class="md:col-span-4">
                    <label for="search" class="label">Cari produk</label>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau deskripsi produk" class="field">
                </div>
                <div class="md:col-span-3">
                    <label for="category_id" class="label">Kategori</label>
                    <select id="category_id" name="category_id" class="field">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="min_price" class="label">Harga min (Rp)</label>
                    <input id="min_price" name="min_price" type="number" min="0" step="1000" value="{{ $filters['min_price'] ?? '' }}" placeholder="0" class="field">
                </div>
                <div class="md:col-span-2">
                    <label for="max_price" class="label">Harga maks (Rp)</label>
                    <input id="max_price" name="max_price" type="number" min="0" step="1000" value="{{ $filters['max_price'] ?? '' }}" placeholder="Tanpa batas" class="field">
                </div>
                <div class="md:col-span-1">
                    <label for="sort" class="label">Urutkan</label>
                    <select id="sort" name="sort" class="field">
                        <option value="-created_at" @selected(($filters['sort'] ?? '') === '-created_at')>Terbaru</option>
                        <option value="price" @selected(($filters['sort'] ?? '') === 'price')>Termurah</option>
                        <option value="-price" @selected(($filters['sort'] ?? '') === '-price')>Termahal</option>
                        <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nama A-Z</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-ink-muted">
                    <input type="checkbox" name="in_stock" value="1" @checked(! empty($filters['in_stock'])) class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                    Hanya tampilkan yang tersedia
                </label>
                <div class="flex gap-2">
                    <a href="{{ route('products.index') }}" class="btn-secondary h-10">Reset</a>
                    <button type="submit" class="btn-primary h-10">
                        <x-icon name="filter" class="h-4 w-4" /> Terapkan
                    </button>
                </div>
            </div>
        </form>

        @if ($products->isEmpty())
            <x-empty-state class="mt-6" title="Produk tidak ditemukan" description="Coba ubah kata kunci, kategori, atau rentang harga." action="Lihat semua produk" :actionUrl="route('products.index')" />
        @else
            <div class="mt-6 flex items-center justify-between text-sm text-ink-muted">
                <p>{{ $products->total() }} produk ditemukan</p>
                <p>Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</p>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
