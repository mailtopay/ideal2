<?php declare(strict_types=1);

namespace POM\iDEAL\Helpers;

class DerConverter
{
    /**
     * @param string $pemContent
     * @return bool|string
     */
    public static function pemToDer(string $pemContent): string
    {
        // Extract the base64-encoded portion (between -----BEGIN CERTIFICATE----- and -----END CERTIFICATE-----)
        $begin = '-----BEGIN CERTIFICATE-----';
        $end = '-----END CERTIFICATE-----';

        $pemContent = substr($pemContent, strpos($pemContent, $begin) + strlen($begin));
        $pemContent = substr($pemContent, 0, strpos($pemContent, $end));

        // Remove whitespace and newline characters
        $pemContent = str_replace("\r", '', str_replace("\n", '', $pemContent));

        return base64_decode($pemContent);
    }

}