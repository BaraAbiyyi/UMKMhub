@extends('layouts.dashboard')

@section('title', 'Kategori')
@section('eyebrow', 'Superadmin')
@section('heading', 'Kategori produk')

@section('content')
    <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
        <section class="card h-fit p-5">
            <h2 class="text-lg font-bold text-ink">Tambah kategori</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label for="name" class="label">Nama kategori</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}" class="field @error('name') border-brand @enderror" placeholder="Contoh: Makanan Ringan">
                    @error('name')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="label">Deskripsi (opsional)</label>
                    <textarea id="description" name="description" rows="2" class="field h-auto py-2">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="btn-primary w-full">Tambah kategori</button>
            </form>
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-mist px-5 py-4">
                <h2 class="text-lg font-bold text-ink">Daftar kategori</h2>
                <p class="text-sm text-ink-muted">{{ $categories->count() }} kategori tersedia.</p>
            </div>

            @if ($categories->isEmpty())
                <div class="p-5">
                    <x-empty-state title="Belum ada kategori" description="Tambahkan kategori agar Admin UMKM dapat mengelompokkan produk." />
                </div>
            @else
                <ul class="divide-y divide-mist/70">
                    @foreach ($categories as $category)
                        <li class="px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-ink">{{ $category->name }}</p>
                                    <p class="text-sm text-ink-muted">{{ $category->description ?: 'Tanpa deskripsi' }}</p>
                                    <p class="mt-1 text-xs text-ink-muted">{{ $category->products_count }} produk &middot; {{ $category->stores_count }} toko</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <details>
                                        <summary class="btn-secondary h-9 cursor-pointer list-none px-3 text-sm">
                                            <x-icon name="edit" class="h-4 w-4" /> Ubah
                                        </summary>
                                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-2 w-72 space-y-2 rounded-control border border-mist bg-canvas p-3">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label for="name-{{ $category->id }}" class="label">Nama</label>
                                                <input id="name-{{ $category->id }}" name="name" type="text" required value="{{ $category->name }}" class="field">
                                            </div>
                                            <div>
                                                <label for="description-{{ $category->id }}" class="label">Deskripsi</label>
                                                <textarea id="description-{{ $category->id }}" name="description" rows="2" class="field h-auto py-2">{{ $category->description }}</textarea>
                                            </div>
                                            <button type="submit" class="btn-primary h-9 w-full text-sm">Simpan</button>
                                        </form>
                                    </details>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Hapus kategori ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-control px-2 py-1 text-sm font-semibold text-brand hover:bg-brand-soft">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
