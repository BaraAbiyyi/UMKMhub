<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case UmkmAdmin = 'umkm_admin';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::UmkmAdmin => 'Admin UMKM',
            self::Customer => 'Pembeli',
        };
    }
}
