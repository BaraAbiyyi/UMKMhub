@extends('layouts.public')

@section('title', 'Checkout')

@section('content')
    @php $grandTotal = collect($groups)->sum('total'); @endphp

    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('cart.index') }}" class="hover:text-brand">Keranjang</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Checkout</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-3xl font-extrabold text-ink">Checkout</h1>
                <p class="mt-1 text-ink-muted">Pesanan dipisah otomatis per toko.</p>
            </div>
            <x-stepper :steps="['Keranjang', 'Checkout', 'Pembayaran']" :current="1" />
        </div>

        <x-simulation-banner class="mt-5" message="MODE SIMULASI: checkout ini tidak membuat tagihan nyata. Pembayaran dilakukan pada langkah berikutnya dan juga disimulasikan." />

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
            @csrf

            <div class="space-y-4">
                <section class="card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-ink">Alamat pengiriman</h2>
                        <a href="{{ route('profile.index') }}" class="text-sm font-semibold text-brand hover:underline">Kelola alamat</a>
                    </div>

                    @error('address_id')
                        <p class="mt-2 text-sm text-brand">{{ $message }}</p>
                    @enderror

                    @if ($addresses->isEmpty())
                        <div class="mt-3 rounded-control border border-dashed border-mist bg-canvas p-5 text-center">
                            <p class="text-sm font-semibold text-ink">Belum ada alamat tersimpan.</p>
                            <p class="mt-1 text-sm text-ink-muted">Tambahkan alamat pengiriman terlebih dahulu, lalu kembali ke checkout.</p>
                            <a href="{{ route('profile.index') }}" class="btn-primary mt-3">Tambah alamat</a>
                        </div>
                    @else
                        <div class="mt-3 space-y-2">
                            @foreach ($addresses as $address)
                                <label class="flex cursor-pointer items-start gap-3 rounded-control border border-mist p-3 text-sm hover:border-mist-deep has-[:checked]:border-brand has-[:checked]:bg-brand-soft/50">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" required
                                           @checked(old('address_id', $addresses->first()->id) == $address->id)
                                           class="mt-1 h-4 w-4 border-mist text-brand focus:ring-brand">
                                    <span class="min-w-0">
                                        <span class="flex flex-wrap items-center gap-2 font-semibold text-ink">
                                            {{ $address->recipient }}
                                            @if ($address->is_default)
                                                <span class="rounded-full bg-brand-soft px-2 py-0.5 text-xs text-brand">Utama</span>
                                            @endif
                                        </span>
                                        <span class="block text-ink-muted">{{ $address->phone }}</span>
                                        <span class="block text-ink-muted">{{ $address->address }}, {{ $address->city }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="card overflow-hidden">
                    <h2 class="border-b border-mist px-5 py-4 text-lg font-bold text-ink">Rincian pesanan</h2>

                    @foreach ($groups as $group)
                        @php $storeItems = $cart->items->filter(fn ($item) => $item->product->store_id === $group['store']['id']); @endphp
                        <div class="border-b border-mist/70 last:border-0">
                            <div class="flex items-center justify-between gap-2 bg-canvas px-5 py-2 text-sm">
                                <span class="inline-flex items-center gap-1 font-semibold text-ink">
                                    <x-icon name="store" class="h-4 w-4 text-brand" /> {{ $group['store']['name'] }}
                                </span>
                                <span class="text-ink-muted">Total {{ rupiah($group['total']) }}</span>
                            </div>
                            <ul class="divide-y divide-mist/60">
                                @foreach ($storeItems as $item)
                                    <li class="flex items-center gap-3 px-5 py-3 text-sm">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate font-medium text-ink">{{ $item->product->name }}</span>
                                            <span class="text-ink-muted">{{ $item->quantity }} &times; {{ rupiah($item->product->price) }}</span>
                                        </span>
                                        <span class="font-semibold text-ink">{{ rupiah($item->product->price * $item->quantity) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="px-5 pb-3 text-xs text-ink-muted">Ongkir simulasi {{ rupiah($group['shipping_cost']) }}</p>
                        </div>
                    @endforeach
                </section>
            </div>

            <aside class="lg:sticky lg:top-20 lg:self-start">
                <div class="card p-5">
                    <h2 class="text-lg font-bold text-ink">Ringkasan pembayaran</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        @foreach ($groups as $group)
                            <div class="flex justify-between gap-2 text-ink-muted">
                                <dt class="truncate">{{ $group['store']['name'] }}</dt>
                                <dd>{{ rupiah($group['total']) }}</dd>
                            </div>
                        @endforeach
                        <div class="flex justify-between border-t border-mist pt-2 text-base font-bold text-ink">
                            <dt>Total</dt>
                            <dd>{{ rupiah($grandTotal) }}</dd>
                        </div>
                    </dl>

                    <button type="submit" class="btn-primary mt-4 w-full" @disabled($addresses->isEmpty())>
                        Buat pesanan
                    </button>
                    <a href="{{ route('cart.index') }}" class="mt-2 flex w-full items-center justify-center gap-1 text-sm font-semibold text-brand hover:underline">
                        <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke keranjang
                    </a>

                    <p class="mt-4 text-xs text-ink-muted">Setelah pesanan dibuat, Anda memiliki waktu {{ config('umkmhub.payment_expiry_minutes') }} menit untuk menyelesaikan pembayaran simulasi sebelum pesanan kedaluwarsa.</p>
                </div>
            </aside>
        </form>
    </div>
@endsection
