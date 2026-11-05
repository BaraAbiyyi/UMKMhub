@php
    $mobile = $mobile ?? false;
    $items = [
        ['route' => 'umkm.dashboard', 'label' => 'Ringkasan', 'icon' => 'dashboard', 'active' => request()->routeIs('umkm.dashboard')],
        ['route' => 'umkm.products', 'label' => 'Produk', 'icon' => 'box', 'active' => request()->routeIs('umkm.products*')],
        ['route' => 'umkm.stocks', 'label' => 'Stok', 'icon' => 'package', 'active' => request()->routeIs('umkm.stocks*')],
        ['route' => 'umkm.orders', 'label' => 'Pesanan', 'icon' => 'inbox', 'active' => request()->routeIs('umkm.orders*')],
        ['route' => 'umkm.store', 'label' => 'Profil Toko', 'icon' => 'store', 'active' => request()->routeIs('umkm.store*')],
    ];

    if (($store ?? null) && ! $store->isApproved()) {
        $items = array_values(array_filter($items, fn ($item) => in_array($item['route'], ['umkm.dashboard', 'umkm.store'], true)));
    }
@endphp

@foreach ($items as $item)
    <a href="{{ route($item['route']) }}"
       title="{{ $item['label'] }}"
       @class([
           'flex items-center gap-2 rounded-control px-3 py-2 text-sm whitespace-nowrap',
           'bg-brand-soft font-semibold text-brand' => $item['active'],
           'text-ink-muted hover:bg-brand-soft hover:text-brand' => ! $item['active'],
       ])>
        <x-icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
        <span>{{ $item['label'] }}</span>
    </a>
@endforeach
