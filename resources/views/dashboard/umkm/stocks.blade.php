@extends('layouts.dashboard')

@section('title', 'Stok')
@section('eyebrow', 'Dashboard UMKM')
@section('heading', 'Stok produk')

@section('content')
    @php $conflictId = session('conflict_stock_id'); @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">Perbarui jumlah stok. Setiap perubahan menaikkan versi untuk mencegah benturan data.</p>
        <form method="GET" action="{{ route('umkm.stocks') }}" class="flex items-center gap-2">
            <label class="inline-flex items-center gap-2 text-sm text-ink-muted">
                <input type="checkbox" name="low_stock" value="1" @checked(! empty($filters['low_stock'])) onchange="this.form.submit()" class="h-4 w-4 rounded border-mist text-brand focus:ring-brand">
                Hanya stok menipis (&le; {{ config('umkmhub.low_stock_threshold') }})
            </label>
            <noscript><button type="submit" class="btn-secondary h-9 px-3 text-sm">Terapkan</button></noscript>
        </form>
    </div>

    @if ($stocks->isEmpty())
        <x-empty-state class="mt-4" title="Belum ada data stok" description="Stok dibuat otomatis saat Anda menambahkan produk." action="Kelola produk" :actionUrl="route('umkm.products')" />
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Produk</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Stok</th>
                            <th scope="col">Versi</th>
                            <th scope="col">Perbarui</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stocks as $stock)
                            @php $low = $stock->quantity <= (int) config('umkmhub.low_stock_threshold'); @endphp
                            <tr class="{{ (int) $conflictId === $stock->id ? 'bg-brand-soft' : '' }}">
                                <td>
                                    <p class="font-medium text-ink">{{ $stock->product->name }}</p>
                                    @if ($conflictId && (int) $conflictId === $stock->id)
                                        <p class="text-xs font-semibold text-brand">Versi berubah. Muat ulang halaman lalu coba lagi.</p>
                                    @endif
                                </td>
                                <td class="text-ink-muted">{{ $stock->product->category?->name ?? '-' }}</td>
                                <td>
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $low ? 'text-brand' : 'text-ink' }}">
                                        @if ($low)<x-icon name="alert" class="h-4 w-4" />@endif
                                        {{ $stock->quantity }}
                                    </span>
                                </td>
                                <td class="text-ink-muted">v{{ $stock->version }}</td>
                                <td>
                                    <form method="POST" action="{{ route('umkm.stocks.update', $stock) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="version" value="{{ $stock->version }}">
                                        <label for="qty-{{ $stock->id }}" class="sr-only">Stok {{ $stock->product->name }}</label>
                                        <input id="qty-{{ $stock->id }}" name="quantity" type="number" min="0" value="{{ $stock->quantity }}" class="field h-9 w-24 px-2 py-1 text-sm">
                                        <button type="submit" class="btn-primary h-9 px-3 text-sm">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $stocks->links() }}</div>
    @endif
@endsection
