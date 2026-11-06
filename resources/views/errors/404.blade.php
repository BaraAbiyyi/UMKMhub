@extends('layouts.public')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <div class="mx-auto flex max-w-2xl flex-col items-center justify-center px-4 py-20 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-rose-50 text-3xl font-black text-[#6B1D2F] ring-1 ring-rose-100">
            404
        </div>
        <h1 class="mt-6 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">Halaman atau Produk Tidak Ditemukan</h1>
        <p class="mt-3 text-sm text-gray-500">
            Produk yang Anda tuju mungkin sedang tidak aktif, belum disetujui, atau tautan tidak valid.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('products.index') }}" class="btn-primary">
                Jelajahi Produk Lain
            </a>
            <a href="{{ route('home') }}" class="btn-secondary">
                Kembali ke Beranda
            </a>
        </div>
    </div>
@endsection

