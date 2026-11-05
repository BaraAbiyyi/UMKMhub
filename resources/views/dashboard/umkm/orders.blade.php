@extends('layouts.dashboard')

@section('title', 'Pesanan Masuk')
@section('eyebrow', 'Dashboard UMKM')
@section('heading', 'Pesanan masuk')

@section('content')
    @php
        $statuses = [
            '' => 'Semua',
            'pending_payment' => 'Menunggu bayar',
            'paid' => 'Baru dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $current = $status ?? '';
    @endphp

    <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filter status pesanan">
        @foreach ($statuses as $value => $label)
            <a href="{{ $value === '' ? route('umkm.orders') : route('umkm.orders', ['status' => $value]) }}"
               class="chip {{ $current === $value ? 'chip-active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-empty-state class="mt-4" title="Belum ada pesanan" description="Pesanan dari pembeli akan muncul di sini setelah checkout." />
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Pembeli</th>
                            <th scope="col">Item</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Waktu</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="font-medium text-ink">{{ $order->code }}</td>
                                <td class="text-ink-muted">{{ $order->user->name }}</td>
                                <td class="text-ink-muted">{{ $order->items->sum('quantity') }}</td>
                                <td class="font-semibold text-ink">{{ rupiah($order->total) }}</td>
                                <td><x-status-badge :status="$order->status->value" /></td>
                                <td class="text-ink-muted">{{ tanggal($order->created_at) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('umkm.orders.show', $order) }}" class="text-sm font-semibold text-brand hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
