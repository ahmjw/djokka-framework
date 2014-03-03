<?php

/**
 * Mengelola berkas yang terdapat di dalam web
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Helpers;

use Djokka\Helpers\String;

/**
 * Kelas pembantu yang bertugas mengelola berkas yang terdapat di dalam web
 */
class File
{
    //use TShortcut;

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
     * @param $class adalah nama kelas (opsional)
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
     * Mengubah suatu string menjadi format path yang benar
     * @param string $path Lokasi direktori/berkas yang akan dibenarkan
     * @since 1.0.3
     * @return string
     */
    public function realPath($path) {
        return preg_replace("/([\/\\\]+)/i", DS, $path);
    }

    /**
     * Mengambil lokasi folder data
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function dataDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('data_path') . DS);
    }

    /**
     * Mengambil lokasi folder konfigurasi
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function configDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('config_path') . DS);
    }

    /**
     * Mengambil lokasi folder komponen
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function componentDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('component_path') . DS);
    }

    /**
     * Mengambil lokasi folder aset
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function assetDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('asset_path') . DS);
    }

    /**
     * Mengambil lokasi folder tema
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function themeDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('theme_path') . DS);
    }

    /**
     * Mengambil lokasi folder model
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function moduleDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('module_path') . DS);
    }

    /**
     * Mengambil lokasi folder modul
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function modelDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            DS.Config::getInstance()->getData('model_path') . DS);
    }

    /**
     * Mengambil lokasi folder plugin
     * @since 1.0.1
     * @return string lokasi folder
     */
    public function pluginDir()
    {
        return $this->realPath(Config::getInstance()->getData('dir') . DS . Config::getInstance()->getData('app_path') .
            Config::getInstance()->getData('plugin_path') . DS);
    }

    /**
     * Membuat file baru atau menulis suatu konten ke dalam suatu file
     * @since 1.0.0
     * @param string $path Lokasi file di dalam folder web
     * @param string $content Teks yang akan dimasukkan ke dalam file tersebut
     * @param string $mode Mode penulisan berkas
     * file
     */
    public function write($path, $content, $mode = 'w')
    {
        $dir = String::getInstance()->unlastPart(DS, $path);
        if(!file_exists($dir))
            throw new \Exception("Directory not found in path $dir", 404);
        $handle = fopen($path, $mode);
        if(!$handle)
            throw new \Exception("Cannot open file in path $path", 500);
        fwrite($handle, $content);
        fclose($handle);
    }

    /**
     * Menyalin/copy isi dalam direktori ke direktori lainnya
     * @param string $src Direktori asal
     * @param string $dst Direktori tujuan
     * @since 1.0.3
     * @return void
     */
    public function copyDir($src, $dst)
    {
        $dir = opendir($src);
        if (!file_exists($dst)) {
            mkdir($dst);
        }
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                $src_path = $this->realPath($src . '/' . $file);
                $dst_path = $this->realPath($dst . '/' . $file);

                if ( is_dir($src_path) ) { 
                    $this->copyDir($src_path, $dst_path);
                } else { 
                    copy($src_path, $dst_path);
                }
            } 
        } 
        closedir($dir); 
    }  

    /**
     * Membuat folder baru di dalam folder web
     * @since 1.0.0
     * @param $path adalah lokasi folder yang hendak dibuat
     */
    public function makeDir($path)
    {
        $real_path = String::getInstance()->realPath($path);
        $dir = Config::getInstance()->getData('dir');
        $real_path = str_replace($dir, null, $real_path);
        foreach (explode(DS, $real_path) as $path) {
            if(!empty($path)) {
                $temp .= DS.$path;
                $scanned = $dir.$temp;
                if(!file_exists($scanned)) {
                    mkdir($scanned);
                }
            }
        }
    }

    /**
     * Mengunduh/download file dalam modus binary
     * @since 1.0.0
     * @param string $path Lokasi file pada folder web
     * @param string $name Nama ketika file didownload/diunduh
     * @param string $mime MIME-type ketika file didownload/diunduh
     * @return string
     */
    public function download($path, $name, $mime = 'application/octet-stream')
    {
        $info = pathinfo($path);
        $name = String::getInstance()->slugify($name).'.'.$info['extension'];
        header('Content-type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Length: ' . filesize($path));
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        echo file_get_contents($path);
    }

    /**
     * Mengambil nama-nama file yang terdapat di dalam suatu folder
     * @since 1.0.0
     * @param $path adalah lokasi folder yang ingin dibaca isinya
     * @param $args adalah parameter tambahan yang ingin dimasukkan untuk pembacaan
     * isi folder
     * @return daftar nama-nama file dalam bentuk array
     */
    public function getFiles($path, $args = array())
    {
        $files = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $realpath = $path.DS.$entry;
                    if(is_dir($realpath)) {
                        // Jika pembacaan hendak dilakukan secara rekursif
                        if(isset($args['recursively']) && $args['recursively'] === true) {
                            $files = $this->getFiles($realpath, $args);
                        } else {
                            $this->getFiles($realpath, $args);
                        }
                    }
                    if(is_file($realpath)) {
                        if(isset($args['full_path']) && $args['full_path'] === true) {
                            $files[] = $realpath;
                        } else {
                            $files[] = $entry;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }
}