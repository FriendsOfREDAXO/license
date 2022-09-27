<?php

class YLicenseStandardizer
{
    public function __construct()
    {
    }

    public static function standardize($license)
    {
        if (is_array($license)) {
            $tmp = [];
            foreach ($license as $item) {
                $tmp[] = self::resolveLicenseName($item);
            }
            $license = $tmp;
        } else {
            $license = self::resolveLicenseName($license);
        }

        return $license;
    }

    private static function resolveLicenseName($name)
    {
        if (preg_match('/^The MIT License(?: \(MIT\))$/i', $name)) {
            $name = 'MIT License';
        }
        if (preg_match('/MIT/i', $name)) {
            $name = 'MIT License';
        }
        return $name;
    }
}
