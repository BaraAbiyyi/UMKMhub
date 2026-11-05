@extends('layouts.public')

@section('title', 'Daftar Pembeli')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-10 md:py-16">
        <div class="card p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-ink">Buat akun pembeli</h1>
            <p class="mt-1 text-sm text-ink-muted">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk di sini</a>.</p>

            @if ($errors->any())
                <div class="mt-4 flex items-start gap-2 rounded-control border border-brand/40 bg-brand-soft px-4 py-3 text-sm text-brand" role="alert">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Periksa kembali data yang Anda masukkan.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}"
                           class="field @error('name') border-brand @enderror" placeholder="Nama Anda">
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

                <button type="submit" class="btn-primary w-full">Daftar</button>
            </form>

            <p class="mt-6 text-center text-sm text-ink-muted">
                Ingin berjualan? <a href="{{ route('store-applications.create') }}" class="font-semibold text-brand hover:underline">Daftarkan UMKM Anda</a>.
            </p>
        </div>
    </div>
@endsection
