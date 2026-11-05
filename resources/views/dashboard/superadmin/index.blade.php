@extends('layouts.dashboard')

@section('title', 'Ringkasan Platform')
@section('eyebrow', 'Superadmin')
@section('heading', 'Ringkasan platform')

@section('content')
    @php
        $maxOrders = $platformOrders->max() ?: 1;
        $orderDays = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">Pantau pertumbuhan toko dan aktivitas pesanan UMKMHub.</p>
        <a href="{{ route('admin.applications') }}" class="btn-primary h-9 px-3 text-sm">
            <x-icon name="shield" class="h-4 w-4" /> Tinjau pendaftaran
        </a>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-stat-card label="Toko aktif" :value="$stats['active_stores']" icon="store" accent />
        <x-stat-card label="Menunggu persetujuan" :value="$stats['pending_stores']" note="Perlu ditinjau" icon="clock" />
        <x-stat-card label="Total produk" :value="$stats['products']" icon="box" />
        <x-stat-card label="Total pengguna" :value="$stats['users']" icon="users" />
        <x-stat-card label="Total pesanan" :value="$stats['orders']" icon="package" />
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-[1fr_320px]">
        <section class="card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-ink">Pesanan 7 hari</h2>
                    <p class="text-sm text-ink-muted">Jumlah pesanan platform per hari.</p>
                </div>
            </div>

            <div class="mt-6 flex h-44 items-end gap-2 sm:gap-3" role="img" aria-label="Grafik pesanan platform tujuh hari terakhir">
                @foreach ($orderDays as $day)
                    @php
                        $count = (int) ($platformOrders[$day->format('Y-m-d')] ?? 0);
                        $height = (int) round(($count / $maxOrders) * 100);
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <span class="text-[10px] text-ink-muted">{{ $count ?: '' }}</span>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t bg-brand/80" style="height: {{ max($height, $count > 0 ? 4 : 1) }}%"></div>
                        </div>
                        <span class="text-[11px] text-ink-muted">{{ $day->format('d M') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card p-5">
            <h2 class="text-lg font-bold text-ink">Pendaftaran toko</h2>
            <p class="text-sm text-ink-muted">5 minggu terakhir.</p>
            @if ($registrations->isEmpty())
                <p class="mt-3 text-sm text-ink-muted">Belum ada data pendaftaran.</p>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($registrations as $week => $count)
                        <li class="flex items-center justify-between gap-2 text-sm">
                            <span class="text-ink-muted">{{ str_replace('-W', ' minggu ', $week) }}</span>
                            <span class="font-semibold text-ink">{{ $count }} toko</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-mist px-5 py-4">
            <h2 class="text-lg font-bold text-ink">Pendaftaran terbaru</h2>
            <a href="{{ route('admin.applications') }}" class="text-sm font-semibold text-brand hover:underline">Lihat semua</a>
        </div>

        @if ($latestApplications->isEmpty())
            <div class="p-5">
                <x-empty-state title="Tidak ada pendaftaran baru" description="Semua pengajuan toko sudah ditinjau." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Toko</th>
                            <th scope="col">Pemilik</th>
                            <th scope="col">Kota</th>
                            <th scope="col">Diajukan</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestApplications as $application)
                            <tr>
                                <td class="font-medium text-ink">{{ $application->name }}</td>
                                <td class="text-ink-muted">{{ $application->owner->name }}</td>
                                <td class="text-ink-muted">{{ $application->city }}</td>
                                <td class="text-ink-muted">{{ tanggal($application->submitted_at, false) }}</td>
                                <td><x-status-badge :status="$application->status->value" /></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.applications', ['status' => 'pending']) }}" class="text-sm font-semibold text-brand hover:underline">Tinjau</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
