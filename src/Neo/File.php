<?php

namespace Neo;

/**
 * 文件处理类
 */
class File
{
    /**
     * 统计文件行数
     *
     * @param string $file 文件
     *
     * @return int 行数
     */
    public static function lines(string $file)
    {
        $lines = 0;

        // 打开文件
        $fp = fopen($file, 'r') or exit('open file failure!');
        if ($fp) {
            while (stream_get_line($fp, 2)) {
                ++$lines;
            }

            fclose($fp);
        }

        return $lines;
    }

    /**
     * 将文件/目录从一个位置复制到另一个位置
     *
     * @param string $source
     * @param string $target
     */
    public static function fullCopy(string $source, string $target)
    {
        if (is_dir($source)) {
            @mkdir($target, 0755, true);

            $d = dir($source);
            while (($entry = $d->read()) !== false) {
                if ($entry[0] === '.') {
                    continue;
                }

                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    static::fullCopy($Entry, $target . '/' . $entry);
                    continue;
                }

                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }

    /**
     * 递归删除一个目录
     *
     * @param string $dirName 目录
     */
    public static function rmdir(string $dirName)
    {
        if ($handle = opendir($dirName)) {
            while (($item = readdir($handle)) !== false) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dirName . '/' . $item)) {
                        static::rmdir($dirName . '/' . $item);
                    } else {
                        @unlink($dirName . '/' . $item);
                    }
                }
            }
            closedir($handle);
            @rmdir($dirName);
        }
    }

    /**
     * 创建目录
     *
     * @param string $dir
     *
     * @return bool
     */
    public static function mkdir(string $dir)
    {
        if (is_dir($dir)) {
            return true;
        }

        $done = @mkdir($dir, 0755, true);

        if ($done) {
            // 创建一个空首页
            file_put_contents($dir . '/index.html', '');
        }

        return $done;
    }

    /**
     * 获取目录（含子目录）下所有文件
     *
     * @param string $dir
     * @param bool   $recursive
     * @param array  $extFilters
     *
     * @return null|array
     */
    public static function getFiles(string $dir, bool $recursive = false, array $extFilters = [])
    {
        if (! is_dir($dir)) {
            return null;
        }

        $files = [];

        $handle = @opendir($dir);

        while (($file = @readdir($handle)) !== false) {
            if ($file[0] === '.') {
                continue;
            }

            $_file = $dir . '/' . $file;

            if (is_dir($_file)) {
                if ($recursive) {
                    $files = array_merge($files, static::getFiles($_file, $recursive, $extFilters));
                }
            } else {
                if (empty($extFilters)) {
                    $files[] = $_file;
                } else {
                    if (in_array(pathinfo($file, PATHINFO_EXTENSION), $extFilters)) {
                        $files[] = $_file;
                    }
                }
            }
        }

        @closedir($handle);

        return $files;
    }

    /**
     * 获取资源校验hash值
     *
     * @param string $fileName 资源路径
     * @param int    $fileSize 资源大小
     *
     * @return string 校验哈希
     */
    public static function getFileHash(string $fileName, int $fileSize)
    {
        if (empty($fileName) || empty($fileSize)) {
            return '';
        }

        $str = '';
        // 步进
        $step = floor($fileSize / 10);

        // 将截取的资源数据拼接起来
        for ($i = 0; $i < 10; ++$i) {
            $str .= file_get_contents($fileName, false, null, $i * $step, 100);
        }

        return md5($str);
    }

    /**
     * 回调函数，文件名中的数字加1
     *
     * @param array $matches
     *
     * @return string
     */
    private static function upcountNameCallback(array $matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';

        return '-' . $index . $ext;
    }

    /**
     * 给文件名生成数字序号，比如:
     * /path/to/file.ext => /path/to/file-1.ext
     * /path/to/file-12.ext => /path/to/file-13.ext
     *
     * @param string $name
     *
     * @return string
     */
    public static function getUniquefileName(string $name)
    {
        while (is_file($name)) {
            $name = preg_replace_callback(
                '/(?:(?:-([\d]+))?(\.[^.]+)?)?$/',
                ['NeoFile', 'upcountNameCallback'],
                $name,
                1
            );
        }

        return $name;
    }

    /**
     * 下载文件
     *
     * @param string $url
     * @param array  $httpinfo
     *
     * @return string
     */
    public static function download(string $url, array &$httpinfo = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resource = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);

        if (intval($httpinfo['http_code']) == 200) {
            return $resource;
        }

        $httpinfo = [];

        return null;
    }

    /**
     * 包装一个带超时的file_get_contents
     *
     * @param string $fileName name of the file to read
     * @param int    $timeout  超时时间,默认10秒
     *
     * @return string
     */
    public static function getContent(string $fileName, int $timeout = 10)
    {
        $timeout = (int) $timeout;

        $ctx = stream_context_create([
            'http' => [
                'timeout' => $timeout,
            ],
        ]);

        return file_get_contents($fileName, 0, $ctx);
    }

    /**
     * 返回 mime 类型
     *
     * @param string $fileName
     *
     * @return string
     */
    public static function getMimeType(string $fileName)
    {
        $mime = null;

        if (file_exists($fileName)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileName);
            finfo_close($finfo);
        }

        return $mime;
    }
}
