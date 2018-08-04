<?php

namespace App\Helplers;

/**
 * 工具庫
 */

class ToolsHelpler
{
    /**
     * 字串轉成16進位編碼
     *
     * @param  string  $string
     *
     * @return string $hex
     */

    public static function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    /**
     * 16進位編碼轉成字串
     *
     * @param  string  $hex
     *
     * @return string $string
     */

    public static function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }
}
