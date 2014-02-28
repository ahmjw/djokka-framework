<?php

/**
 * File ini dapat memberikan jalan pintas bagi kelas lain untuk mengakses fungsi penting
 * @since 1.0.3
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @since 1.0.3
 * @version 1.0.3
 */
namespace Djokka;

use Djokka\Route;
use Djokka\Controller;
use Djokka\Controller\Linker;
use Djokka\View;
use Djokka\Model;
use Djokka\Model\Pager;
use Djokka\View\Asset;
use Djokka\Helpers\Config;
use Djokka\Helpers\Session;
use Djokka\Helpers\User;
use Djokka\Model\SchemaCollection;

/**
 * Trait yang digunakan untuk menyediakan fungsi penting dengan jalan pintas
 */
trait TShortcut
{
    /**
     * Memuat pustaka yang terdapat di dalam framework
     * @since 1.0.3
     * @param mixed $subclass adalah nama kelas pustaka framework
     * @return object Objek instance kelas pustaka framework
     */
    public function lib($subclass)
    {
        $class_map = Config::getInstance()->getClassMap();
        if (!isset($class_map[$subclass])) {
            throw new \Exception('Class library with name '.$subclass.' not found', 500);
        }
        return call_user_func(array($class_map[$subclass], 'getInstance'));
    }

	/**
     * Mengamankan string dengan slashing dan HTML entitying
     * @since 1.0.3
     * @param $str adalah string yang akan diamankan
     * @return string hasil pengamanan
     */
    public function securify($str)
    {
        return htmlentities(addslashes($str));
    }

    /**
     * Mengambil lokasi folder data
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function dataDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('data_path').DS);
    }

    /**
     * Mengambil lokasi folder konfigurasi
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function configDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('config_path').DS);
    }

    /**
     * Mengambil lokasi folder komponen
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function componentDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('component_path').DS);
    }

    /**
     * Mengambil lokasi folder tema
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function themeDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('theme_path').DS);
    }

    /**
     * Mengambil lokasi URL tema
     * @since 1.0.3
     * @param $url adalah alamat URL jika hendak menggunakan lokasi eksternal
     * @return string lokasi URL
     */
    public function themeUrl($url = null)
    {
        if (($theme_url = $this->config('theme_url')) == null) {
            $theme_url = Route::getInstance()->urlPath($this->themeDir());
        }
        return $theme_url.$this->theme().'/'.$url;
    }

    /**
     * Mengambil lokasi folder aset
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function assetDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('asset_path').DS);
    }

    /**
     * Mengambil lokasi URL aset
     * @since 1.0.3
     * @param $url adalah alamat URL jika hendak menggunakan lokasi eksternal
     * @return string lokasi URL
     */
    public function assetUrl($url = null)
    {
        if (($asset_url = $this->config('asset_url')) == null) {
            $asset_url = Route::getInstance()->urlPath($this->assetDir());
        }
        return $asset_url.$url;
    }

    /**
     * Mengambil lokasi folder model
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function moduleDir()
    {
        $dir = $this->defval($this->config('ref_dir'), $this->config('dir'));
        return $this->realPath($dir.DS.$this->config('app_path').$this->config('module_path').DS);
    }

    /**
     * Mengambil lokasi folder modul
     * @since 1.0.3
     * @return string lokasi folder
     */
    public function modelDir()
    {
        $dir = $this->defval($this->config('ref_dir'), $this->config('dir'));
        return $this->realPath($dir.DS.$this->config('app_path').DS.$this->config('model_path').DS);
    }

    /**
     * Mengambil lokasi folder plugin
     * @since 1.0.1
     * @return string lokasi folder
     */
    public function pluginDir()
    {
        $dir = $this->defval($this->config('ref_dir'), $this->config('dir'));
        return $this->realPath($dir.DS.$this->config('app_path').$this->config('plugin_path').DS);
    }

    /**
     * Mengalihkan ke halaman lain
     * @since 1.0.3
     * @param $url adalah alamat URL lain target pengalihan halaman
     * @param $params adalah parameter tambahan untuk alamat URL
     */
    public function redirect($url = null, $params = array())
    {
        if ($url == -1) {
            header('Location: '.$_SERVER['HTTP_REFERER']);
        } else {
            header('Location: '.$this->link($url, $params));
        }
    }

    /**
     * Memuat/impor berkas kelas
     * @since 1.0.1
     * @param string $class Nama kelas
     * @param mixed $var Variabel yang akan diisi
     * @return string Lokasi folder
     */
    public function using($class, &$var = null) 
    {
        $path = $this->componentDir().$class.'.php';
        if (!file_exists($path)) {
            throw new \Exception("Failed to importing object file. File not found in path $path", 404);
        }
        include_once($path);
        if (preg_match('/[a-zA-Z0-9_]+\/([a-zA-Z0-9_]+$)/i', $class, $match)) {
            $class = $match[1];
        }
        if (!class_exists($class)) {
            throw new \Exception("Class $class is not defiend in file $path", 500);
        }
        $var = new $class;
    }

    /**
     * Mengecek otorisasi suatu user web
     * @since 1.0.3
     * @param $type adalah tipe user yang telah ditentukan untuk penyaringan
     * @return boolean -> user telah terotorisasi atau belum
     */
    public function authorized($type = null)
    {
        if ($type == null) {
            return Session::getInstance()->exists('user');
        } else {
            if ($data = $this->user($type)) {
                return !empty($data);
            }
        }
    }

    /**
     * Mengambil nilai default antara dua kemungkinan
     * @since 1.0.3
     * @param $data adalah data yang akan dicek kekosongannya
     * @param $default adalah nilai default ketika data kosong
     * @return data atau nilai default
     */
    public function defval($data, $default)
    {
        return $data != null ? $data : $default; 
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
     * Membaca, menambah atau mengubah nilai konfigurasi
     */
    public function config() 
    {
        switch (func_num_args()) {
            case 0:
                return Config::getInstance()->getConfig();
            case 1:
                if (!is_array(func_get_arg(0))) {
                    return Config::getInstance()->getData(func_get_arg(0));
                } else {
                    return Config::getInstance()->merge(func_get_arg(0));
                }
            case 2:
                Config::getInstance()->setData(func_get_arg(0), func_get_arg(1));
                break;
        }
    }

    /**
     * Membaca, menambah atau mengubah nilai sesi
     */
    public function session() 
    {
        switch (func_num_args()) {
            case 0:
                return Session::getInstance()->getSession();
            case 1:
                return Session::getInstance()->getData(func_get_arg(0));
            case 2:
                Session::getInstance()->setData(func_get_arg(0), func_get_arg(1));
                break;
        }
    }

    /**
     * Membaca, menambah atau mengubah user yang sedang login
     */
    public function user() 
    {
        switch (func_num_args()) {
            case 0:
                return User::getInstance()->getUser();
            case 1:
                return User::getInstance()->getUserByType(func_get_arg(0));
        }
    }

    /**
     * Memuat model
     * @param mixed $name string Nama model yang akan dimuat
     * @param optional $is_new boolean Model dimuat sebagai data baru atau data lama
     * @return object
     */
    public function model($name, $is_new = false)
    {
        if (preg_match('/^\/([a-zA-Z][a-zA-Z0-9]+)$/i', $name, $match)) {
            $path = $this->modelDir()."$match[1].php";
            $class = 'Djokka\\Models\\'.$match[1];
        } else {
            $path = $this->moduleDir().$this->config('module').DS."models".DS."$name.php";
            $class = 'Djokka\\Models\\'.$name;
        }
        $path = $this->realPath($path);
        if (!file_exists($path)) {
            throw new \Exception("Model file not found in path $path", 404);
        }
        include_once($path);
        if (!class_exists($class)) {
            throw new \Exception("Class $class is not defined in file $path", 500);
        }
        $object = new $class;
        $object->dataset('module', $name);
        if ((bool)$is_new && $object instanceof ActiveRecord) {
            $object->setNew();
            foreach ($object->schema('fields') as $field) {
                if (!isset($object->{$field})) {
                    $object->{$field} = null;
                }
            }
        }
        return $object;
    }

    /**
     * Menginisialisai pager (pembagi halaman) dan memberikan nilai limit pada model
     */
    public function pager() 
    {
        switch (func_num_args()) {
            case 0:
                return Pager::getInstance()->result();
            case 1:
                return Pager::getInstance()->init(func_get_args());
        }
    }

    /**
     * Mengambil alamat URL untuk halaman yang sedang diakses
     * @return string
     */
    public function getUrl()
    {
        return Route::getInstance()->getUrl();
    }

    /**
     * Membentuk alamat URL berdasarkan lokasi modul
     * @since 1.0.3
     * @param $module adalah lokasi modul
     * @param $params adalah parameter tambahan untuk dimasukkan ke URL
     * @return string lokasi URL
     */
    public function link()
    {
        switch (func_num_args()) {
            case 0:
                return $this->getUrl();
            case 1:
                return Linker::getInstance()->getLink(func_get_arg(0));
            case 2:
                if (is_array(func_get_arg(0))) {
                    return Linker::getInstance()->appendGet(func_get_arg(0));
                } else {
                    return Linker::getInstance()->getLinkParameter(func_get_arg(0), func_get_arg(1));
                }
        }
    }
}