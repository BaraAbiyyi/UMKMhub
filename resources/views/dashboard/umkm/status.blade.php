@extends('layouts.dashboard')

@section('title', 'Status Toko')
@section('eyebrow', 'Dashboard UMKM')
@section('heading', 'Status pendaftaran toko')

@section('content')
    @php
        $status = $store->status;
        $copy = match ($status) {
            \App\Enums\StoreStatus::Pending => [
                'title' => 'Pendaftaran sedang ditinjau',
                'text' => 'Superadmin akan memeriksa data toko Anda. Anda dapat melengkapi profil sementara menunggu.',
                'icon' => 'clock',
            ],
            \App\Enums\StoreStatus::Rejected => [
                'title' => 'Pendaftaran ditolak',
                'text' => $store->rejection_reason ?: 'Perbaiki data toko lalu ajukan ulang pendaftaran.',
                'icon' => 'alert',
            ],
            \App\Enums\StoreStatus::Suspended => [
                'title' => 'Toko disuspend',
                'text' => 'Toko Anda sementara dinonaktifkan oleh Superadmin. Hubungi Superadmin untuk informasi lebih lanjut.',
                'icon' => 'shield',
            ],
            default => [
                'title' => 'Toko aktif',
                'text' => 'Toko Anda sudah tayang di marketplace.',
                'icon' => 'check',
            ],
        };
    @endphp

    <div class="max-w-2xl">
        <div class="card p-6">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand">
                    <x-icon :name="$copy['icon']" class="h-6 w-6" />
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-ink">{{ $copy['title'] }}</h2>
                        <x-status-badge :status="$status->value" />
                    </div>
                    <p class="mt-2 text-ink-muted">{{ $copy['text'] }}</p>
                    <p class="mt-2 text-sm text-ink-muted">Toko <strong class="text-ink">{{ $store->name }}</strong> &middot; diajukan {{ tanggal($store->submitted_at, false) }}.</p>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('umkm.store') }}" class="btn-primary flex-1">Lengkapi profil toko</a>
                @if ($status === \App\Enums\StoreStatus::Rejected)
                    <form method="POST" action="{{ route('umkm.store.reapply') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-secondary w-full">Ajukan ulang pendaftaran</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-4 rounded-control border border-mist bg-white p-5">
            <h3 class="font-bold text-ink">Apa yang bisa dilakukan sekarang?</h3>
            <ul class="mt-2 space-y-1 text-sm text-ink-muted">
                <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-ok" /> Melengkapi profil toko dan logo</li>
                <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-ok" /> Menyiapkan produk (dapat dikelola setelah disetujui)</li>
                <li class="flex items-center gap-2"><x-icon name="x" class="h-4 w-4 text-ink-muted" /> Menerima pesanan sebelum toko disetujui</li>
            </ul>
        </div>

        <x-simulation-banner class="mt-4" message="MODE SIMULASI: persetujuan toko di UMKMHub adalah bagian dari simulasi platform." />
    </div>
@endsection
