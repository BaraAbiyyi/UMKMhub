<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#6B1D2F">
    <title>@yield('title', 'UMKMhub') &middot; Marketplace UMKM</title>
    <meta name="description" content="@yield('description', 'UMKMhub menghubungkan pembeli dengan UMKM lokal: belanja lintas toko dalam satu keranjang.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#F9FAFB] text-gray-900 antialiased selection:bg-[#6B1D2F]/10 selection:text-[#6B1D2F]">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:text-brand focus:shadow-md focus:ring-2 focus:ring-brand">
        Lewati ke konten utama
    </a>

    <header class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/90 backdrop-blur-md transition-all">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-2.5 transition" aria-label="UMKMhub, ke beranda">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-[#7F1D1D] via-[#6B1D2F] to-[#5B1324] text-xs font-black tracking-wider text-white shadow-xs group-hover:shadow-sm group-hover:scale-105 transition-transform">
                        UH
                    </span>
                    <span class="text-xl font-bold tracking-tight text-gray-900">
                        UMKM<span class="text-[#6B1D2F]">hub</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 text-sm font-semibold text-gray-600 md:flex" aria-label="Navigasi utama">
                    <a href="{{ route('products.index') }}" class="rounded-lg px-3.5 py-2 transition-all hover:bg-rose-50/60 hover:text-[#6B1D2F] {{ request()->routeIs('products.*') ? 'bg-rose-50/80 text-[#6B1D2F]' : '' }}">Produk</a>
                    <a href="{{ route('stores.index') }}" class="rounded-lg px-3.5 py-2 transition-all hover:bg-rose-50/60 hover:text-[#6B1D2F] {{ request()->routeIs('stores.*') ? 'bg-rose-50/80 text-[#6B1D2F]' : '' }}">Toko UMKM</a>
                    @auth
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('orders.index') }}" class="rounded-lg px-3.5 py-2 transition-all hover:bg-rose-50/60 hover:text-[#6B1D2F] {{ request()->routeIs('orders.*') ? 'bg-rose-50/80 text-[#6B1D2F]' : '' }}">Pesanan</a>
                        @endif
                    @endauth
                </nav>
            </div>

            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 hover:text-[#6B1D2F] transition-all sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-[#6B1D2F] px-4.5 text-sm font-semibold text-white shadow-xs hover:bg-[#5B1324] hover:shadow transition-all active:scale-[0.98]">Daftar</a>
                @endguest

                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('cart.index') }}" class="relative rounded-xl p-2 text-gray-700 hover:bg-rose-50 hover:text-[#6B1D2F] transition-colors" aria-label="Keranjang belanja">
                            <x-icon name="cart" class="h-5 w-5" />
                            @php $cartCount = auth()->user()->cart?->items()->sum('quantity') ?? 0; @endphp
                            @if ($cartCount > 0)
                                <span class="absolute -right-0.5 -top-0.5 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-[#6B1D2F] px-1 text-[10px] font-bold text-white shadow-xs">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @endif

                    <div class="relative">
                        <details class="group">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl p-1.5 text-sm font-semibold text-gray-800 hover:bg-gray-100 transition-colors">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-50 text-[#6B1D2F] ring-1 ring-rose-200">
                                    <x-icon name="user" class="h-4 w-4" />
                                </span>
                                <span class="hidden max-w-28 truncate sm:inline">{{ auth()->user()->name }}</span>
                                <x-icon name="chevron-down" class="hidden h-4 w-4 text-gray-500 sm:block transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="absolute right-0 z-50 mt-2 w-56 rounded-2xl border border-gray-200 bg-white p-1.5 shadow-xl">
                                @if (auth()->user()->isCustomer())
                                    <a href="{{ route('profile.index') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-[#6B1D2F] transition-colors">Akun &amp; alamat</a>
                                    <a href="{{ route('orders.index') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-[#6B1D2F] transition-colors">Pesanan saya</a>
                                @elseif (auth()->user()->isUmkmAdmin())
                                    <a href="{{ route('umkm.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-[#6B1D2F] transition-colors">Dashboard UMKM</a>
                                @elseif (auth()->user()->isSuperadmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-[#6B1D2F] transition-colors">Dashboard Superadmin</a>
                                @endif
                                <div class="my-1 border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
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
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 pt-4" aria-live="polite">
            @if (session('status'))
                <div class="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-xs">
                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mt-2 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-[#6B1D2F] shadow-xs">
                    <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-[#6B1D2F]" />
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>
    @endif

    <main id="konten" class="flex-1 pb-20 md:pb-12">
        @yield('content')
    </main>

    <footer class="mt-auto border-t border-gray-200/80 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-1 lg:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#7F1D1D] to-[#6B1D2F] text-xs font-black text-white shadow-xs">
                            UH
                        </span>
                        <span class="text-xl font-bold tracking-tight text-gray-950">UMKM<span class="text-[#6B1D2F]">hub</span></span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-gray-500">Marketplace yang menghubungkan pembeli dengan UMKM lokal. Belanja dari banyak toko, satu keranjang, satu pengalaman.</p>
                    <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full w-fit px-3 py-1">
                        <x-icon name="shield" class="h-3.5 w-3.5 text-emerald-600" /> Transaksi Terpercaya
                    </div>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-gray-900">Jelajahi</p>
                    <ul class="mt-4 space-y-2.5 text-sm font-medium text-gray-600">
                        <li><a href="{{ route('products.index') }}" class="hover:text-[#6B1D2F] transition-colors">Produk</a></li>
                        <li><a href="{{ route('stores.index') }}" class="hover:text-[#6B1D2F] transition-colors">Toko UMKM</a></li>
                        <li><a href="{{ route('store-applications.create') }}" class="hover:text-[#6B1D2F] transition-colors">Daftar sebagai UMKM</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-gray-900">Bantuan</p>
                    <ul class="mt-4 space-y-2.5 text-sm font-medium text-gray-600">
                        <li><a href="{{ route('login') }}" class="hover:text-[#6B1D2F] transition-colors">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-[#6B1D2F] transition-colors">Buat akun pembeli</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-gray-900">Keamanan & Layanan</p>
                    <p class="mt-4 text-xs leading-relaxed text-gray-500">
                        Platform e-commerce lokal ramah UMKM dengan pemisahan pesanan otomatis serta pengawasan kurasi produk lokal Indonesia.
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span> Server Aktif & Terlindungi
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t border-gray-100 pt-6">
                <x-simulation-banner message="MODE SIMULASI: seluruh pembayaran di UMKMHub adalah simulasi. Tidak ada uang sungguhan yang diproses dan tidak ada pengiriman nyata." />
            </div>

            <div class="mt-6 flex flex-col items-center justify-between gap-4 border-t border-gray-100 pt-6 text-xs text-gray-500 sm:flex-row">
                <p>&copy; {{ date('Y') }} UMKMhub. Dibuat untuk UMKM lokal.</p>
                <p class="text-gray-400">Dukungan Ekonomi Digital & Pemberdayaan Usaha Mikro, Kecil, dan Menengah</p>
            </div>
        </div>
    </footer>

    {{-- Navigasi bawah khusus mobile --}}
    @if (auth()->check() && auth()->user()->isCustomer())
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 backdrop-blur-md md:hidden shadow-lg" aria-label="Navigasi cepat">
            <div class="grid grid-cols-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs transition-colors {{ request()->routeIs('home') ? 'text-[#6B1D2F] font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                    <x-icon name="home" class="h-5 w-5" /> Beranda
                </a>
                <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs transition-colors {{ request()->routeIs('products.*') ? 'text-[#6B1D2F] font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                    <x-icon name="search" class="h-5 w-5" /> Cari
                </a>
                <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs transition-colors {{ request()->routeIs('cart.*', 'checkout.*') ? 'text-[#6B1D2F] font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                    <x-icon name="cart" class="h-5 w-5" /> Keranjang
                </a>
                <a href="{{ route('orders.index') }}" class="flex flex-col items-center gap-1 py-2.5 text-xs transition-colors {{ request()->routeIs('orders.*') ? 'text-[#6B1D2F] font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                    <x-icon name="package" class="h-5 w-5" /> Pesanan
                </a>
            </div>
        </nav>
    @endif
</body>
</html>
