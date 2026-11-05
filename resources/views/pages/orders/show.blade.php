@extends('layouts.public')

@section('title', 'Pesanan '.$order->code)

@section('content')
    @php
        $addr = $order->shipping_address;
        $canPay = $order->status === \App\Enums\OrderStatus::PendingPayment;
        $canCancel = $order->canBeCancelledByBuyer();
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('orders.index') }}" class="hover:text-brand">Pesanan</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">{{ $order->code }}</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-3xl font-extrabold text-ink">Detail pesanan</h1>
                <p class="mt-1 text-ink-muted">{{ $order->code }} &middot; {{ tanggal($order->created_at) }}</p>
            </div>
            <x-status-badge :status="$order->status->value" />
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-[1fr_300px]">
            <div class="space-y-4">
                <section class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-bold text-ink">Produk</h2>
                        <a href="{{ route('stores.show', $order->store->slug) }}" class="text-sm font-semibold text-brand hover:underline">{{ $order->store->name }}</a>
                    </div>

                    <ul class="mt-3 divide-y divide-mist/70 text-sm">
                        @foreach ($order->items as $item)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-control bg-brand-soft text-sm font-bold text-brand">
                                        {{ mb_substr($item->product_name, 0, 1) }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-ink">{{ $item->product_name }}</span>
                                        <span class="text-ink-muted">{{ $item->quantity }} &times; {{ rupiah($item->unit_price) }}</span>
                                    </span>
                                </span>
                                <span class="shrink-0 font-semibold text-ink">{{ rupiah($item->subtotal()) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="mt-4 space-y-2 border-t border-mist pt-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Subtotal</dt>
                            <dd class="text-ink">{{ rupiah($order->subtotal) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-muted">Ongkir simulasi</dt>
                            <dd class="text-ink">{{ rupiah($order->shipping_cost) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 text-base font-bold text-ink">
                            <dt>Total</dt>
                            <dd class="text-brand">{{ rupiah($order->total) }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="card p-5">
                    <h2 class="text-lg font-bold text-ink">Alamat pengiriman</h2>
                    <p class="mt-2 text-sm font-semibold text-ink">{{ $addr['recipient'] ?? '-' }}</p>
                    <p class="text-sm text-ink-muted">{{ $addr['phone'] ?? '-' }}</p>
                    <p class="text-sm text-ink-muted">{{ $addr['address'] ?? '-' }}, {{ $addr['city'] ?? '-' }}</p>
                    <p class="mt-2 text-xs text-ink-muted">Alamat ini adalah salinan saat pesanan dibuat.</p>
                </section>
            </div>

            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-ink">Pembayaran</h2>
                    <div class="mt-2 space-y-1 text-sm">
                        <p class="flex items-center justify-between gap-2">
                            <span class="text-ink-muted">Metode</span>
                            <span class="font-medium text-ink">Simulasi</span>
                        </p>
                        <p class="flex items-center justify-between gap-2">
                            <span class="text-ink-muted">Status</span>
                            <x-status-badge :status="$order->payment?->status->value ?? 'pending'" />
                        </p>
                        @if ($order->payment?->paid_at)
                            <p class="flex items-center justify-between gap-2">
                                <span class="text-ink-muted">Dibayar</span>
                                <span class="text-ink">{{ tanggal($order->payment->paid_at) }}</span>
                            </p>
                        @endif
                        @if ($order->expires_at && $canPay)
                            <p class="flex items-center justify-between gap-2">
                                <span class="text-ink-muted">Batas bayar</span>
                                <span class="text-ink">{{ tanggal($order->expires_at) }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2">
                        @if ($canPay)
                            <a href="{{ route('checkout.payment', $order) }}" class="btn-primary w-full">Bayar simulasi</a>
                        @endif

                        @if ($canCancel)
                            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                                  onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan ke toko.');">
                                @csrf
                                <button type="submit" class="btn-secondary w-full text-brand">Batalkan pesanan</button>
                            </form>
                        @endif

                        <a href="{{ route('orders.index') }}" class="flex w-full items-center justify-center gap-1 text-sm font-semibold text-brand hover:underline">
                            <x-icon name="arrow-left" class="h-4 w-4" /> Semua pesanan
                        </a>
                    </div>
                </div>

                <x-simulation-banner message="MODE SIMULASI: status dan pembayaran di halaman ini adalah bagian dari simulasi." />
            </aside>
        </div>
    </div>
@endsection
