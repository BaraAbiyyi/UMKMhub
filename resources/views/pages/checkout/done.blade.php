@extends('layouts.public')

@section('title', 'Hasil Pesanan')

@section('content')
    @php
        $paid = $order->status === \App\Enums\OrderStatus::Paid;
        $cancelled = $order->status === \App\Enums\OrderStatus::Cancelled;
    @endphp

    <div class="mx-auto max-w-2xl px-4 py-10">
        <div class="card overflow-hidden">
            <div class="border-b border-mist p-6 text-center">
                @if ($paid)
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-ok/10 text-ok">
                        <x-icon name="check" class="h-8 w-8" />
                    </span>
                    <h1 class="mt-3 text-2xl font-extrabold text-ink">Pembayaran simulasi berhasil</h1>
                    <p class="mt-1 text-sm text-ink-muted">Pesanan Anda diteruskan ke toko. Ini hanya simulasi, tidak ada uang sungguhan.</p>
                @elseif ($cancelled)
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <x-icon name="x" class="h-8 w-8" />
                    </span>
                    <h1 class="mt-3 text-2xl font-extrabold text-ink">Pesanan dibatalkan</h1>
                    <p class="mt-1 text-sm text-ink-muted">
                        {{ $order->payment?->status === \App\Enums\PaymentStatus::Expired ? 'Batas waktu pembayaran simulasi telah lewat. Stok sudah dikembalikan ke toko.' : 'Pembayaran simulasi tidak berhasil. Stok sudah dikembalikan ke toko.' }}
                    </p>
                @else
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sand-soft text-sand">
                        <x-icon name="clock" class="h-8 w-8" />
                    </span>
                    <h1 class="mt-3 text-2xl font-extrabold text-ink">Pesanan menunggu pembayaran</h1>
                    <p class="mt-1 text-sm text-ink-muted">Selesaikan pembayaran simulasi agar pesanan diproses toko.</p>
                @endif
            </div>

            <div class="p-6">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Kode pesanan</dt>
                        <dd class="font-semibold text-ink">{{ $order->code }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Toko</dt>
                        <dd class="text-ink">{{ $order->store->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-ink-muted">Status</dt>
                        <dd><x-status-badge :status="$order->status->value" /></dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-mist pt-2 text-base font-bold text-ink">
                        <dt>Total</dt>
                        <dd class="text-brand">{{ rupiah($order->total) }}</dd>
                    </div>
                </dl>

                <ul class="mt-4 divide-y divide-mist/70 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-3 py-2">
                            <span class="min-w-0 truncate text-ink">{{ $item->product_name }} <span class="text-ink-muted">&times;{{ $item->quantity }}</span></span>
                            <span class="font-semibold text-ink">{{ rupiah($item->subtotal()) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('orders.show', $order) }}" class="btn-primary flex-1">Lihat detail pesanan</a>
                    <a href="{{ route('products.index') }}" class="btn-secondary flex-1">Lanjut belanja</a>
                </div>

                @if ($paid)
                    <p class="mt-4 text-center text-xs text-ink-muted">Simpan kode pesanan untuk memudahkan pengecekan status.</p>
                @elseif ($cancelled)
                    <p class="mt-4 text-center text-xs text-ink-muted">Anda bisa membuat pesanan baru dari keranjang atau katalog.</p>
                @endif
            </div>
        </div>

        <x-simulation-banner class="mt-4" message="MODE SIMULASI: seluruh alur pesanan di UMKMHub adalah simulasi. Tidak ada pengiriman atau pembayaran nyata." />
    </div>
@endsection
