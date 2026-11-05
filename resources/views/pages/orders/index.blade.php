@extends('layouts.public')

@section('title', 'Pesanan Saya')

@section('content')
    @php
        $statuses = [
            '' => 'Semua',
            'pending_payment' => 'Menunggu pembayaran',
            'paid' => 'Sudah dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $current = request('status', '');
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-8">
        <nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
            <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
            <span aria-hidden="true"> / </span>
            <span class="text-ink">Pesanan</span>
        </nav>

        <div class="mt-4">
            <h1 class="text-3xl font-extrabold text-ink">Pesanan saya</h1>
            <p class="mt-1 text-ink-muted">Pantau status pesanan simulasi Anda dari setiap toko.</p>
        </div>

        <div class="mt-4 flex flex-wrap gap-2" role="navigation" aria-label="Filter status pesanan">
            @foreach ($statuses as $value => $label)
                <a href="{{ $value === '' ? route('orders.index') : route('orders.index', ['status' => $value]) }}"
                   class="chip {{ $current === $value ? 'chip-active' : '' }}"
                   @if ($current === $value) aria-current="true" @endif>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <x-empty-state class="mt-6" title="Belum ada pesanan" description="Pesanan yang Anda buat akan tampil di sini." action="Mulai belanja" :actionUrl="route('products.index')" />
        @else
            <div class="mt-6 space-y-3">
                @foreach ($orders as $order)
                    <article class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-mist bg-canvas px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-ink">{{ $order->store->name }}</p>
                                <p class="text-xs text-ink-muted">{{ $order->code }} &middot; {{ tanggal($order->created_at) }}</p>
                            </div>
                            <x-status-badge :status="$order->status->value" />
                        </div>

                        <div class="flex flex-wrap items-center gap-3 px-4 py-3">
                            <div class="flex min-w-0 flex-1 items-center gap-2">
                                @foreach ($order->items->take(3) as $item)
                                    <span class="flex h-10 w-10 items-center justify-center rounded-control bg-brand-soft text-sm font-bold text-brand" title="{{ $item->product_name }}">
                                        {{ mb_substr($item->product_name, 0, 1) }}
                                    </span>
                                @endforeach
                                <p class="min-w-0 truncate text-sm text-ink-muted">
                                    {{ $order->items->sum('quantity') }} item
                                </p>
                            </div>
                            <p class="font-bold text-brand">{{ rupiah($order->total) }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2 border-t border-mist px-4 py-3">
                            <a href="{{ route('orders.show', $order) }}" class="btn-secondary h-9 px-3 text-sm">Detail</a>
                            @if ($order->status === \App\Enums\OrderStatus::PendingPayment)
                                <a href="{{ route('checkout.payment', $order) }}" class="btn-primary h-9 px-3 text-sm">Bayar simulasi</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
