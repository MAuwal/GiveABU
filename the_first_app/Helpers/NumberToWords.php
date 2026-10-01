<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];

    private static $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    public static function convert($number)
    {
        if ($number == 0) {
            return 'Zero';
        }

        $number = (int) $number;
        
        if ($number < 0) {
            return 'Negative ' . self::convert(abs($number));
        }

        $words = '';

        if ($number >= 1000000000) {
            $words .= self::convert((int)($number / 1000000000)) . ' Billion ';
            $number %= 1000000000;
        }

        if ($number >= 1000000) {
            $words .= self::convert((int)($number / 1000000)) . ' Million ';
            $number %= 1000000;
        }

        if ($number >= 1000) {
            $words .= self::convert((int)($number / 1000)) . ' Thousand ';
            $number %= 1000;
        }

        if ($number >= 100) {
            $words .= self::convert((int)($number / 100)) . ' Hundred ';
            $number %= 100;
        }

        if ($number >= 20) {
            $words .= self::$tens[(int)($number / 10)] . ' ';
            $number %= 10;
        }

        if ($number > 0) {
            $words .= self::$ones[$number] . ' ';
        }

        return trim($words);
    }
}

