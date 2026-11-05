@props(['status'])

@php
    $map = [
        'pending_payment' => ['Menunggu pembayaran', 'bg-sand-soft text-ink'],
        'paid' => ['Sudah dibayar', 'bg-mist/40 text-ink'],
        'processing' => ['Diproses', 'bg-mist text-brand'],
        'shipped' => ['Dikirim', 'bg-ink-muted text-white'],
        'completed' => ['Selesai', 'bg-ok text-white'],
        'cancelled' => ['Dibatalkan', 'bg-brand-soft text-brand'],
        'pending' => ['Menunggu', 'bg-sand-soft text-ink'],
        'approved' => ['Disetujui', 'bg-ok text-white'],
        'rejected' => ['Ditolak', 'bg-brand-soft text-brand'],
        'suspended' => ['Disuspend', 'bg-ink-muted text-white'],
        'success' => ['Berhasil', 'bg-ok text-white'],
        'failed' => ['Gagal', 'bg-brand-soft text-brand'],
        'expired' => ['Kedaluwarsa', 'bg-brand-soft text-brand'],
        'refunded_simulation' => ['Dana simulasi dikembalikan', 'bg-sand-soft text-ink'],
    ];

    [$label, $classes] = $map[$status] ?? [ucfirst(str_replace('_', ' ', (string) $status)), 'bg-canvas text-ink-muted'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.$classes]) }}>
    {{ $label }}
</span>
