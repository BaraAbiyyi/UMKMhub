<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5e1c23">
    <title>@yield('title', 'UMKMHub') &middot; Marketplace UMKM</title>
    <meta name="description" content="@yield('description', 'UMKMHub menghubungkan pembeli dengan UMKM lokal: belanja lintas toko dalam satu keranjang.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-white focus:px-4 focus:py-2 focus:text-brand">
        Lewati ke konten utama
    </a>

    <header class="sticky top-0 z-40 border-b border-mist bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:gap-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2" aria-label="UMKMHub, ke beranda">
                <span class="flex h-9 w-9 items-center justify-center rounded-control bg-brand text-sm font-extrabold text-white">UH</span>
                <span class="text-lg font-extrabold tracking-tight text-brand">UMKMHub</span>
            </a>

            <nav class="hidden items-center gap-1 text-sm font-medium text-ink-muted md:flex" aria-label="Navigasi utama">
                <a href="{{ route('products.index') }}" class="rounded-control px-3 py-2 hover:bg-brand-soft hover:text-brand {{ request()->routeIs('products.*') ? 'text-brand' : '' }}">Produk</a>
                <a href="{{ route('stores.index') }}" class="rounded-control px-3 py-2 hover:bg-brand-soft hover:text-brand {{ request()->routeIs('stores.*') ? 'text-brand' : '' }}">Toko UMKM</a>
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('orders.index') }}" class="rounded-control px-3 py-2 hover:bg-brand-soft hover:text-brand {{ request()->routeIs('orders.*') ? 'text-brand' : '' }}">Pesanan</a>
                    @endif
                @endauth
            </nav>

            <div class="ml-auto flex items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" class="hidden rounded-control px-3 py-2 text-sm font-semibold text-ink hover:bg-brand-soft hover:text-brand sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary h-10 px-3 text-sm">Daftar</a>
                @endguest

                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('cart.index') }}" class="relative rounded-control p-2 text-ink hover:bg-brand-soft hover:text-brand" aria-label="Keranjang belanja">
                            <x-icon name="cart" class="h-6 w-6" />
                            @php $cartCount = auth()->user()->cart?->items()->sum('quantity') ?? 0; @endphp
                            @if ($cartCount > 0)
                                <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand px-1 text-[11px] font-bold text-white">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @endif

                    <div class="relative">
                        <details class="group">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-control p-1.5 text-sm font-semibold text-ink hover:bg-brand-soft">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-soft text-brand">
                                    <x-icon name="user" class="h-4 w-4" />
                                </span>
                                <span class="hidden max-w-28 truncate sm:inline">{{ auth()->user()->name }}</span>
                                <x-icon name="chevron-down" class="hidden h-4 w-4 text-ink-muted sm:block" />
                            </summary>
                            <div class="absolute right-0 z-50 mt-2 w-56 rounded-card border border-mist bg-white p-1 shadow-card">
                                @if (auth()->user()->isCustomer())
                                    <a href="{{ route('profile.index') }}" class="block rounded-control px-3 py-2 text-sm hover:bg-brand-soft">Akun &amp; alamat</a>
                                    <a href="{{ route('orders.index') }}" class="block rounded-control px-3 py-2 text-sm hover:bg-brand-soft">Pesanan saya</a>
                                @elseif (auth()->user()->isUmkmAdmin())
                                    <a href="{{ route('umkm.dashboard') }}" class="block rounded-control px-3 py-2 text-sm hover:bg-brand-soft">Dashboard UMKM</a>
                                @elseif (auth()->user()->isSuperadmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block rounded-control px-3 py-2 text-sm hover:bg-brand-soft">Dashboard Superadmin</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-control px-3 py-2 text-left text-sm text-brand hover:bg-brand-soft">
                                        <x-icon name="logout" class="h-4 w-4" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    @if (session('status') || session('error'))
        <div class="mx-auto w-full max-w-6xl px-4 pt-4" aria-live="polite">
            @if (session('status'))
                <div class="flex items-start gap-2 rounded-control border border-ok/40 bg-ok/10 px-4 py-3 text-sm text-ink">
                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-ok" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mt-2 flex items-start gap-2 rounded-control border border-brand/40 bg-brand-soft px-4 py-3 text-sm text-brand">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>
    @endif

    <main id="konten" class="flex-1 pb-24 md:pb-12">
        @yield('content')
    </main>

    <footer class="mt-8 border-t border-mist bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <p class="text-lg font-extrabold text-brand">UMKMHub</p>
                    <p class="mt-2 max-w-sm text-sm text-ink-muted">Marketplace yang menghubungkan pembeli dengan UMKM lokal. Belanja dari banyak toko, satu keranjang, satu pengalaman.</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">Jelajahi</p>
                    <ul class="mt-2 space-y-1 text-sm text-ink-muted">
                        <li><a href="{{ route('products.index') }}" class="hover:text-brand">Produk</a></li>
                        <li><a href="{{ route('stores.index') }}" class="hover:text-brand">Toko UMKM</a></li>
                        <li><a href="{{ route('store-applications.create') }}" class="hover:text-brand">Daftar sebagai UMKM</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">Bantuan</p>
                    <ul class="mt-2 space-y-1 text-sm text-ink-muted">
                        <li><a href="{{ route('login') }}" class="hover:text-brand">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-brand">Buat akun pembeli</a></li>
                    </ul>
                </div>
            </div>

            <x-simulation-banner class="mt-6" message="MODE SIMULASI: seluruh pembayaran di UMKMHub adalah simulasi. Tidak ada uang sungguhan yang diproses dan tidak ada pengiriman nyata." />

            <p class="mt-4 text-xs text-ink-muted">&copy; {{ date('Y') }} UMKMHub. Dibuat untuk UMKM lokal.</p>
        </div>
    </footer>

    {{-- Navigasi bawah khusus mobile --}}
    @if (auth()->check() && auth()->user()->isCustomer())
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-mist bg-white/95 backdrop-blur md:hidden" aria-label="Navigasi cepat">
            <div class="grid grid-cols-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-2 text-xs {{ request()->routeIs('home') ? 'text-brand font-semibold' : 'text-ink-muted' }}">
                    <x-icon name="home" class="h-5 w-5" /> Beranda
                </a>
                <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-1 py-2 text-xs {{ request()->routeIs('products.*') ? 'text-brand font-semibold' : 'text-ink-muted' }}">
                    <x-icon name="search" class="h-5 w-5" /> Cari
                </a>
                <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-1 py-2 text-xs {{ request()->routeIs('cart.*', 'checkout.*') ? 'text-brand font-semibold' : 'text-ink-muted' }}">
                    <x-icon name="cart" class="h-5 w-5" /> Keranjang
                </a>
                <a href="{{ route('orders.index') }}" class="flex flex-col items-center gap-1 py-2 text-xs {{ request()->routeIs('orders.*') ? 'text-brand font-semibold' : 'text-ink-muted' }}">
                    <x-icon name="package" class="h-5 w-5" /> Pesanan
                </a>
            </div>
        </nav>
    @endif
</body>
</html>
