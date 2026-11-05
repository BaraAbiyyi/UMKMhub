@extends('layouts.public')

@section('title', 'Toko UMKM')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Toko UMKM</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-ink">Toko UMKM</h1>
                <p class="mt-1 text-ink-muted">Kenali UMKM lokal dan produk unggulan mereka.</p>
            </div>

            <form method="GET" action="{{ route('stores.index') }}" class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                <div class="flex-1">
                    <label for="search" class="sr-only">Cari toko</label>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama toko..."
                           class="field sm:w-56">
                </div>
                <div>
                    <label for="category_id" class="sr-only">Kategori</label>
                    <select id="category_id" name="category_id" class="field sm:w-44">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary">Cari</button>
            </form>
        </div>

        @if ($stores->isEmpty())
            <x-empty-state class="mt-8" title="Toko tidak ditemukan" description="Coba kata kunci atau kategori lain." action="Lihat semua toko" :actionUrl="route('stores.index')" />
        @else
            <p class="mt-6 text-sm text-ink-muted">{{ $stores->total() }} toko ditemukan</p>
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($stores as $store)
                    <x-store-card :store="$store" />
                @endforeach
            </div>

            <div class="mt-6">
                {{ $stores->links() }}
            </div>
        @endif
    </div>
@endsection
