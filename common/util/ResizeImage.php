<?php
namespace common\util;

use app\common\models\base\AbstractObject;

class ResizeImage extends AbstractObject
{
    public $image;
    public $image_type;
    public $width;
    public $height;

    function loadFile($filename)
    {
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        switch ($this->image_type) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($filename);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($filename);
                break;
        }
        $this->getWidth();
        $this->getHeight();
    }

    function toSaveFile($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        switch ($this->image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->image, $filename, $compression);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->image, $filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->image, $filename);
                break;
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }

    }

    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        $kek = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $kek);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    function getHeight()
    {
        $this->width = imagesy($this->image);
        return $this->width;
    }

    function getWidth()
    {
        $this->height = imagesx($this->image);
        return $this->height;
    }

    function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }

    function crop($dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0, $dst_w = 200, $dst_h = 200)
    {
        $new_image = imagecreatetruecolor($dst_w, $dst_h);
        $kek = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $kek);
        imagecopyresampled($new_image, $this->image, $dst_x, $dst_y, $src_x, $src_y, $this->width, $this->height, $this->width, $this->height);
        $this->image = $new_image;
    }
}

?>