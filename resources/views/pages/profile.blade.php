@extends('layouts.public')

@section('title', 'Akun Saya')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Akun</span>
        </nav>

        <div class="mt-4">
            <h1 class="text-3xl font-extrabold text-ink">Akun saya</h1>
            <p class="mt-1 text-ink-muted">Kelola data akun dan alamat pengiriman.</p>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-[280px_1fr]">
            <section class="card h-fit p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <x-icon name="user" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-ink">{{ $user->name }}</p>
                        <p class="truncate text-sm text-ink-muted">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="mt-4 border-t border-mist pt-3 text-sm">
                    <p class="flex items-center justify-between gap-2">
                        <span class="text-ink-muted">Peran</span>
                        <span class="font-medium text-ink">{{ $user->role->label() }}</span>
                    </p>
                </div>
                <a href="{{ route('orders.index') }}" class="btn-secondary mt-4 w-full">Pesanan saya</a>
            </section>

            <div class="space-y-4">
                <section class="card p-5">
                    <h2 class="text-lg font-bold text-ink">Alamat pengiriman</h2>
                    <p class="mt-1 text-sm text-ink-muted">Alamat utama dipakai default saat checkout.</p>

                    @if ($addresses->isEmpty())
                        <p class="mt-3 rounded-control border border-dashed border-mist bg-canvas px-4 py-3 text-sm text-ink-muted">Belum ada alamat. Tambahkan alamat pertama Anda di bawah.</p>
                    @else
                        <ul class="mt-3 space-y-2">
                            @foreach ($addresses as $address)
                                <li class="rounded-control border border-mist p-3">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0 text-sm">
                                            <p class="flex flex-wrap items-center gap-2 font-semibold text-ink">
                                                {{ $address->recipient }}
                                                @if ($address->is_default)
                                                    <span class="rounded-full bg-brand-soft px-2 py-0.5 text-xs font-semibold text-brand">Utama</span>
                                                @endif
                                            </p>
                                            <p class="text-ink-muted">{{ $address->phone }}</p>
                                            <p class="text-ink-muted">{{ $address->address }}, {{ $address->city }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}"
                                              onsubmit="return confirm('Hapus alamat ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 text-sm text-ink-muted hover:text-brand">
                                                <x-icon name="trash" class="h-4 w-4" /> Hapus
                                            </button>
                                        </form>
                                    </div>

                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-sm font-semibold text-brand">Ubah alamat</summary>
                                        <form method="POST" action="{{ route('profile.addresses.update', $address) }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label for="recipient-{{ $address->id }}" class="label">Penerima</label>
                                                <input id="recipient-{{ $address->id }}" name="recipient" type="text" required value="{{ $address->recipient }}" class="field">
                                            </div>
                                            <div>
                                                <label for="phone-{{ $address->id }}" class="label">Telepon</label>
                                                <input id="phone-{{ $address->id }}" name="phone" type="tel" required value="{{ $address->phone }}" class="field">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label for="address-{{ $address->id }}" class="label">Alamat</label>
                                                <input id="address-{{ $address->id }}" name="address" type="text" required value="{{ $address->address }}" class="field">
                                            </div>
                                            <div>
                                                <label for="city-{{ $address->id }}" class="label">Kota</label>
                                                <input id="city-{{ $address->id }}" name="city" type="text" required value="{{ $address->city }}" class="field">
                                            </div>
                                            <label class="flex items-end gap-2 pb-3 text-sm text-ink-muted">
                                                <input type="checkbox" name="is_default" value="1" @checked($address->is_default) class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                                                Jadikan alamat utama
                                            </label>
                                            <div class="sm:col-span-2">
                                                <button type="submit" class="btn-primary">Simpan perubahan</button>
                                            </div>
                                        </form>
                                    </details>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card p-5">
                    <h2 class="text-lg font-bold text-ink">Tambah alamat baru</h2>
                    <form method="POST" action="{{ route('profile.addresses.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <div>
                            <label for="recipient" class="label">Nama penerima</label>
                            <input id="recipient" name="recipient" type="text" required value="{{ old('recipient') }}" class="field @error('recipient') border-brand @enderror" placeholder="Nama penerima">
                            @error('recipient')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="label">Nomor telepon</label>
                            <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" class="field @error('phone') border-brand @enderror" placeholder="0812xxxxxxx">
                            @error('phone')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="label">Alamat lengkap</label>
                            <input id="address" name="address" type="text" required value="{{ old('address') }}" class="field @error('address') border-brand @enderror" placeholder="Jalan, nomor, kelurahan">
                            @error('address')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="city" class="label">Kota</label>
                            <input id="city" name="city" type="text" required value="{{ old('city') }}" class="field @error('city') border-brand @enderror" placeholder="Contoh: Malang">
                            @error('city')<p class="mt-1 text-sm text-brand">{{ $message }}</p>@enderror
                        </div>
                        <label class="flex items-end gap-2 pb-3 text-sm text-ink-muted">
                            <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                            Jadikan alamat utama
                        </label>
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn-primary">Tambah alamat</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
