<?php

namespace nulastudio\SSR;

class Util
{
    public static function urlSafeBase64Encode($string)
    {
        $base64 = base64_encode($string);
        $base64 = str_replace(array('+', '/', '='), array('-', '_', ''), $base64);
        return $base64;
    }
    public static function urlSafeBase64Decode($base64)
    {
        $base64 = str_replace(array('-', '_'), array('+', '/'), $base64);
        $mod4   = strlen($base64) % 4;
        if ($mod4) {
            $base64 .= substr('====', $mod4);
        }
        return base64_decode($base64);
    }
    public static function ensureBase64($string)
    {
        return preg_replace('#[^A-Za-z0-9\+\/\=\-\_]#', '', $string);
    }
}
