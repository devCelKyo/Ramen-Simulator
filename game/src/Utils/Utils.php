<?php

namespace App\Utils;

class Utils
{
    const SUFFIXES = array("K", "M", "B", "T", "Qd", "Qn", "Sx", "Sp", "Oc", "N", "D");

    /**
     * Parses the GMP number object into a more legible, suffixed version.
     */
    public static function gmpToString(\GMP|string $number)
    {
        if (is_string($number)) return $number;
        
        $string = gmp_strval($number);
        $amountDigits = strlen($string);
        if ($amountDigits <= 3) {
            return $string;
        }

        $groupsOf3 = intdiv($amountDigits - 1, 3);
        $suffix = self::SUFFIXES[$groupsOf3 - 1];

        if ($amountDigits % 3 == 0) {
            $nbRemainingDigits = 3;
        }
        else {
            $nbRemainingDigits = $amountDigits % 3;
        }
        $remainingDigits = substr($string, 0, $nbRemainingDigits);
        $pointValue = $string[$nbRemainingDigits];

        return $remainingDigits . "." . $pointValue . $suffix;
    }

    /**
     * Inverses gmpToString
     */
    public static function stringToGMP(string $number)
    {
        // Basic regex just to make sure it's not complete nonsense
        $matches_format = preg_match('#^[0-9]{1,3}(\.[0-9])?[A-Za-z]$#', $number);
        $all_numbers = preg_match('#^[0-9]+$#', $number);
        if (!$matches_format && !$all_numbers) {
            throw new \Exception('Invalid number format... how could you miss this?');
        }

        $n = strlen($number);
        // (1) Trivial case where it's all numbers
        if ($all_numbers) {
            return gmp_init($number);
        }

        // (2) Now, we can assume it's the general case : 000.0A
        $prefixNumber = "";
        for ($i = 0; $i < strlen($number); $i++) {
            if ($number[$i] == '.') continue;
            if (is_numeric($number[$i])) {
                $prefixNumber .= $number[$i];
            }
            else {
                break;
            }
        }
        $suffix = $number[$n - 1];

        $key = array_search($suffix, self::SUFFIXES);
        if ($key === false) {
            throw new \Exception('Invalid suffix');
        }
        
        $powerOf10 = ($key + 1)*3;
        // Testing if there is a dot
        if (strpos($number, '.') !== false) {
            $powerOf10 -= 1;
        }

        $zeroes = "1";
        for ($i = 0; $i < $powerOf10; $i++) {
            $zeroes .= "0";
        }
        $zeroes = gmp_init($zeroes);
        $gmp = gmp_mul($prefixNumber, $zeroes);

        return $gmp;
    }

    public static function max(\GMP $a, \GMP $b)
    {
        if ($a > $b) {
            return $a;
        }
        return $b;
    }

    public static function min(\GMP $a, \GMP $b)
    {
        if ($a > $b) {
            return $b;
        }
        return $a;
    }

    public static function pow(int $base, int $exp)
    {
        if ($exp == 0) {
            return 1;
        }

        $number = gmp_init($base);
        for ($i = 0; $i < $exp; $i++) {
            $number = $number * $base;
        }

        return $number;
    }
}