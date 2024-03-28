<?php declare(strict_types=1);

namespace POM\iDEAL\Helpers;

class Encode
{
    /**
     * @param string $data
     * @return bool|string
     */
    public static function base64UrlEncode(string $data): bool|string
    {
        $b64 = base64_encode($data);

        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) {
            return false;
        }

        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');

        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }

}