@extends('layouts.dashboard')

@section('title', 'Persetujuan Toko')
@section('eyebrow', 'Superadmin')
@section('heading', 'Persetujuan pendaftaran')

@section('content')
    @php
        $statuses = ['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'suspended' => 'Disuspend'];
        $current = request('status', 'pending');
    @endphp

    <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filter status pendaftaran">
        @foreach ($statuses as $value => $label)
            <a href="{{ $value === '' ? route('admin.applications') : route('admin.applications', ['status' => $value]) }}"
               class="chip {{ $current === $value ? 'chip-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($stores->isEmpty())
        <x-empty-state class="mt-4" title="Tidak ada pendaftaran" description="Belum ada toko dengan status ini." />
    @else
        <div class="mt-4 space-y-3">
            @foreach ($stores as $store)
                <article class="card p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-ink">{{ $store->name }}</h2>
                                <x-status-badge :status="$store->status->value" />
                            </div>
                            <p class="mt-1 text-sm text-ink-muted">
                                {{ $store->owner->name }} &middot; {{ $store->owner->email }}
                            </p>
                        </div>
                        <p class="text-sm text-ink-muted">Diajukan {{ tanggal($store->submitted_at) }}</p>
                    </div>

                    <dl class="mt-3 grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-ink-muted">Kategori</dt><dd class="text-ink">{{ $store->category?->name ?? '-' }}</dd></div>
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-ink-muted">Kota</dt><dd class="text-ink">{{ $store->city }}</dd></div>
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-ink-muted">Telepon</dt><dd class="text-ink">{{ $store->phone }}</dd></div>
                        <div class="flex gap-2"><dt class="w-24 shrink-0 text-ink-muted">Alamat</dt><dd class="text-ink">{{ $store->address }}</dd></div>
                        <div class="flex gap-2 sm:col-span-2"><dt class="w-24 shrink-0 text-ink-muted">Deskripsi</dt><dd class="text-ink">{{ $store->description ?: '-' }}</dd></div>
                    </dl>

                    @if ($store->status === \App\Enums\StoreStatus::Pending)
                        <div class="mt-4 flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.stores.approve', $store) }}">
                                @csrf
                                <button type="submit" class="btn-primary h-9 px-3 text-sm">
                                    <x-icon name="check" class="h-4 w-4" /> Setujui
                                </button>
                            </form>

                            <details class="w-full sm:w-auto">
                                <summary class="btn-secondary h-9 cursor-pointer list-none px-3 text-sm text-brand">
                                    <x-icon name="x" class="h-4 w-4" /> Tolak
                                </summary>
                                <form method="POST" action="{{ route('admin.stores.reject', $store) }}" class="mt-2 rounded-control border border-mist bg-canvas p-3">
                                    @csrf
                                    <label for="reason-{{ $store->id }}" class="label">Alasan penolakan</label>
                                    <textarea id="reason-{{ $store->id }}" name="rejection_reason" rows="2" required class="field h-auto py-2" placeholder="Contoh: alamat tidak lengkap"></textarea>
                                    <button type="submit" class="btn-danger-ghost mt-2 h-9 px-3 text-sm">Kirim penolakan</button>
                                </form>
                            </details>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-4">{{ $stores->links() }}</div>
    @endif
@endsection
