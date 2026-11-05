@extends('layouts.public')

@section('title', 'Masuk')

@section('content')
    <div class="mx-auto grid max-w-5xl gap-8 px-4 py-10 md:grid-cols-2 md:items-center md:py-16">
        <section class="hidden md:block">
            <p class="inline-flex items-center gap-2 rounded-full border border-sand bg-sand-soft px-3 py-1 text-xs font-semibold text-ink">
                <x-icon name="info" class="h-4 w-4 text-brand" /> MODE SIMULASI
            </p>
            <h1 class="mt-4 text-4xl font-extrabold leading-tight text-ink">Satu akun untuk menjelajah UMKM lokal.</h1>
            <p class="mt-3 text-ink-muted">Masuk untuk melanjutkan belanja, memantau pesanan, atau mengelola toko Anda di UMKMHub.</p>
            <ul class="mt-6 space-y-2 text-sm text-ink-muted">
                <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-ok" /> Keranjang lintas toko</li>
                <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-ok" /> Pembayaran simulasi yang transparan</li>
                <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-ok" /> Dashboard untuk Admin UMKM</li>
            </ul>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-ink">Masuk ke akun</h2>
            <p class="mt-1 text-sm text-ink-muted">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-brand hover:underline">Daftar pembeli</a> atau <a href="{{ route('store-applications.create') }}" class="font-semibold text-brand hover:underline">daftarkan UMKM</a>.</p>

            @if ($errors->any())
                <div class="mt-4 flex items-start gap-2 rounded-control border border-brand/40 bg-brand-soft px-4 py-3 text-sm text-brand" role="alert">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Periksa kembali data yang Anda masukkan.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                           class="field @error('email') border-brand @enderror" placeholder="nama@email.com">
                    @error('email')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="field @error('password') border-brand @enderror" placeholder="Password Anda">
                    @error('password')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-ink-muted">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                    Ingat saya
                </label>

                <button type="submit" class="btn-primary w-full">Masuk</button>
            </form>

            <div class="mt-6 rounded-control border border-mist bg-canvas p-4 text-xs text-ink-muted">
                <p class="font-semibold text-ink">Akun demo (password: <code>password123</code>)</p>
                <ul class="mt-1 space-y-0.5">
                    <li>Superadmin &middot; superadmin@umkmhub.id</li>
                    <li>Admin UMKM &middot; sari@umkmhub.id</li>
                    <li>Pembeli &middot; dimas@gmail.com</li>
                </ul>
            </div>
        </section>
    </div>
@endsection
