<?php

use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    function rupiah(int|float|null $value): string
    {
        return 'Rp'.number_format((int) $value, 0, ',', '.');
    }
}

if (! function_exists('tanggal')) {
    function tanggal(DateTimeInterface|string|null $value, bool $withTime = true): string
    {
        if (! $value) {
            return '-';
        }

        $date = $value instanceof DateTimeInterface ? $value : Carbon::parse($value);

        return $withTime ? $date->translatedFormat('j M Y, H:i') : $date->translatedFormat('j M Y');
    }
}
