@extends('layouts.dashboard')

@section('title', 'Ringkasan Toko')
@section('eyebrow', 'Dashboard UMKM')
@section('heading', $store->name)

@section('content')
    @php
        $maxSales = max($series['sales'] ?: [0]);
        $chartMax = $maxSales > 0 ? $maxSales : 1;
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">Ringkasan performa toko hari ini, {{ tanggal(now(), false) }}.</p>
        <div class="flex gap-2">
            <a href="{{ route('stores.show', $store->slug) }}" class="btn-secondary h-9 px-3 text-sm">
                <x-icon name="eye" class="h-4 w-4" /> Lihat toko
            </a>
            <a href="{{ route('umkm.products') }}" class="btn-primary h-9 px-3 text-sm">
                <x-icon name="plus" class="h-4 w-4" /> Tambah produk
            </a>
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Penjualan hari ini" :value="rupiah($stats['sales'])" icon="chart" accent />
        <x-stat-card label="Pesanan baru" :value="$stats['new_orders']" note="Perlu diproses" icon="inbox" />
        <x-stat-card label="Produk aktif" :value="$stats['active_products']" icon="box" />
        <x-stat-card label="Stok menipis" :value="$stats['low_stock']" note="Ambang {{ config('umkmhub.low_stock_threshold') }} unit" icon="alert" />
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-[1fr_320px]">
        <section class="card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-ink">Penjualan 7 hari</h2>
                    <p class="text-sm text-ink-muted">Total transaksi berstatus dibayar ke selesai.</p>
                </div>
                <p class="text-2xl font-extrabold text-brand">{{ rupiah(array_sum($series['sales'])) }}</p>
            </div>

            <div class="mt-6 flex h-48 items-end gap-2 sm:gap-3" role="img" aria-label="Grafik penjualan tujuh hari terakhir">
                @foreach ($series['labels'] as $index => $label)
                    @php $height = (int) round(($series['sales'][$index] / $chartMax) * 100); @endphp
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <span class="text-[10px] text-ink-muted">{{ $series['sales'][$index] > 0 ? number_format($series['sales'][$index] / 1000, 0, ',', '.') . 'k' : '' }}</span>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t bg-brand/80 transition-all hover:bg-brand" style="height: {{ max($height, $series['sales'][$index] > 0 ? 4 : 1) }}%"></div>
                        </div>
                        <span class="text-[11px] text-ink-muted">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card p-5">
            <h2 class="text-lg font-bold text-ink">Jumlah pesanan</h2>
            <p class="text-sm text-ink-muted">7 hari terakhir.</p>
            <ul class="mt-3 space-y-2">
                @foreach ($series['labels'] as $index => $label)
                    <li class="flex items-center justify-between gap-2 text-sm">
                        <span class="text-ink-muted">{{ $label }}</span>
                        <span class="inline-flex items-center gap-2">
                            <span class="h-1.5 w-16 overflow-hidden rounded-full bg-canvas">
                                <span class="block h-full bg-mist-deep" style="width: {{ min(100, $series['orders'][$index] * 25) }}%"></span>
                            </span>
                            <span class="w-6 text-right font-semibold text-ink">{{ $series['orders'][$index] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-mist px-5 py-4">
            <h2 class="text-lg font-bold text-ink">Pesanan terbaru</h2>
            <a href="{{ route('umkm.orders') }}" class="text-sm font-semibold text-brand hover:underline">Lihat semua</a>
        </div>

        @if ($latestOrders->isEmpty())
            <div class="p-5">
                <x-empty-state title="Belum ada pesanan" description="Pesanan masuk akan tampil di sini setelah pembeli melakukan checkout." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Pembeli</th>
                            <th scope="col">Item</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestOrders as $order)
                            <tr>
                                <td class="font-medium text-ink">{{ $order->code }}</td>
                                <td class="text-ink-muted">{{ $order->user->name }}</td>
                                <td class="text-ink-muted">{{ $order->items->sum('quantity') }}</td>
                                <td class="font-semibold text-ink">{{ rupiah($order->total) }}</td>
                                <td><x-status-badge :status="$order->status->value" /></td>
                                <td class="text-right">
                                    <a href="{{ route('umkm.orders.show', $order) }}" class="text-sm font-semibold text-brand hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
