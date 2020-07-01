<?php

/**
 * @backupGlobals disabled
 *
 * @internal
 * @coversNothing
 */
class ImageTest extends BaseTester
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testThumb()
    {
        $src   = ABS_PATH . '/Neo/hancock.jpg';
        $thumb = ABS_PATH . '/Neo/hancock-thumb.jpg';

        $dest = \Neo\Image::thumb($src, 600, 800);

        $this->assertEquals($thumb, $dest);

        [$w, $h] = getimagesize($thumb);
        $this->assertEquals(600, $w);
        $this->assertEquals(800, $h);
    }

    public function testWatermast()
    {
        $src       = ABS_PATH . '/Neo/hancock.jpg';
        $watermask = ABS_PATH . '/Neo/water.jpg';
        $water     = ABS_PATH . '/Neo/hancock-water.jpg';

        $dest = \Neo\Image::watermask($src, $watermask, 9);

        $this->assertEquals($water, $dest);
    }

    public function testExif()
    {
        $exif = \Neo\Image::exif(ABS_PATH . '/Neo/hancock.jpg');

        $this->assertEquals('Apple', $exif['Make']);
        $this->assertEquals('iPhone 8 Plus', $exif['Model']);
        $this->assertEquals('f/1.8', $exif['ApertureFNumber']);
        $this->assertEquals('1/15', $exif['ExposureTime']);
        $this->assertEquals('100', $exif['ISOSpeedRatings']);
        $this->assertEquals('3.99', $exif['FocalLength']);
        $this->assertEquals('24', $exif['Flash']);
    }
}
