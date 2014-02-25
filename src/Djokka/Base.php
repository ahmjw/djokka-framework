<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \
 * @version 1.0.0
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

/**
 * Kelas Djokka adalah kelas inti framework. Dimuat pertama kali oleh
 * index.php pada root web. Kelas ini mengendalikan keseluruhan sistem.
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Base
{
    /**
     * @var Menampung instance dari kelas induk kontroller
     * @since 1.0.0
     * @access private
     */
    private static $core;

    private static $errors = array();

    /**
     * Memuat pustaka yang terdapat di dalam framework
     * @since 1.0.0
     * @param $subclass adalah nama kelas pustaka framework
     * @return objek instance kelas pustaka framework
     */
    public function __invoke($subclass)
    {
        $class_map = Config::get()->getClassMap();
        if(!isset($class_map[$subclass])) {
            throw new \Exception('Class library with name '.$subclass.' not found', 500);
        }
        return call_user_func(array($class_map[$subclass], 'get'));
    }

    public function lib($subclass)
    {
        $class_map = Config::get()->getClassMap();
        if(!isset($class_map[$subclass])) {
            throw new \Exception('Class library with name '.$subclass.' not found', 500);
        }
        return call_user_func(array($class_map[$subclass], 'get'));
    }

    /**
     * Mengambil instance kelas induk kontroller
     * @since 1.0.0
     * @return objek instance kelas induk kontroller
     */
    public static function getCore()
    {
        return self::$core;
    }

    /**
     * Menentukan suatu kelas anak sebagai kelas induk kontroller
     * @since 1.0.0
     * @param $core adalah instance kelas anak
     */
    public static function setCore($core)
    {
        self::$core = $core;
    }

    /**
     * Mengamankan string dengan slashing dan HTML entitying
     * @since 1.0.0
     * @param $str adalah string yang akan diamankan
     * @return string hasil pengamanan
     */
    public function securify($str)
    {
        return htmlentities(addslashes($str));
    }

    public function configDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('config_path').DS);
    }

    /**
     * Mengambil lokasi folder komponen
     * @since 1.0.0
     * @return string lokasi folder
     */
    public function componentDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('component_path').DS);
    }

    /**
     * Mengambil lokasi folder tema
     * @since 1.0.0
     * @return string lokasi folder
     */
    public function themeDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('theme_path').DS);
    }

    /**
     * Mengambil lokasi URL tema
     * @since 1.0.0
     * @param $url adalah alamat URL jika hendak menggunakan lokasi eksternal
     * @return string lokasi URL
     */
    public function themeUrl($url = null)
    {
        if(($theme_url = $this->config('theme_url')) == null) {
            $theme_url = Route::get()->urlPath($this->themeDir());
        }
        return $theme_url.$this->theme().'/'.$url;
    }

    /**
     * Mengambil lokasi folder aset
     * @since 1.0.0
     * @return string lokasi folder
     */
    public function assetDir()
    {
        return $this->realPath($this->config('dir').DS.$this->config('app_path').$this->config('asset_path').DS);
    }

    /**
     * Mengambil lokasi URL aset
     * @since 1.0.0
     * @param $url adalah alamat URL jika hendak menggunakan lokasi eksternal
     * @return string lokasi URL
     */
    public function assetUrl($url = null)
    {
        if(($asset_url = $this->config('asset_url')) == null) {
            $asset_url = Route::get()->urlPath($this->assetDir());
        }
        return $asset_url.$url;
    }

    /**
     * Mengambil lokasi folder modul
     * @since 1.0.0
     * @return string lokasi folder
     */
    public function moduleDir()
    {
        $dir = $this->defval($this->config('ref_dir'), $this->config('dir'));
        return $this->realPath($dir.DS.$this->config('app_path').$this->config('module_path').DS);
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
     * @since 1.0.0
     * @param $url adalah alamat URL lain target pengalihan halaman
     * @param $params adalah parameter tambahan untuk alamat URL
     */
    public function redirect($url = null, $params = array())
    {
        if($url == -1) {
            header('Location: '.$_SERVER['HTTP_REFERER']);
        } else {
            header('Location: '.$this->link($url, $params));
        }
    }

    public function using($class, &$var = null) {
        $path = $this->componentDir().$class.'.php';
        if(!file_exists($path)) {
            throw new \Exception("Failed to importing object file. File not found in path $path", 404);
        }
        include_once($path);
        if(preg_match('/[a-zA-Z0-9_]+\/([a-zA-Z0-9_]+$)/i', $class, $match)) {
            $class = $match[1];
        }
        if(!class_exists($class)) {
            throw new \Exception("Class $class is not defiend in file $path", 500);
        }
        $var = new $class;
    }

    /**
     * Mengecek otorisasi suatu pengakses web
     * @since 1.0.0
     * @param $type adalah tipe user yang telah ditentukan untuk penyaringan
     * @return boolean -> user telah terotorisasi atau belum
     */
    public function authorized($type = null)
    {
        if($type == null) {
            return Session::get()->exists('user');
        } else {
            if($data = $this->user($type)) {
                return !empty($data);
            }
        }
    }

    /**
     * Mengambil nilai default antara dua kemungkinan
     * @since 1.0.0
     * @param $data adalah data yang akan dicek kekosongannya
     * @param $default adalah nilai default ketika data kosong
     * @return data atau nilai default
     */
    public function defval($data, $default)
    {
        return $data != null ? $data : $default; 
    }

    public function realPath($path) {
        return preg_replace("/([\/\\\]+)/i", DS, $path);
    }

    public function dateFormat($format, $date_str) {
        return date($format, strtotime($date_str));
    }

    /**
     * Sub fungsi kelas
     */
    public function config() {
        switch (func_num_args()) {
            case 0:
                return Config::get()->getConfig();
            case 1:
                if(!is_array(func_get_arg(0))) {
                    return Config::get()->getData(func_get_arg(0));
                } else {
                    return Config::get()->merge(func_get_arg(0));
                }
            case 2:
                Config::get()->setData(func_get_arg(0), func_get_arg(1));
                break;
        }
    }

    public function session() {
        switch (func_num_args()) {
            case 0:
                return Session::get()->getSession();
            case 1:
                return Session::get()->getData(func_get_arg(0));
            case 2:
                Session::get()->setData(func_get_arg(0), func_get_arg(1));
                break;
        }
    }

    public function user() {
        switch (func_num_args()) {
            case 0:
                return User::get()->getUser();
            case 1:
                return User::get()->getUserByType(func_get_arg(0));
        }
    }

    public function model($name, $is_new = false) {
        return Model::get()->load($name, $is_new);
    }

    public function pager() {
        switch (func_num_args()) {
            case 0:
                return Pager::get()->result();
            case 1:
                return Pager::get()->init(func_get_args());
        }
    }

    public function getUrl()
    {
        return Route::get()->getUrl();
    }

    /**
     * Membentuk alamat URL berdasarkan lokasi modul
     * @since 1.0.0
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
                return Linker::get()->getLink(func_get_arg(0));
            case 2:
                if(is_array(func_get_arg(0))) {
                    return Linker::get()->appendGet(func_get_arg(0));
                } else {
                    return Linker::get()->getLinkParameter(func_get_arg(0), func_get_arg(1));
                }
        }
    }

}