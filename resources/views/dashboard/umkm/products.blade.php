@extends('layouts.dashboard')

@section('title', 'Produk')
@section('eyebrow', 'Dashboard UMKM')
@section('heading', 'Produk toko')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">{{ $products->total() }} produk di {{ $store->name }}.</p>
        <details class="relative">
            <summary class="btn-primary h-9 cursor-pointer list-none px-3 text-sm">
                <x-icon name="plus" class="h-4 w-4" /> Tambah produk
            </summary>
            <div class="mt-2 w-full min-w-72 rounded-card border border-mist bg-white p-5 shadow-card sm:w-96">
                <h2 class="text-lg font-bold text-ink">Produk baru</h2>
                <form method="POST" action="{{ route('umkm.products.store') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label for="new-name" class="label">Nama produk</label>
                        <input id="new-name" name="name" type="text" required value="{{ old('name') }}" class="field @error('name') border-brand @enderror">
                        @error('name')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="new-category" class="label">Kategori</label>
                            <select id="new-category" name="category_id" required class="field @error('category_id') border-brand @enderror">
                                <option value="">Pilih</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-price" class="label">Harga (Rp)</label>
                            <input id="new-price" name="price" type="number" min="0" step="500" required value="{{ old('price') }}" class="field @error('price') border-brand @enderror">
                            @error('price')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="new-description" class="label">Deskripsi</label>
                        <textarea id="new-description" name="description" rows="2" class="field h-auto py-2">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label for="new-image" class="label">Foto produk</label>
                        <input id="new-image" name="image" type="file" accept="image/*" class="block w-full text-sm text-ink-muted file:mr-3 file:rounded-control file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand">
                        @error('image')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2 text-sm text-ink">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                        Tayangkan produk
                    </label>
                    <button type="submit" class="btn-primary w-full">Simpan produk</button>
                </form>
            </div>
        </details>
    </div>

    <form method="GET" action="{{ route('umkm.products') }}" class="card mt-4 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="search" class="label">Cari produk</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama produk" class="field">
        </div>
        <div class="sm:w-48">
            <label for="category_id" class="label">Kategori</label>
            <select id="category_id" name="category_id" class="field">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-40">
            <label for="is_active" class="label">Tayang</label>
            <select id="is_active" name="is_active" class="field">
                <option value="">Semua</option>
                <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Aktif</option>
                <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Nonaktif</option>
            </select>
        </div>
        <button type="submit" class="btn-primary h-11">Terapkan</button>
    </form>

    @if ($products->isEmpty())
        <x-empty-state class="mt-4" title="Belum ada produk" description="Tambahkan produk pertama Anda agar tampil di marketplace." />
    @else
        <div class="mt-4 space-y-3">
            @foreach ($products as $product)
                @php
                    $image = $product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : null;
                    $stock = (int) ($product->stock->quantity ?? 0);
                @endphp
                <article class="card p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-control bg-mist/30">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center text-lg font-bold text-brand/40">{{ mb_substr($product->name, 0, 1) }}</div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate font-semibold text-ink">{{ $product->name }}</h2>
                                @if ($product->is_active)
                                    <span class="rounded-full bg-ok/10 px-2 py-0.5 text-xs font-semibold text-ok">Aktif</span>
                                @else
                                    <span class="rounded-full bg-canvas px-2 py-0.5 text-xs font-semibold text-ink-muted">Nonaktif</span>
                                @endif
                            </div>
                            <p class="text-sm text-ink-muted">{{ $product->category->name }} &middot; {{ rupiah($product->price) }}</p>
                            <p class="text-xs {{ $stock <= (int) config('umkmhub.low_stock_threshold') ? 'font-semibold text-brand' : 'text-ink-muted' }}">
                                Stok {{ $stock }} unit
                            </p>
                        </div>

                        <details class="w-full sm:w-auto">
                            <summary class="btn-secondary h-9 cursor-pointer list-none px-3 text-sm">
                                <x-icon name="edit" class="h-4 w-4" /> Ubah
                            </summary>
                            <div class="mt-3 w-full rounded-control border border-mist bg-canvas p-4 sm:w-96">
                                <form method="POST" action="{{ route('umkm.products.update', $product) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label for="name-{{ $product->id }}" class="label">Nama produk</label>
                                        <input id="name-{{ $product->id }}" name="name" type="text" required value="{{ $product->name }}" class="field">
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="category-{{ $product->id }}" class="label">Kategori</label>
                                            <select id="category-{{ $product->id }}" name="category_id" required class="field">
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="price-{{ $product->id }}" class="label">Harga (Rp)</label>
                                            <input id="price-{{ $product->id }}" name="price" type="number" min="0" step="500" required value="{{ $product->price }}" class="field">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="description-{{ $product->id }}" class="label">Deskripsi</label>
                                        <textarea id="description-{{ $product->id }}" name="description" rows="2" class="field h-auto py-2">{{ $product->description }}</textarea>
                                    </div>
                                    <div>
                                        <label for="image-{{ $product->id }}" class="label">Ganti foto (opsional)</label>
                                        <input id="image-{{ $product->id }}" name="image" type="file" accept="image/*" class="block w-full text-sm text-ink-muted file:mr-3 file:rounded-control file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand">
                                    </div>
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="flex items-center gap-2 text-sm text-ink">
                                        <input type="checkbox" name="is_active" value="1" @checked($product->is_active) class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                                        Tayangkan produk
                                    </label>
                                    <button type="submit" class="btn-primary w-full">Simpan perubahan</button>
                                </form>

                                <form method="POST" action="{{ route('umkm.products.destroy', $product) }}" class="mt-2"
                                      onsubmit="return confirm('Hapus produk ini? Tindakan tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex w-full items-center justify-center gap-1 rounded-control px-3 py-2 text-sm font-semibold text-brand hover:bg-brand-soft">
                                        <x-icon name="trash" class="h-4 w-4" /> Hapus produk
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">{{ $products->links() }}</div>
    @endif
@endsection
