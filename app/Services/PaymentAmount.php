<?php

namespace App\Services;

use InvalidArgumentException;

class PaymentAmount
{
    public static function kobo(mixed $naira): int
    {
        $value = (string) $naira;
        if (! preg_match('/\A(\d{1,13})(?:\.(\d{1,2}))?\z/', $value, $parts)) {
            throw new InvalidArgumentException('Invalid Naira amount');
        }

        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    public static function minor(mixed $amount): ?int
    {
        if (! is_int($amount) && ! is_string($amount)) {
            return null;
        }
        if (! preg_match('/\A\d{1,15}\z/', (string) $amount)) {
            return null;
        }

        return (int) $amount;
    }

    public static function naira(int $kobo): string
    {
        return intdiv($kobo, 100).'.'.str_pad((string) ($kobo % 100), 2, '0', STR_PAD_LEFT);
    }
}
