@extends('layouts.dashboard')

@section('title', 'Profil Toko')
@section('eyebrow', 'Dashboard UMKM')
@section('heading', 'Profil toko')

@section('content')
    @php $logo = $store->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($store->logo_path) : null; @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">Informasi ini tampil di halaman publik toko Anda.</p>
        <div class="flex items-center gap-2">
            <x-status-badge :status="$store->status->value" />
            @if ($store->isApproved())
                <a href="{{ route('stores.show', $store->slug) }}" class="btn-secondary h-9 px-3 text-sm">
                    <x-icon name="eye" class="h-4 w-4" /> Lihat toko
                </a>
            @endif
        </div>
    </div>

    @if ($store->status === \App\Enums\StoreStatus::Rejected)
        <div class="mt-4 rounded-control border border-brand/40 bg-brand-soft px-4 py-3 text-sm text-brand">
            <p class="font-semibold">Pendaftaran ditolak.</p>
            @if ($store->rejection_reason)
                <p class="mt-1">Alasan: {{ $store->rejection_reason }}</p>
            @endif
            <form method="POST" action="{{ route('umkm.store.reapply') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn-primary h-9 px-3 text-sm">Ajukan ulang pendaftaran</button>
            </form>
        </div>
    @elseif ($store->status === \App\Enums\StoreStatus::Pending)
        <div class="mt-4 rounded-control border border-sand bg-sand-soft px-4 py-3 text-sm text-ink">
            Pendaftaran Anda sedang ditinjau Superadmin. Anda masih dapat melengkapi profil toko.
        </div>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-[280px_1fr]">
        <section class="card h-fit p-5">
            <div class="flex flex-col items-center text-center">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo {{ $store->name }}" class="h-24 w-24 rounded-card border border-mist object-cover">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-card bg-brand-soft text-3xl font-extrabold text-brand">{{ mb_substr($store->name, 0, 1) }}</div>
                @endif
                <p class="mt-3 font-bold text-ink">{{ $store->name }}</p>
                <p class="text-sm text-ink-muted">{{ $store->category?->name ?? 'Tanpa kategori' }}</p>
                <p class="mt-1 text-sm text-ink-muted">{{ $store->city }}</p>
                <p class="mt-2 text-xs text-ink-muted">Slug: {{ $store->slug }}</p>
            </div>
        </section>

        <section class="card p-5">
            <h2 class="text-lg font-bold text-ink">Ubah profil</h2>
            <form method="POST" action="{{ route('umkm.store.update') }}" enctype="multipart/form-data" class="mt-3 grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')

                <div class="sm:col-span-2">
                    <label for="store_name" class="label">Nama toko</label>
                    <input id="store_name" name="store_name" type="text" required value="{{ old('store_name', $store->name) }}" class="field @error('store_name') border-brand @enderror">
                    @error('store_name')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="label">Kategori usaha</label>
                    <select id="category_id" name="category_id" class="field">
                        <option value="">Tanpa kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $store->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="phone" class="label">Nomor telepon</label>
                    <input id="phone" name="phone" type="tel" required value="{{ old('phone', $store->phone) }}" class="field @error('phone') border-brand @enderror">
                    @error('phone')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="city" class="label">Kota</label>
                    <input id="city" name="city" type="text" required value="{{ old('city', $store->city) }}" class="field @error('city') border-brand @enderror">
                    @error('city')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="logo" class="label">Ganti logo (opsional)</label>
                    <input id="logo" name="logo" type="file" accept="image/*" class="block w-full text-sm text-ink-muted file:mr-3 file:rounded-control file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand">
                    @error('logo')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="label">Alamat toko</label>
                    <input id="address" name="address" type="text" required value="{{ old('address', $store->address) }}" class="field @error('address') border-brand @enderror">
                    @error('address')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="label">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" class="field h-auto py-2 @error('description') border-brand @enderror">{{ old('description', $store->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary">Simpan profil</button>
                </div>
            </form>
        </section>
    </div>
@endsection
