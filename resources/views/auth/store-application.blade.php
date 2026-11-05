@extends('layouts.public')

@section('title', 'Daftar UMKM')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 md:py-14">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 rounded-full border border-sand bg-sand-soft px-3 py-1 text-xs font-semibold text-ink">
                <x-icon name="store" class="h-4 w-4 text-brand" /> Untuk pemilik usaha
            </p>
            <h1 class="mt-3 text-3xl font-extrabold text-ink">Daftarkan UMKM Anda</h1>
            <p class="mt-2 text-ink-muted">Isi data akun dan toko. Pendaftaran akan ditinjau Superadmin sebelum toko tampil di marketplace.</p>
        </div>

        @if ($errors->any())
            <div class="mt-5 flex items-start gap-2 rounded-control border border-brand/40 bg-brand-soft px-4 py-3 text-sm text-brand" role="alert">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>Periksa kembali data yang Anda masukkan. Beberapa kolom perlu diperbaiki.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('store-applications.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf

            <section class="card p-5 sm:p-6">
                <h2 class="text-lg font-bold text-ink">Akun Admin UMKM</h2>
                <p class="mt-1 text-sm text-ink-muted">Akun ini dipakai untuk mengelola toko dan produk.</p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="label">Nama lengkap</label>
                        <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}"
                               class="field @error('name') border-brand @enderror" placeholder="Nama pemilik">
                        @error('name')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                               class="field @error('email') border-brand @enderror" placeholder="nama@email.com">
                        @error('email')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="label">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="field @error('password') border-brand @enderror" placeholder="Minimal 8 karakter">
                        @error('password')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Ulangi password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="field" placeholder="Ketik ulang password">
                    </div>
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="text-lg font-bold text-ink">Profil Toko</h2>
                <p class="mt-1 text-sm text-ink-muted">Informasi ini akan tampil di halaman toko Anda.</p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="store_name" class="label">Nama toko</label>
                        <input id="store_name" name="store_name" type="text" required value="{{ old('store_name') }}"
                               class="field @error('store_name') border-brand @enderror" placeholder="Contoh: Keripik Bu Sari">
                        @error('store_name')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="category_id" class="label">Kategori usaha</label>
                        <select id="category_id" name="category_id" class="field @error('category_id') border-brand @enderror">
                            <option value="">Pilih kategori (opsional)</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="label">Nomor telepon</label>
                        <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}"
                               class="field @error('phone') border-brand @enderror" placeholder="0812xxxxxxx">
                        @error('phone')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="city" class="label">Kota</label>
                        <input id="city" name="city" type="text" required value="{{ old('city') }}"
                               class="field @error('city') border-brand @enderror" placeholder="Contoh: Malang">
                        @error('city')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="logo" class="label">Logo toko (opsional)</label>
                        <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-ink-muted file:mr-3 file:rounded-control file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand">
                        <p class="mt-1 text-xs text-ink-muted">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                        @error('logo')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">Alamat toko</label>
                        <input id="address" name="address" type="text" required value="{{ old('address') }}"
                               class="field @error('address') border-brand @enderror" placeholder="Jalan, nomor, kelurahan">
                        @error('address')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="label">Deskripsi singkat</label>
                        <textarea id="description" name="description" rows="3"
                                  class="field h-auto py-2 @error('description') border-brand @enderror"
                                  placeholder="Ceritakan produk unggulan dan keunikan toko Anda">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <x-simulation-banner message="MODE SIMULASI: pendaftaran dan operasional toko di UMKMHub berjalan dalam mode simulasi. Tidak ada transaksi uang sungguhan." />

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-ink-muted">Dengan mendaftar, Anda menyetujui bahwa data toko akan ditinjau Superadmin.</p>
                <button type="submit" class="btn-primary w-full sm:w-auto">Kirim pendaftaran</button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-ink-muted">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk di sini</a>.
        </p>
    </div>
@endsection
