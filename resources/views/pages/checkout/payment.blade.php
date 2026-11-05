@extends('layouts.public')

@section('title', 'Pembayaran Simulasi')

@section('content')
    @php
        $isPending = $order->status === \App\Enums\OrderStatus::PendingPayment;
        $others = $pendingOrders->where('id', '!=', $order->id);
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('orders.index') }}" class="hover:text-brand">Pesanan</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Pembayaran</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-3xl font-extrabold text-ink">Pembayaran simulasi</h1>
                <p class="mt-1 text-ink-muted">Pesanan <span class="font-semibold text-ink">{{ $order->code }}</span> &middot; {{ $order->store->name }}</p>
            </div>
            <x-stepper :steps="['Keranjang', 'Checkout', 'Pembayaran']" :current="2" />
        </div>

        <x-simulation-banner class="mt-5" message="MODE SIMULASI: halaman ini meniru pembayaran. Tidak ada uang sungguhan yang diproses. Gunakan tombol di bawah untuk menyimulasikan hasil pembayaran." />

        <div class="mt-6 grid gap-4 md:grid-cols-[1fr_300px]">
            <section class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-bold text-ink">Rincian tagihan</h2>
                    <x-status-badge :status="$order->status->value" />
                </div>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Subtotal</dt>
                        <dd class="font-medium text-ink">{{ rupiah($order->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Ongkir simulasi</dt>
                        <dd class="font-medium text-ink">{{ rupiah($order->shipping_cost) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-mist pt-2 text-base font-bold text-ink">
                        <dt>Total tagihan</dt>
                        <dd class="text-brand">{{ rupiah($order->total) }}</dd>
                    </div>
                </dl>

                <h3 class="mt-5 text-sm font-bold text-ink">Item pesanan</h3>
                <ul class="mt-2 divide-y divide-mist/70 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-3 py-2">
                            <span class="min-w-0">
                                <span class="block truncate text-ink">{{ $item->product_name }}</span>
                                <span class="text-ink-muted">{{ $item->quantity }} &times; {{ rupiah($item->unit_price) }}</span>
                            </span>
                            <span class="font-semibold text-ink">{{ rupiah($item->subtotal()) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-5 rounded-control bg-canvas p-4 text-sm">
                    <p class="font-semibold text-ink">Kirim ke</p>
                    @php $addr = $order->shipping_address; @endphp
                    <p class="mt-1 text-ink-muted">{{ $addr['recipient'] ?? '-' }} &middot; {{ $addr['phone'] ?? '-' }}</p>
                    <p class="text-ink-muted">{{ $addr['address'] ?? '-' }}, {{ $addr['city'] ?? '-' }}</p>
                </div>
            </section>

            <aside class="space-y-4">
                <div class="card p-5">
                    <p class="text-sm font-semibold text-ink">Total yang harus dibayar</p>
                    <p class="mt-1 text-3xl font-extrabold text-brand">{{ rupiah($order->total) }}</p>

                    @if ($isPending)
                        @if ($order->expires_at)
                            <p class="mt-2 flex items-center gap-1 text-xs text-ink-muted">
                                <x-icon name="clock" class="h-3.5 w-3.5" /> Bayar sebelum {{ tanggal($order->expires_at) }}
                            </p>
                        @endif

                        <div class="mt-4 space-y-2">
                            <form method="POST" action="{{ route('checkout.pay', $order) }}">
                                @csrf
                                <input type="hidden" name="result" value="success">
                                <button type="submit" class="btn-primary w-full">
                                    <x-icon name="check" class="h-4 w-4" /> Simulasikan pembayaran berhasil
                                </button>
                            </form>

                            <form method="POST" action="{{ route('checkout.pay', $order) }}">
                                @csrf
                                <input type="hidden" name="result" value="failed">
                                <button type="submit" class="btn-secondary w-full text-brand">
                                    <x-icon name="x" class="h-4 w-4" /> Simulasikan pembayaran gagal
                                </button>
                            </form>

                            <form method="POST" action="{{ route('checkout.pay', $order) }}">
                                @csrf
                                <input type="hidden" name="result" value="expired">
                                <button type="submit" class="btn-secondary w-full text-ink-muted">
                                    <x-icon name="clock" class="h-4 w-4" /> Biarkan kedaluwarsa
                                </button>
                            </form>
                        </div>
                        <p class="mt-3 text-xs text-ink-muted">Jika pembayaran gagal atau kedaluwarsa, pesanan dibatalkan dan stok dikembalikan.</p>
                    @else
                        <a href="{{ route('checkout.done', $order) }}" class="btn-primary mt-4 w-full">Lihat hasil pesanan</a>
                    @endif

                    <a href="{{ route('orders.index') }}" class="mt-2 flex w-full items-center justify-center gap-1 text-sm font-semibold text-brand hover:underline">
                        <x-icon name="arrow-left" class="h-4 w-4" /> Daftar pesanan
                    </a>
                </div>

                @if ($others->isNotEmpty())
                    <div class="card p-5">
                        <p class="text-sm font-bold text-ink">Pesanan lain yang belum dibayar</p>
                        <p class="mt-1 text-xs text-ink-muted">Pesanan dari toko lain dibuat terpisah. Selesaikan satu per satu.</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($others as $other)
                                <li>
                                    <a href="{{ route('checkout.payment', $other) }}" class="flex items-center justify-between gap-2 rounded-control border border-mist px-3 py-2 text-sm hover:border-mist-deep">
                                        <span class="min-w-0">
                                            <span class="block truncate font-medium text-ink">{{ $other->store->name }}</span>
                                            <span class="text-xs text-ink-muted">{{ $other->code }}</span>
                                        </span>
                                        <span class="shrink-0 font-semibold text-brand">{{ rupiah($other->total) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
