@extends('layouts.dashboard')

@section('title', 'Daftar Toko')
@section('eyebrow', 'Superadmin')
@section('heading', 'Daftar toko')

@section('content')
    <form method="GET" action="{{ route('admin.stores') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="search" class="label">Cari toko</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama toko" class="field">
        </div>
        <div class="sm:w-48">
            <label for="status" class="label">Status</label>
            <select id="status" name="status" class="field">
                <option value="">Semua status</option>
                @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'suspended' => 'Disuspend'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary h-11">Terapkan</button>
    </form>

    @if ($stores->isEmpty())
        <x-empty-state class="mt-4" title="Toko tidak ditemukan" description="Coba ubah kata kunci atau filter status." />
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Toko</th>
                            <th scope="col">Pemilik</th>
                            <th scope="col">Kota</th>
                            <th scope="col">Status</th>
                            <th scope="col">Diajukan</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stores as $store)
                            <tr>
                                <td>
                                    <p class="font-medium text-ink">{{ $store->name }}</p>
                                    <p class="text-xs text-ink-muted">{{ $store->category?->name ?? 'Tanpa kategori' }}</p>
                                </td>
                                <td class="text-ink-muted">{{ $store->owner->name }}</td>
                                <td class="text-ink-muted">{{ $store->city }}</td>
                                <td><x-status-badge :status="$store->status->value" /></td>
                                <td class="text-ink-muted">{{ tanggal($store->submitted_at, false) }}</td>
                                <td class="text-right">
                                    @if ($store->status === \App\Enums\StoreStatus::Approved)
                                        <form method="POST" action="{{ route('admin.stores.suspend', $store) }}"
                                              onsubmit="return confirm('Suspend toko ini? Produk tidak akan tampil di katalog.');">
                                            @csrf
                                            <button type="submit" class="rounded-control px-2 py-1 text-sm font-semibold text-brand hover:bg-brand-soft">Suspend</button>
                                        </form>
                                    @elseif ($store->status === \App\Enums\StoreStatus::Suspended)
                                        <form method="POST" action="{{ route('admin.stores.activate', $store) }}">
                                            @csrf
                                            <button type="submit" class="rounded-control px-2 py-1 text-sm font-semibold text-ok hover:bg-ok/10">Aktifkan</button>
                                        </form>
                                    @elseif ($store->status === \App\Enums\StoreStatus::Pending)
                                        <a href="{{ route('admin.applications') }}" class="rounded-control px-2 py-1 text-sm font-semibold text-brand hover:bg-brand-soft">Tinjau</a>
                                    @else
                                        <span class="text-xs text-ink-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $stores->links() }}</div>
    @endif
@endsection
