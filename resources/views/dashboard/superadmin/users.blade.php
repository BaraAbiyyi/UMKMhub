@extends('layouts.dashboard')

@section('title', 'Pengguna')
@section('eyebrow', 'Superadmin')
@section('heading', 'Pengguna platform')

@section('content')
    <form method="GET" action="{{ route('admin.users') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="search" class="label">Cari pengguna</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau email" class="field">
        </div>
        <div class="sm:w-48">
            <label for="role" class="label">Peran</label>
            <select id="role" name="role" class="field">
                <option value="">Semua peran</option>
                @foreach (['customer' => 'Pembeli', 'umkm_admin' => 'Admin UMKM', 'superadmin' => 'Superadmin'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary h-11">Terapkan</button>
    </form>

    @if ($users->isEmpty())
        <x-empty-state class="mt-4" title="Pengguna tidak ditemukan" description="Coba ubah kata kunci atau filter peran." />
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Email</th>
                            <th scope="col">Peran</th>
                            <th scope="col">Toko</th>
                            <th scope="col">Bergabung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="font-medium text-ink">{{ $user->name }}</td>
                                <td class="text-ink-muted">{{ $user->email }}</td>
                                <td>
                                    @php
                                        $roleClass = match ($user->role->value) {
                                            'superadmin' => 'bg-brand text-white',
                                            'umkm_admin' => 'bg-mist/50 text-ink',
                                            default => 'bg-canvas text-ink-muted',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleClass }}">{{ $user->role->label() }}</span>
                                </td>
                                <td class="text-ink-muted">{{ $user->stores_count }}</td>
                                <td class="text-ink-muted">{{ tanggal($user->created_at, false) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    @endif
@endsection
