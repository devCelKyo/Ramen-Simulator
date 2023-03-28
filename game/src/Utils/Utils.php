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
}