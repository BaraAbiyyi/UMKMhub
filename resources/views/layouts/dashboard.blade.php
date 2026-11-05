<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5e1c23">
    <title>@yield('title', 'Dashboard') &middot; UMKMHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-white focus:px-4 focus:py-2 focus:text-brand">
        Lewati ke konten utama
    </a>

    @php
        $isSuperadmin = auth()->user()->isSuperadmin();
        $store = $store ?? null;
        $ownedStores = auth()->user()->isUmkmAdmin() ? auth()->user()->stores()->orderBy('name')->get() : collect();
        $isPendingStore = $store && ! $store->isApproved();
    @endphp

    <div class="lg:flex">
        {{-- Sidebar desktop --}}
        <aside class="hidden w-64 shrink-0 border-r border-mist bg-white lg:flex lg:min-h-screen lg:flex-col">
            <div class="border-b border-mist p-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-control bg-brand text-sm font-extrabold text-white">UH</span>
                    <span class="text-lg font-extrabold text-brand">UMKMHub</span>
                </a>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ $isSuperadmin ? 'Superadmin' : 'Admin UMKM' }}</p>
                <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
            </div>

            <nav class="flex-1 space-y-1 p-3" aria-label="Menu dashboard">
                @if ($isSuperadmin)
                    @include('layouts.partials.admin-menu')
                @else
                    @include('layouts.partials.umkm-menu')
                @endif
            </nav>

            <div class="border-t border-mist p-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-control px-3 py-2 text-sm text-ink-muted hover:bg-brand-soft hover:text-brand">
                    <x-icon name="home" class="h-4 w-4" /> Lihat marketplace
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-control px-3 py-2 text-left text-sm text-brand hover:bg-brand-soft">
                        <x-icon name="logout" class="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-30 border-b border-mist bg-white/95 backdrop-blur">
                <div class="flex min-h-16 flex-wrap items-center gap-3 px-4 py-2 sm:px-6">
                    <a href="{{ route('home') }}" class="lg:hidden" aria-label="UMKMHub, ke beranda">
                        <span class="flex h-9 w-9 items-center justify-center rounded-control bg-brand text-sm font-extrabold text-white">UH</span>
                    </a>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-ink-muted">@yield('eyebrow', $isSuperadmin ? 'Superadmin' : 'Dashboard UMKM')</p>
                        <h1 class="truncate text-lg font-bold text-ink">@yield('heading', 'Ringkasan')</h1>
                    </div>

                    <div class="ml-auto flex items-center gap-2">
                        @if ($ownedStores->count() > 1)
                            <details class="relative">
                                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-control border border-mist px-3 py-2 text-sm font-semibold text-ink hover:border-mist-deep">
                                    <x-icon name="store" class="h-4 w-4 text-brand" />
                                    <span class="max-w-32 truncate">{{ $store?->name ?? 'Pilih toko' }}</span>
                                    <x-icon name="chevron-down" class="h-4 w-4 text-ink-muted" />
                                </summary>
                                <div class="absolute right-0 z-50 mt-2 w-64 rounded-card border border-mist bg-white p-1 shadow-card">
                                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-ink-muted">Ganti toko</p>
                                    @foreach ($ownedStores as $owned)
                                        <a href="{{ request()->fullUrlWithQuery(['store' => $owned->id]) }}" class="flex items-center justify-between rounded-control px-3 py-2 text-sm hover:bg-brand-soft">
                                            <span class="truncate">{{ $owned->name }}</span>
                                            @if ($store && $store->id === $owned->id)
                                                <x-icon name="check" class="h-4 w-4 text-ok" />
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endif

                        @if (! $isSuperadmin)
                            <a href="{{ route('umkm.orders') }}" class="relative rounded-control p-2 text-ink hover:bg-brand-soft" aria-label="Pesanan masuk">
                                <x-icon name="inbox" class="h-5 w-5" />
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Menu mobile --}}
                <nav class="flex gap-1 overflow-x-auto border-t border-mist px-3 py-2 lg:hidden" aria-label="Menu dashboard mobile">
                    @if ($isSuperadmin)
                        @include('layouts.partials.admin-menu', ['mobile' => true])
                    @else
                        @include('layouts.partials.umkm-menu', ['mobile' => true])
                    @endif
                </nav>
            </header>

            @if (session('status') || session('error'))
                <div class="px-4 pt-4 sm:px-6" aria-live="polite">
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

            @if (! $isSuperadmin && $store && ! $store->isApproved() && ! request()->routeIs('umkm.dashboard', 'umkm.store'))
                <div class="px-4 pt-4 sm:px-6">
                    <div class="flex flex-wrap items-center gap-2 rounded-control border border-sand bg-sand-soft px-4 py-3 text-sm text-ink">
                        <x-status-badge :status="$store->status->value" />
                        <span>Toko <strong>{{ $store->name }}</strong> belum aktif. Anda bisa melengkapi profil, tetapi produk dan pesanan terkunci sampai disetujui.</span>
                    </div>
                </div>
            @endif

            <main id="konten" class="flex-1 px-4 py-6 sm:px-6">
                @yield('content')
            </main>

            <footer class="border-t border-mist px-4 py-4 text-xs text-ink-muted sm:px-6">
                <span class="inline-flex items-center gap-1 font-semibold text-brand"><x-icon name="info" class="h-4 w-4" /> MODE SIMULASI</span>
                &middot; Pembayaran dan pengiriman di UMKMHub hanya simulasi.
            </footer>
        </div>
    </div>
</body>
</html>
