@extends('layouts.public')

@section('title', 'Keranjang')

@section('content')
    @php
        $grandTotal = collect($groups)->sum('total');
        $itemCount = $cart->items->sum('quantity');
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Keranjang</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-3xl font-extrabold text-ink">Keranjang</h1>
                <p class="mt-1 text-ink-muted">{{ $itemCount }} item dari {{ count($groups) }} toko</p>
            </div>
            <x-stepper :steps="['Keranjang', 'Checkout', 'Pembayaran']" :current="0" />
        </div>

        @if ($cart->items->isEmpty())
            <x-empty-state class="mt-6" title="Keranjang masih kosong" description="Yuk mulai jelajahi produk UMKM lokal." action="Jelajahi produk" :actionUrl="route('products.index')" />
        @else
            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
                <div class="space-y-4">
                    @foreach ($groups as $group)
                        @php $storeItems = $cart->items->filter(fn ($item) => $item->product->store_id === $group['store']['id']); @endphp
                        <section class="card overflow-hidden">
                            <header class="flex items-center gap-2 border-b border-mist bg-white px-4 py-3">
                                <x-icon name="store" class="h-4 w-4 text-brand" />
                                <a href="{{ route('stores.show', $group['store']['slug']) }}" class="font-semibold text-ink hover:text-brand">{{ $group['store']['name'] }}</a>
                            </header>

                            <ul class="divide-y divide-mist/70">
                                @foreach ($storeItems as $item)
                                    @php $itemStock = (int) ($item->product->stock->quantity ?? 0); @endphp
                                    <li class="flex gap-3 p-4">
                                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-control bg-mist/30">
                                            @if ($item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full items-center justify-center text-lg font-bold text-brand/40">{{ mb_substr($item->product->name, 0, 1) }}</div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('products.show', $item->product) }}" class="line-clamp-2 text-sm font-semibold text-ink hover:text-brand">{{ $item->product->name }}</a>
                                            <p class="mt-1 text-sm font-bold text-brand">{{ rupiah($item->product->price) }}</p>
                                            @if ($item->quantity > $itemStock)
                                                <p class="mt-1 text-xs font-semibold text-brand">Stok tersisa {{ $itemStock }}. Kurangi jumlah sebelum checkout.</p>
                                            @endif

                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label for="qty-{{ $item->id }}" class="sr-only">Jumlah {{ $item->product->name }}</label>
                                                    <input id="qty-{{ $item->id }}" name="quantity" type="number" min="1" max="1000" value="{{ $item->quantity }}"
                                                           class="field h-9 w-20 px-2 py-1 text-sm">
                                                    <button type="submit" class="text-sm font-semibold text-brand hover:underline">Perbarui</button>
                                                </form>

                                                <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-sm text-ink-muted hover:text-brand">
                                                        <x-icon name="trash" class="h-4 w-4" /> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <p class="ml-auto shrink-0 text-sm font-bold text-ink">{{ rupiah($item->product->price * $item->quantity) }}</p>
                                    </li>
                                @endforeach
                            </ul>

                            <footer class="space-y-1 border-t border-mist bg-canvas px-4 py-3 text-sm">
                                <div class="flex justify-between text-ink-muted">
                                    <span>Subtotal</span>
                                    <span>{{ rupiah($group['subtotal']) }}</span>
                                </div>
                                <div class="flex justify-between text-ink-muted">
                                    <span>Ongkir simulasi</span>
                                    <span>{{ rupiah($group['shipping_cost']) }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-ink">
                                    <span>Total toko</span>
                                    <span>{{ rupiah($group['total']) }}</span>
                                </div>
                            </footer>
                        </section>
                    @endforeach
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start">
                    <div class="card p-5">
                        <h2 class="text-lg font-bold text-ink">Ringkasan</h2>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between text-ink-muted">
                                <dt>Jumlah item</dt>
                                <dd>{{ $itemCount }}</dd>
                            </div>
                            <div class="flex justify-between text-ink-muted">
                                <dt>Jumlah toko</dt>
                                <dd>{{ count($groups) }}</dd>
                            </div>
                            <div class="flex justify-between text-ink-muted">
                                <dt>Ongkir simulasi</dt>
                                <dd>{{ rupiah(collect($groups)->sum('shipping_cost')) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-mist pt-2 text-base font-bold text-ink">
                                <dt>Total</dt>
                                <dd>{{ rupiah($grandTotal) }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('checkout.index') }}" class="btn-primary mt-4 w-full">
                            Lanjut ke checkout <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                        <a href="{{ route('products.index') }}" class="mt-2 flex w-full items-center justify-center gap-1 text-sm font-semibold text-brand hover:underline">
                            <x-icon name="arrow-left" class="h-4 w-4" /> Lanjut belanja
                        </a>

                        <x-simulation-banner class="mt-4" message="MODE SIMULASI: total di atas bukan tagihan nyata." />
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
