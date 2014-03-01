<?php

/**
 * Mengolah citra atau data gambar
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Helpers;

/**
 * Kelas pembantu yang bertugas mempermudah pengolahan citra atau data gambar
 */
class Image
{

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * Mengubah resolusi suatu citra/gambar dan menyimpannya sebagai file gambar baru
     * @since 1.0.0
     * @param $source adalah lokasi file gambar yang hendak diubah
     * @param $destination adalah lokasi menyimpan hasil pengubahan file gambar
     * @param $size adalah nilai ukuran resolusi baru untuk gambar
     */
    public function resize($source, $destination, $size)
    {
        $info = getimagesize($source);
        $width = $info[0];
        $height = $info[1];
        if($width < $size) return;

        $new_width = $size;
        $new_height = ($height / $width) * $size;
        $new_image = imagecreatetruecolor($new_width, $new_height);

        switch ($info['mime']) {
            case 'image/png':
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
                $image = imagecreatefrompng($source);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
            default:
                $image = imagecreatefromjpeg($source);
        }
        
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($info['mime']) {
            case 'image/png':
                imagepng($new_image, $destination);
                break;
            case 'image/gif':
                imagegif($new_image, $destination);
                break;
            default:
                imagejpeg($new_image, $destination, 100);
        }
        imagedestroy($image);
    }

}