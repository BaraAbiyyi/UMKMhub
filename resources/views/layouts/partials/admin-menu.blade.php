@php
    $mobile = $mobile ?? false;
    $items = [
        ['route' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'dashboard', 'active' => request()->routeIs('admin.dashboard')],
        ['route' => 'admin.applications', 'label' => 'Persetujuan', 'icon' => 'shield', 'active' => request()->routeIs('admin.applications')],
        ['route' => 'admin.stores', 'label' => 'Toko', 'icon' => 'store', 'active' => request()->routeIs('admin.stores')],
        ['route' => 'admin.users', 'label' => 'Pengguna', 'icon' => 'users', 'active' => request()->routeIs('admin.users')],
        ['route' => 'admin.categories', 'label' => 'Kategori', 'icon' => 'category', 'active' => request()->routeIs('admin.categories')],
        ['route' => 'admin.orders', 'label' => 'Pesanan', 'icon' => 'package', 'active' => request()->routeIs('admin.orders')],
    ];
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
