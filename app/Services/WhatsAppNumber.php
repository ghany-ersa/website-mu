<?php

namespace App\Services;

/**
 * Normalizes an Indonesian phone number into the international-digits-only format
 * wa.me links require (62xxxxxxxxxx). Numbers typed with the local 0-prefix (the
 * common way Indonesians write their own number, e.g. 081234567890) don't work as
 * a wa.me link as-is - wa.me needs the country code instead of the leading 0.
 */
class WhatsAppNumber
{
    public static function normalize(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }

    public static function href(?string $number, ?string $message = null): ?string
    {
        $digits = self::normalize($number);

        if ($digits === null) {
            return null;
        }

        $href = 'https://wa.me/'.$digits;

        if (filled($message)) {
            $href .= '?text='.rawurlencode($message);
        }

        return $href;
    }
}
