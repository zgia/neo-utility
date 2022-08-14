<?php

namespace Neo;

/**
 * 图片操作类
 */
class Image
{
    public const IMAGE_TYPE = [
        '1' => 'gif',
        '2' => 'jpeg',
        '3' => 'png',
    ];

    /**
     * 通过imagecreatefromxxx获取图片文件的资源
     *
     * @param string $fileName
     * @param int    $width
     * @param int    $height
     *
     * @return resource|\GDImage|false
     */
    public static function getResource(string $fileName, int &$width = 0, int &$height = 0)
    {
        // 判断原始文件是否存在和是否为文件
        if (! file_exists($fileName) || ! is_file($fileName)) {
            return false;
        }

        // 获取图片信息
        $im = false;

        if ($data = @getimagesize($fileName)) {
            $func = 'imagecreatefrom' . static::IMAGE_TYPE[$data[2]];

            if (function_exists($func)) {
                $im = @$func($fileName);

                $width = $data[0];
                $height = $data[1];
            }
        }

        return $im;
    }

    /**
     * 按照比例重新缩略图的尺寸
     *
     * @param int $width
     * @param int $height
     * @param int $thumbWidth
     * @param int $thumbHeight
     *
     * @return array
     */
    public static function calculateWidthHeight(int $width, int $height, int $thumbWidth = 0, int $thumbHeight = 0)
    {
        if (($thumbWidth && $width > $thumbWidth) || ($thumbHeight && $height > $thumbHeight)) {
            $resizeWidthTag = false;
            $resizeHeightTag = false;
            $widthRatio = 0;
            $heightRatio = 0;

            if ($thumbWidth && $width > $thumbWidth) {
                $widthRatio = $thumbWidth / $width;
                $resizeWidthTag = true;
            }

            if ($thumbHeight && $height > $thumbHeight) {
                $heightRatio = $thumbHeight / $height;
                $resizeHeightTag = true;
            }

            $ratio = 1;
            if ($resizeWidthTag && $resizeHeightTag) {
                $ratio = $widthRatio < $heightRatio ? $widthRatio : $heightRatio;
            } elseif ($resizeWidthTag && ! $resizeHeightTag) {
                $ratio = $widthRatio;
            } elseif ($resizeHeightTag && ! $resizeWidthTag) {
                $ratio = $heightRatio;
            }

            return [$width * $ratio, $height * $ratio];
        }

        return [$width, $height];
    }

    /**
     * 根据宽高比创建缩略图片
     *
     * @param string $fileName    原文件
     * @param int    $thumbWidth  缩略图宽度
     * @param int    $thumbHeight 缩略图高度
     * @param string $thumbDir    缩略图目录
     * @param int    $cropx       剪裁时，原图x坐标
     * @param int    $cropy       剪裁时，原图y坐标
     *
     * @return null|string 缩略图路径，null表示错误
     */
    public static function thumb(string $fileName, int $thumbWidth = 0, int $thumbHeight = 0, string $thumbDir = '', int $cropx = 0, int $cropy = 0)
    {
        if (! is_file($fileName)) {
            return null;
        }

        $pathinfo = pathinfo($fileName);

        if ($thumbDir) {
            if (! File::mkdir($thumbDir)) {
                return null;
            }
        } else {
            $thumbDir = $pathinfo['dirname'];
        }

        // 原始图片的宽度和高度
        $width = 0;
        $height = 0;

        $im = static::getResource($fileName, $width, $height);

        if (! $im) {
            return null;
        }

        // 生成的缩略图名称
        $thumbFile = $thumbDir . DIRECTORY_SEPARATOR . $pathinfo['fileName'] . '-thumb.' . $pathinfo['extension'];

        [$newWidth, $newHeight] = static::calculateWidthHeight($width, $height, $thumbWidth, $thumbHeight);

        if ($width == $newWidth && $height == $newHeight) {
            copy($fileName, $thumbFile);
        } else {
            $newim = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newim, $im, 0, 0, $cropx, $cropy, $newWidth, $newHeight, $width, $height);

            imagejpeg($newim, $thumbFile);
            imagedestroy($newim);
        }
        imagedestroy($im);

        return file_exists($thumbFile) ? $thumbFile : null;
    }

    /**
     * 为图片增加水印
     *
     * @param string $fileName  需要增加水印的图片
     * @param string $watermask 水印
     * @param int    $pos       水印位置
     *                          #1        #2        #3
     *                          #4        #5        #6
     *                          #7        #8        #9
     *
     * @return string 水印路径
     */
    public static function watermask(string $fileName, string $watermask, int $pos = 5)
    {
        // 获取原始图片的尺寸
        [$img_w, $img_h, $img_type] = getimagesize($fileName);
        $func = 'imagecreatefrom' . static::IMAGE_TYPE[$img_type];
        $srcImage = $func($fileName);

        // 水印图片的尺寸
        [$logo_w, $logo_h, $logo_type] = getimagesize($watermask);
        $func = 'imagecreatefrom' . static::IMAGE_TYPE[$logo_type];
        $wmImage = $func($watermask);

        /**
         * $pos
         *        #1        #2        #3
         *        #4        #5        #6
         *        #7        #8        #9
         */
        // 根据水印位置的不同，计算水印位置
        $x = 0;
        $y = 0;
        switch ($pos) {
            // 左上
            case 1:
                $x = +5;
                $y = +5;
                break;
            // 中上
            case 2:
                $x = ($img_w - $logo_w) / 2;
                $y = +5;
                break;
            // 右上
            case 3:
                $x = $img_w - $logo_w - 5;
                $y = +5;
                break;
            // 左中
            case 4:
                $x = +5;
                $y = ($img_h - $logo_h) / 2;
                break;
            // 中
            case 5:
                $x = ($img_w - $logo_w) / 2;
                $y = ($img_h - $logo_h) / 2;
                break;
            // 右中
            case 6:
                $x = $img_w - $logo_w;
                $y = ($img_h - $logo_h) / 2;
                break;
            // 左下
            case 7:
                $x = +5;
                $y = $img_h - $logo_h - 5;
                break;
            // 中下
            case 8:
                $x = ($img_w - $logo_w) / 2;
                $y = $img_h - $logo_h - 5;
                break;
            // 右下
            case 9:
                $x = $img_w - $logo_w - 5;
                $y = $img_h - $logo_h - 5;
                break;
        }

        // 合并水印
        imagecopy($srcImage, $wmImage, $x, $y, 0, 0, $logo_w, $logo_h);

        // 输出
        $pathinfo = pathinfo($fileName);
        $dest = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['fileName'] . '-water.' . $pathinfo['extension'];
        $createFunc = 'image' . static::IMAGE_TYPE[$img_type];
        $createFunc($srcImage, $dest);

        imagedestroy($srcImage);
        imagedestroy($wmImage);

        return file_exists($dest) ? $dest : null;
    }

    /**
     * 获取照片中的EXIF信息，如果照片不存在或者没有获取到EXIF，返回null，否则返回EXIF。
     * 返回精简的EXIF信息
     *
     * @param string $photo  照片地址（物理全路径）
     * @param array  $origin 照片的完整EXIF信息
     *
     * @return array
     */
    public static function exif(string $photo, array &$origin = [])
    {
        if (! file_exists($photo) || ! is_readable($photo)) {
            return null;
        }

        // 检查文件类型，仅限：JPEG和TIFF
        $it = exif_imagetype($photo);

        if (! ($it == IMAGETYPE_JPEG || $it == IMAGETYPE_TIFF_II || $it == IMAGETYPE_TIFF_MM)) {
            return null;
        }

        $exif = exif_read_data($photo, null, true);

        if (! $exif || ! $exif['EXIF'] || ! $exif['IFD0']) {
            return null;
        }

        unset($exif['EXIF']['MakerNote']);

        $_e = $exif['EXIF'] + $exif['IFD0'];
        if (is_array($exif['COMPUTED'])) {
            $_e['ApertureFNumber'] = $exif['COMPUTED']['ApertureFNumber'];
        }

        // 必须有这些元素才被认为是拍摄的照片
        if (! array_key_exists('Model', $_e) || ! array_key_exists('ExposureTime', $_e) || ! array_key_exists('FNumber', $_e)) {
            return null;
        }

        // 保留完整信息
        $origin = $exif;

        // 只取部分数据
        $brief = [];
        foreach ([
            'ExposureTime',
            'Make',
            'Model',
            'FNumber',
            'ApertureFNumber',
            'ISOSpeedRatings',
            'DateTimeOriginal',
            'FocalLength',
            'Flash',
        ] as $n) {
            $brief[$n] = $_e[$n];
        }

        // 如果没有焦距，则计算出一个
        if (! $brief['ApertureFNumber']) {
            if (strpos($brief['FNumber'], '/') === false) {
                $f = (float) $brief['FNumber'];
            } else {
                $_f = explode('/', $brief['FNumber']);
                $f = $_f[1] ? $_f[0] / $_f[1] : $_f[0];
            }

            $brief['ApertureFNumber'] = 'f' . round($f, 2);
        }

        // 计算焦距
        if ($brief['FocalLength']) {
            if (strpos($brief['FocalLength'], '/') === false) {
                $fl = (float) $brief['FocalLength'];
            } else {
                $_fl = explode('/', $brief['FocalLength']);
                $fl = $_fl[1] ? $_fl[0] / $_fl[1] : $_fl[0];
            }

            $brief['FocalLength'] = round($fl, 2);
        }

        unset($_e, $brief['FNumber']);

        return $brief;
    }
}
