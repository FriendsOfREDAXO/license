<?php
class YLicenseStandardizer
{
    function __construct()
    {
    }

    static public function standardize($license)
    {
        if (is_array($license)) {
            $tmp = array();
            foreach ($license AS $item) {
                $tmp[] = self::resolveLicenseName($item);
            }
            $license = $tmp;
        }
        else {
            $license = self::resolveLicenseName($license);
        }

        return $license;
    }

    static private function resolveLicenseName($name)
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