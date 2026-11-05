@extends('layouts.dashboard')

@section('title', 'Pesanan Platform')
@section('eyebrow', 'Superadmin')
@section('heading', 'Semua pesanan')

@section('content')
    @php
        $statuses = [
            '' => 'Semua',
            'pending_payment' => 'Menunggu bayar',
            'paid' => 'Dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $current = $status ?? '';
    @endphp

    <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filter status pesanan">
        @foreach ($statuses as $value => $label)
            <a href="{{ $value === '' ? route('admin.orders') : route('admin.orders', ['status' => $value]) }}"
               class="chip {{ $current === $value ? 'chip-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-empty-state class="mt-4" title="Belum ada pesanan" description="Pesanan dari seluruh toko akan tampil di sini." />
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Toko</th>
                            <th scope="col">Pembeli</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="font-medium text-ink">{{ $order->code }}</td>
                                <td class="text-ink-muted">{{ $order->store->name }}</td>
                                <td class="text-ink-muted">{{ $order->user->name }}</td>
                                <td class="font-semibold text-ink">{{ rupiah($order->total) }}</td>
                                <td><x-status-badge :status="$order->status->value" /></td>
                                <td class="text-ink-muted">{{ tanggal($order->created_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
