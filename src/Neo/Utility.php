<?php

namespace Neo;

/**
 * 工具类
 */
class Utility
{
    /**
     * 是否CLI模式
     *
     * @return bool
     */
    public static function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * XML转换为数组
     *
     * @param string $xml
     *
     * @return mixed
     */
    public static function xml2array(string $xml)
    {
        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 是否MD5字符串
     *
     * @param string $md5 The MD5 string
     *
     * @return bool
     */
    public static function isMD5Str(string $md5)
    {
        return preg_match('#^[a-f0-9]{32}$#', $md5) ? true : false;
    }

    /**
     * 是否有效的EMAIL
     *
     * @param string $email email address
     *
     * @return bool
     */
    public static function isEmail(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    /**
     * 是否有效的URL
     *
     * @param string $link URL
     *
     * @return bool
     */
    public static function isLink(string $link)
    {
        return filter_var($link, FILTER_VALIDATE_URL) ? true : false;
    }

    /**
     * 是否有效的IP
     *
     * @param string $ip IP address
     *
     * @return bool
     */
    public static function isIpAddress(string $ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP) ? true : false;
    }

    /**
     * 多行文字转换为数组
     *
     * @param string $str
     *
     * @return array
     */
    public static function linesToArray(string $str)
    {
        $str = preg_replace(['/\r\n|\r/', '/\n+/'], PHP_EOL, trim($str));

        return array_map('trim', explode(PHP_EOL, $str));
    }

    /**
     * 获取对象/类的"basename"
     *
     * @param mixed $class
     *
     * @return string
     */
    public static function getClassBasename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * 通过管道异步打开进程
     *
     * @param string $cmd
     * @param string $exec
     */
    public static function toPipe(string $cmd, string $exec = '')
    {
        pclose(popen($exec . $cmd . ' & ', 'r'));
    }

    /**
     * 检查PHP版本，如果不符合，则退出
     *
     * @param string $requiredVersion
     */
    public static function checkPHPVersion(string $requiredVersion)
    {
        $phpVersion = phpversion();

        if (version_compare($requiredVersion, $phpVersion, '>')) {
            die(sprintf(
                'Your server is running PHP version %s but NeoFrame requires at least %s.',
                $phpVersion,
                $requiredVersion
            ));
        }
    }

    /**
     * 获取服务器名称
     *
     * @return false|string
     */
    public static function gethostname()
    {
        return ($_SERVER['NEO_HOST'] ?? false) ?: gethostname();
    }
}
