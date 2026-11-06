@props(['store'])

@php
    $logo = $store->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($store->logo_path) : null;
@endphp

<article class="group relative flex flex-col justify-between rounded-2xl border border-gray-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md">
    <div>
        <div class="flex items-start gap-3.5">
            <div class="relative shrink-0">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo {{ $store->name }}" class="h-12 w-12 rounded-xl border border-gray-200 object-cover shadow-2xs">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-rose-50 to-red-100 text-lg font-black text-[#6B1D2F] ring-1 ring-rose-200/60 shadow-2xs">
                        {{ mb_substr($store->name, 0, 1) }}
                    </div>
                @endif
                <span class="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-white ring-2 ring-white" title="Terverifikasi">
                    <x-icon name="check" class="h-2.5 w-2.5" />
                </span>
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="truncate text-base font-bold text-gray-900 group-hover:text-[#6B1D2F] transition-colors">
                    {{ $store->name }}
                </h3>
                <p class="truncate text-xs font-medium text-gray-500 mt-0.5">
                    {{ $store->city }}@if ($store->category) &middot; <span class="text-[#6B1D2F] font-semibold">{{ $store->category->name }}</span>@endif
                </p>
            </div>
        </div>

        <p class="mt-3.5 line-clamp-2 text-xs leading-relaxed text-gray-600">
            {{ $store->description ?: 'Toko UMKM terverifikasi di UMKMhub.' }}
        </p>
    </div>

    <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-3 text-xs">
        <span class="inline-flex items-center gap-1 font-semibold text-gray-500">
            <x-icon name="box" class="h-3.5 w-3.5 text-gray-400" /> {{ $store->products_count ?? 0 }} produk
        </span>
        <a href="{{ route('stores.show', $store->slug) }}" class="inline-flex items-center gap-1 font-bold text-[#6B1D2F] hover:text-[#5B1324] hover:underline">
            Kunjungi toko <x-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
        </a>
    </div>
</article>
