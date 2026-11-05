@extends('layouts.dashboard')

@section('title', 'Pesanan '.$order->code)
@section('eyebrow', 'Dashboard UMKM')
@section('heading', 'Detail pesanan')

@section('content')
    @php
        $addr = $order->shipping_address;
        $nextStatuses = $order->status->allowedNextStatuses();
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('umkm.orders') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke pesanan
        </a>
        <x-status-badge :status="$order->status->value" />
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            <section class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-lg font-bold text-ink">{{ $order->code }}</h2>
                        <p class="text-sm text-ink-muted">Dibuat {{ tanggal($order->created_at) }}</p>
                    </div>
                    <p class="text-2xl font-extrabold text-brand">{{ rupiah($order->total) }}</p>
                </div>

                <ul class="mt-4 divide-y divide-mist/70 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-ink">{{ $item->product_name }}</span>
                                <span class="text-ink-muted">{{ $item->quantity }} &times; {{ rupiah($item->unit_price) }}</span>
                            </span>
                            <span class="shrink-0 font-semibold text-ink">{{ rupiah($item->subtotal()) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-2 space-y-1 border-t border-mist pt-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">Subtotal</dt><dd class="text-ink">{{ rupiah($order->subtotal) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">Ongkir simulasi</dt><dd class="text-ink">{{ rupiah($order->shipping_cost) }}</dd></div>
                    <div class="flex justify-between gap-3 font-bold text-ink"><dt>Total</dt><dd class="text-brand">{{ rupiah($order->total) }}</dd></div>
                </dl>
            </section>

            <section class="card p-5">
                <h2 class="text-lg font-bold text-ink">Data pembeli</h2>
                <p class="mt-2 text-sm font-semibold text-ink">{{ $order->user->name }}</p>
                <p class="text-sm text-ink-muted">{{ $order->user->email }}</p>

                <h3 class="mt-4 text-sm font-bold text-ink">Alamat pengiriman</h3>
                <p class="text-sm text-ink-muted">{{ $addr['recipient'] ?? '-' }} &middot; {{ $addr['phone'] ?? '-' }}</p>
                <p class="text-sm text-ink-muted">{{ $addr['address'] ?? '-' }}, {{ $addr['city'] ?? '-' }}</p>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="card p-5">
                <h2 class="text-sm font-bold text-ink">Ubah status</h2>
                @if (empty($nextStatuses))
                    <p class="mt-2 text-sm text-ink-muted">Pesanan ini sudah berada di status akhir dan tidak dapat diubah.</p>
                @else
                    <form method="POST" action="{{ route('umkm.orders.status', $order) }}" class="mt-3 space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status" class="label">Status berikutnya</label>
                            <select id="status" name="status" class="field">
                                @foreach ($nextStatuses as $next)
                                    <option value="{{ $next->value }}">{{ $next->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary w-full">Simpan status</button>
                    </form>
                    <p class="mt-2 text-xs text-ink-muted">Alur status: dibayar &rarr; diproses &rarr; dikirim &rarr; selesai.</p>
                @endif
            </section>

            <section class="card p-5">
                <h2 class="text-sm font-bold text-ink">Pembayaran simulasi</h2>
                <div class="mt-2 space-y-1 text-sm">
                    <p class="flex justify-between gap-2"><span class="text-ink-muted">Status</span><x-status-badge :status="$order->payment?->status->value ?? 'pending'" /></p>
                    @if ($order->payment?->paid_at)
                        <p class="flex justify-between gap-2"><span class="text-ink-muted">Dibayar</span><span class="text-ink">{{ tanggal($order->payment->paid_at) }}</span></p>
                    @endif
                    <p class="flex justify-between gap-2"><span class="text-ink-muted">Metode</span><span class="text-ink">Simulasi</span></p>
                </div>
            </section>

            <x-simulation-banner message="MODE SIMULASI: jangan memproses pengiriman nyata berdasarkan pesanan ini." />
        </aside>
    </div>
@endsection
