<?php

namespace Neo;

/**
 * 字符处理类
 */
class Str
{
    /**
     * 通过正则表达式分隔字符串为数组
     *
     * @param string $subject 输入字符串
     * @param string $pattern 用于搜索的模式，字符串形式
     *
     * @return array
     */
    public static function splitString(string $subject, string $pattern = '')
    {
        $pattern || $pattern = '';

        return preg_split('/' . $pattern . '/', $subject, 0, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 获取2个数之间的随机浮点数
     *
     * @param int $min 开始的数
     * @param int $max 结束的数
     *
     * @return float
     */
    public static function randFloat(int $min = 0, int $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * 随机生成一个长度为 $length 的，只含数字和字母的字符串，默认是 6
     *
     * @param int  $length        长度，最大256位
     * @param int  $mixed         随机串的组成，0：数字+字母；1：数字；2：字母
     * @param bool $casesensitive 大小写敏感。如果为TRUE，则返回小写字母
     * @param bool $confused      true表示不区分0和O，1和I，false区分
     *
     * @return string 字符串
     */
    public static function randString(int $length = 6, int $mixed = 0, bool $casesensitive = false, bool $confused = true)
    {
        if ($length < 1) {
            return '';
        }

        $length = min($length, 256);

        if ($confused) {
            $num = '0123456789';
            $lower = 'abcdefghijklmnopqrstuvwxyz';
            $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            // 不包含部分易混淆的字符: i,l,o,0,1
            $num = '23456789';
            $lower = 'abcdefghjkmnpqrstuvwxyz';
            $upper = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        $alpha = $casesensitive ? $lower : $lower . $upper;

        switch ($mixed) {
            case 1:
                $str = $num;
                break;
            case 2:
                $str = $alpha;
                break;
            default:
                $str = $num . $alpha;
                break;
        }
        $str = str_shuffle(str_repeat($str, 50));

        return substr($str, 0, $length);
    }

    /**
     * 获取 ASCII表 中，从33到126之间的可见字符组成的长度为 $length 的随机串
     *
     * @param int $length 长度
     *
     * @return string 字符串
     */
    public static function salt(int $length = 6)
    {
        $salt = '';

        for ($i = 0; $i < $length; ++$i) {
            $salt .= chr(mt_rand(33, 126));
        }

        return $salt;
    }

    /**
     * 转换为非负整数
     *
     * @param int $maybeint 待转数
     *
     * @return int An 非负整数
     */
    public static function absint($maybeint)
    {
        return abs(intval($maybeint));
    }

    /**
     * 格式化输出文件大小，带单位
     *
     * @param int $bytes    文件大小
     * @param int $decimals 精度
     *
     * @return string
     */
    public static function byteFormat(int $bytes, int $decimals = 2)
    {
        $units = [
            'B' => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8,
        ];

        $value = 0;
        $unit = '';
        if ($bytes > 0) {
            $pow = floor(log($bytes) / log(1024));
            $unit = array_search($pow, $units);

            // Calculate byte value by prefix
            $value = $bytes / pow(1024, floor($units[$unit]));
        }

        // If decimals is not numeric or decimals is less than 0
        // then set default value
        if (! is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        return number_format($value, $decimals) . $unit;
    }

    /**
     * 缺省字符集，默认utf-8
     *
     * @return string
     */
    public static function getDefaultCharset()
    {
        return defined('NEO_CHARSET') && NEO_CHARSET ? NEO_CHARSET : 'utf-8';
    }

    /**
     * 计算字符长度
     *
     * @param string $string 字符串
     *
     * @return int
     */
    public static function strlen(string $string)
    {
        $charset = static::getDefaultCharset();

        $string = preg_replace('#&\#([0-9]+);#', '_', $string);

        if (function_exists('mb_strlen')) {
            $length = (int) mb_strlen($string, $charset);

            if ($length) {
                return $length;
            }
        }

        if (strtolower($charset) === 'utf-8') {
            $string = utf8_decode($string);
        }

        return strlen($string);
    }

    /**
     * 截取部分字符串
     *
     * @param string $string 待截取的字符串
     * @param int    $start  开始位置
     * @param int    $length 截取的长度
     *
     * @return string
     */
    public static function substr(string $string, int $start = 0, ?int $length = null)
    {
        if (! is_null($length) && $length <= 0) {
            return $string;
        }

        if (function_exists('mb_substr')) {
            $substr = mb_substr($string, $start, $length, static::getDefaultCharset());

            if ($substr != '') {
                return $substr;
            }
        }

        return $length ? substr($string, $start, $length) : substr($string, $start);
    }

    /**
     * 截取IP的A、B、C等类
     *
     * @param string $ip     IP address
     * @param int    $length 长度，1，2，3
     *
     * @return string truncated IP address
     */
    public static function substrIp(string $ip, ?int $length = null)
    {
        if ($length === null || $length > 3) {
            $length = 1;
        }

        return implode('.', array_slice(explode('.', $ip), 0, 4 - $length));
    }

    /**
     * 检查某个字符串是否指定的串开始
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public static function startsWith(string $haystack = '', string $needle = '')
    {
        $length = strlen($needle);

        return substr($haystack, 0, $length) === $needle;
    }

    /**
     * 检查某个字符串是否指定的串结尾
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public static function endsWith(string $haystack = '', string $needle = '')
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

    /**
     * 移除字符串中的Emoj
     *
     * @href http://www.unicode.org/Public/emoji/2.0//emoji-data.txt
     *
     * @param string $text
     *
     * @return string
     */
    public static function removeEmoj(string $text = '')
    {
        if (! $text) {
            return '';
        }

        $emojs = [
            '/[\x{203C}]/u',
            '/[\x{2049}]/u',
            '/[\x{2122}]/u',
            '/[\x{2139}]/u',
            '/[\x{2194}-\x{2199}]/u',
            '/[\x{21A9}-\x{21AA}]/u',
            '/[\x{231A}-\x{231B}]/u',
            '/[\x{2328}]/u',
            '/[\x{23CF}]/u',
            '/[\x{23E9}-\x{23F3}]/u',
            '/[\x{23F8}-\x{23FA}]/u',
            '/[\x{24C2}]/u',
            '/[\x{25AA}-\x{25AB}]/u',
            '/[\x{25B6}]/u',
            '/[\x{25C0}]/u',
            '/[\x{25FB}-\x{25FE}]/u',
            '/[\x{2600}-\x{2604}]/u',
            '/[\x{260E}]/u',
            '/[\x{2611}]/u',
            '/[\x{2614}-\x{2615}]/u',
            '/[\x{2618}]/u',
            '/[\x{261D}]/u',
            '/[\x{2620}]/u',
            '/[\x{2622}-\x{2623}]/u',
            '/[\x{2626}]/u',
            '/[\x{262A}]/u',
            '/[\x{262E}-\x{262F}]/u',
            '/[\x{2638}-\x{263A}]/u',
            '/[\x{2648}-\x{2653}]/u',
            '/[\x{2660}]/u',
            '/[\x{2663}]/u',
            '/[\x{2665}-\x{2666}]/u',
            '/[\x{2668}]/u',
            '/[\x{267B}]/u',
            '/[\x{267F}]/u',
            '/[\x{2692}-\x{2694}]/u',
            '/[\x{2696}-\x{2697}]/u',
            '/[\x{2699}]/u',
            '/[\x{269B}-\x{269C}]/u',
            '/[\x{26A0}-\x{26A1}]/u',
            '/[\x{26AA}-\x{26AB}]/u',
            '/[\x{26B0}-\x{26B1}]/u',
            '/[\x{26BD}-\x{26BE}]/u',
            '/[\x{26C4}-\x{26C5}]/u',
            '/[\x{26C8}]/u',
            '/[\x{26CE}-\x{26CF}]/u',
            '/[\x{26D1}]/u',
            '/[\x{26D3}-\x{26D4}]/u',
            '/[\x{26E9}-\x{26EA}]/u',
            '/[\x{26F0}-\x{26F5}]/u',
            '/[\x{26F7}-\x{26FA}]/u',
            '/[\x{26FD}]/u',
            '/[\x{2702}]/u',
            '/[\x{2705}]/u',
            '/[\x{2708}-\x{270D}]/u',
            '/[\x{270F}]/u',
            '/[\x{2712}]/u',
            '/[\x{2714}]/u',
            '/[\x{2716}]/u',
            '/[\x{271D}]/u',
            '/[\x{2721}]/u',
            '/[\x{2728}]/u',
            '/[\x{2733}-\x{2734}]/u',
            '/[\x{2744}]/u',
            '/[\x{2747}]/u',
            '/[\x{274C}]/u',
            '/[\x{274E}]/u',
            '/[\x{2753}-\x{2755}]/u',
            '/[\x{2757}]/u',
            '/[\x{2763}-\x{2764}]/u',
            '/[\x{2795}-\x{2797}]/u',
            '/[\x{27A1}]/u',
            '/[\x{27B0}]/u',
            '/[\x{27BF}]/u',
            '/[\x{2934}-\x{2935}]/u',
            '/[\x{2B05}-\x{2B07}]/u',
            '/[\x{2B1B}-\x{2B1C}]/u',
            '/[\x{2B50}]/u',
            '/[\x{2B55}]/u',
            '/[\x{3030}]/u',
            '/[\x{303D}]/u',
            '/[\x{3297}]/u',
            '/[\x{3299}]/u',
            '/[\x{1F004}]/u',
            '/[\x{1F0CF}]/u',
            '/[\x{1F170}-\x{1F171}]/u',
            '/[\x{1F17E}-\x{1F17F}]/u',
            '/[\x{1F18E}]/u',
            '/[\x{1F191}-\x{1F19A}]/u',
            '/[\x{1F1E6}-\x{1F1FF}]/u',
            '/[\x{1F201}-\x{1F202}]/u',
            '/[\x{1F21A}]/u',
            '/[\x{1F22F}]/u',
            '/[\x{1F232}-\x{1F23A}]/u',
            '/[\x{1F250}-\x{1F251}]/u',
            '/[\x{1F300}-\x{1F321}]/u',
            '/[\x{1F324}-\x{1F393}]/u',
            '/[\x{1F396}-\x{1F397}]/u',
            '/[\x{1F399}-\x{1F39B}]/u',
            '/[\x{1F39E}-\x{1F3F0}]/u',
            '/[\x{1F3F3}-\x{1F3F5}]/u',
            '/[\x{1F3F7}-\x{1F4FD}]/u',
            '/[\x{1F4FF}-\x{1F53D}]/u',
            '/[\x{1F549}-\x{1F54E}]/u',
            '/[\x{1F550}-\x{1F567}]/u',
            '/[\x{1F56F}-\x{1F570}]/u',
            '/[\x{1F573}-\x{1F579}]/u',
            '/[\x{1F587}]/u',
            '/[\x{1F58A}-\x{1F58D}]/u',
            '/[\x{1F590}]/u',
            '/[\x{1F595}-\x{1F596}]/u',
            '/[\x{1F5A5}]/u',
            '/[\x{1F5A8}]/u',
            '/[\x{1F5B1}-\x{1F5B2}]/u',
            '/[\x{1F5BC}]/u',
            '/[\x{1F5C2}-\x{1F5C4}]/u',
            '/[\x{1F5D1}-\x{1F5D3}]/u',
            '/[\x{1F5DC}-\x{1F5DE}]/u',
            '/[\x{1F5E1}]/u',
            '/[\x{1F5E3}]/u',
            '/[\x{1F5E8}]/u',
            '/[\x{1F5EF}]/u',
            '/[\x{1F5F3}]/u',
            '/[\x{1F5FA}-\x{1F64F}]/u',
            '/[\x{1F680}-\x{1F6C5}]/u',
            '/[\x{1F6CB}-\x{1F6D0}]/u',
            '/[\x{1F6E0}-\x{1F6E5}]/u',
            '/[\x{1F6E9}]/u',
            '/[\x{1F6EB}-\x{1F6EC}]/u',
            '/[\x{1F6F0}]/u',
            '/[\x{1F6F3}]/u',
            '/[\x{1F910}-\x{1F918}]/u',
            '/[\x{1F980}-\x{1F984}]/u',
            '/[\x{1F9C0}]/u',
            '/[\x{231A}-\x{231B}]/u',
            '/[\x{23E9}-\x{23EC}]/u',
            '/[\x{23F0}]/u',
            '/[\x{23F3}]/u',
            '/[\x{25FD}-\x{25FE}]/u',
            '/[\x{2614}-\x{2615}]/u',
            '/[\x{2648}-\x{2653}]/u',
            '/[\x{267F}]/u',
            '/[\x{2693}]/u',
            '/[\x{26A1}]/u',
            '/[\x{26AA}-\x{26AB}]/u',
            '/[\x{26BD}-\x{26BE}]/u',
            '/[\x{26C4}-\x{26C5}]/u',
            '/[\x{26CE}]/u',
            '/[\x{26D4}]/u',
            '/[\x{26EA}]/u',
            '/[\x{26F2}-\x{26F3}]/u',
            '/[\x{26F5}]/u',
            '/[\x{26FA}]/u',
            '/[\x{26FD}]/u',
            '/[\x{2705}]/u',
            '/[\x{270A}-\x{270B}]/u',
            '/[\x{2728}]/u',
            '/[\x{274C}]/u',
            '/[\x{274E}]/u',
            '/[\x{2753}-\x{2755}]/u',
            '/[\x{2757}]/u',
            '/[\x{2795}-\x{2797}]/u',
            '/[\x{27B0}]/u',
            '/[\x{27BF}]/u',
            '/[\x{2B1B}-\x{2B1C}]/u',
            '/[\x{2B50}]/u',
            '/[\x{2B55}]/u',
            '/[\x{1F004}]/u',
            '/[\x{1F0CF}]/u',
            '/[\x{1F18E}]/u',
            '/[\x{1F191}-\x{1F19A}]/u',
            '/[\x{1F1E6}-\x{1F1FF}]/u',
            '/[\x{1F201}]/u',
            '/[\x{1F21A}]/u',
            '/[\x{1F22F}]/u',
            '/[\x{1F232}-\x{1F236}]/u',
            '/[\x{1F238}-\x{1F23A}]/u',
            '/[\x{1F250}-\x{1F251}]/u',
            '/[\x{1F300}-\x{1F320}]/u',
            '/[\x{1F32D}-\x{1F335}]/u',
            '/[\x{1F337}-\x{1F37C}]/u',
            '/[\x{1F37E}-\x{1F393}]/u',
            '/[\x{1F3A0}-\x{1F3CA}]/u',
            '/[\x{1F3CF}-\x{1F3D3}]/u',
            '/[\x{1F3E0}-\x{1F3F0}]/u',
            '/[\x{1F3F4}]/u',
            '/[\x{1F3F8}-\x{1F43E}]/u',
            '/[\x{1F440}]/u',
            '/[\x{1F442}-\x{1F4FC}]/u',
            '/[\x{1F4FF}-\x{1F53D}]/u',
            '/[\x{1F54B}-\x{1F54E}]/u',
            '/[\x{1F550}-\x{1F567}]/u',
            '/[\x{1F595}-\x{1F596}]/u',
            '/[\x{1F5FB}-\x{1F64F}]/u',
            '/[\x{1F680}-\x{1F6C5}]/u',
            '/[\x{1F6CC}]/u',
            '/[\x{1F6D0}]/u',
            '/[\x{1F6EB}-\x{1F6EC}]/u',
            '/[\x{1F910}-\x{1F918}]/u',
            '/[\x{1F980}-\x{1F984}]/u',
            '/[\x{1F9C0}]/u',
            '/[\x{1F3FB}-\x{1F3FF}]/u',
            '/[\x{261D}]/u',
            '/[\x{26F9}]/u',
            '/[\x{270A}-\x{270D}]/u',
            '/[\x{1F385}]/u',
            '/[\x{1F3C3}-\x{1F3C4}]/u',
            '/[\x{1F3CA}-\x{1F3CB}]/u',
            '/[\x{1F442}-\x{1F443}]/u',
            '/[\x{1F446}-\x{1F450}]/u',
            '/[\x{1F466}-\x{1F469}]/u',
            '/[\x{1F46E}]/u',
            '/[\x{1F470}-\x{1F478}]/u',
            '/[\x{1F47C}]/u',
            '/[\x{1F481}-\x{1F483}]/u',
            '/[\x{1F485}-\x{1F487}]/u',
            '/[\x{1F4AA}]/u',
            '/[\x{1F575}]/u',
            '/[\x{1F590}]/u',
            '/[\x{1F595}-\x{1F596}]/u',
            '/[\x{1F645}-\x{1F647}]/u',
            '/[\x{1F64B}-\x{1F64F}]/u',
            '/[\x{1F6A3}]/u',
            '/[\x{1F6B4}-\x{1F6B6}]/u',
            '/[\x{1F6C0}]/u',
            '/[\x{1F918}]/u',
            '/[\x{1F919}-\x{1F91E}]/u',
            '/[\x{1F926}]/u',
            '/[\x{1F930}]/u',
            '/[\x{1F933}-\x{1F939}]/u',
            '/[\x{1F93C}-\x{1F93E}]/u',
            '/[\x{1F940}-\x{1F945}]/u',
            '/[\x{1F947}-\x{1F94B}]/u',
            '/[\x{1F950}-\x{1F95E}]/u',
            '/[\x{1F980}-\x{1F984}]/u',
            '/[\x{1F985}-\x{1F991}]/u',
            '/[\x{1F9C0}]/u',
            // link
            '/[\x{200D}\x{FE0F}]/u',
        ];

        $clean_text = preg_replace($emojs, '', $text);

        return $clean_text === null ? $text : $clean_text;
    }
}
