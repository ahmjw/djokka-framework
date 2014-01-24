<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.1
 */

namespace Djokka;

use Djokka\Route;
use Djokka\View\Asset;
use Djokka\Helpers\String;
use Djokka\Helpers\User;
use Djokka\Controller\Hmvc;
use Djokka\Controller\Modular;
use Djokka\Controller\Plugin;

/**
 * Kelas Djokka\Controller adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Controller extends \Djokka
{
    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.1
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.1
     * @param $class adalah nama kelas (opsional)
     * @return objek instance kelas
     */
    public static function get($class = __CLASS__)
    {
        if(self::$instance == null) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function view($name, $params = array())
    {
        View::get()->mergeView($name, $params);
    }

    public function getView($name, $params = array())
    {
        return View::get()->getView($this, $name, $params);
    }

    /**
     * Membaca file PHP menggunakan buffer
     * @since 1.0.0
     * @param $path adalah lokasi file yang hendak dibaca
     * @param $params adalah parameter tambahan yang hendak dimasukkan ke dalam file
     * @return string hasil pembacaan buffer
     */
    public function render($path, $params = array())
    {
        ob_start();
        if(!empty($params)) {
            extract($params, EXTR_PREFIX_SAME, 'dj_');
        }
        if($return = include($path)) {
            if($return != 1) {
                return $return;
            }
        }
        return ob_get_clean();
    }

    /**
     * Mengambil atau menentukan konten web
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka dia memberikan konten web
     * - Jika memasukkan satu parameter, maka dia menentukan konten web
     * @return string konten web | void
     */
    public function getContent()
    {
        return View::get()->getContent();
    }

    /**
     * Mengambil potongan URL
     * @since 1.0.0
     * @param $i adalah indeks potongan URL yang berupa angka
     * @return string potongan URL
     */
    public function uri($i = null)
    {
        $uris = Route::get()->uris;
        if($i === null) {
            return $uris;
        } else {
            return $uris[$i];
        }
    }

    /**
     * Mengambil nilai parameter yang masuk melalui rute
     * @since 1.0.0
     * @param $var adalah nama indeks parameter berupa string
     * @return nilai parameter
     */
    public function param($var = null)
    {
        $params = Route::get()->url_params;
        if($var !== null) {
            if($this->config('route_format') == 'path') {
                if(count($params) > 0)
                    return is_numeric($var) ? $params[$var] : $params[array_search($var, $params)+1];
            } else {
                return $params[$var];
            }
        } else {
            return $params;
        }
    }

    public function js($code) {
        Asset::get()->js($code);
    }

    public function css($code) {
        Asset::get()->css($code);
    }

    public function asset($url) {
        Asset::get()->add($url);
    }

    public function route() {
        switch (func_num_args()) {
            case 2:
                $this->config('router_action', func_get_arg(0));
                $this->config('router_params', func_get_arg(1));
                break;
            case 3:
                if(preg_match('/'.func_get_arg(1).'/i', Route::get()->getUri(), $matches)) {
                    $this->config('router_action', func_get_arg(0));
                    $matches = array_slice($matches, 1);
                    $properties = func_get_arg(2);
                    $params = array();
                    foreach ($matches as $key => $match) {
                        $params[$properties[$key]] = $match;
                    }
                    $this->config('router_params', $params);
                    return true;
                }
                break;
        }
    }

    /**
     * Mengambil atau menentukan nama tema yang sedang aktif
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka dia akan memberikan nama tema
     * - Jika memasukkan satu parameter, maka dia akan menentukan tema yang
     *   sedang aktif.
     * @return string nama tema | void
     */
    public function theme()
    {
        if(func_num_args() == 0) {
            return $this->config('theme');
        } else {
            return $this->config('theme', func_get_arg(0));
        }
    }

    /**
     * Mengambil atau menentukan nama layout yang sedang aktif
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka dia akan memberikan nama layout
     * - Jika memasukkan satu parameter, maka dia akan menentukan layout yang
     *   sedang aktif.
     * @return string nama layout | void
     */
    public function layout()
    {
        if(func_num_args() == 0) {
            return $this->config('layout');
        } else {
            return $this->config('layout', func_get_arg(0));
        }
    }

    /**
     * Mengambil lokasi URL basis/root web
     * @since 1.0.0
     * @param $url adalah tambahan ke belakang alamat URL
     * @return string lokasi URL
     */
    public function baseUrl($url = null)
    {
        return Route::get()->base_url.'/'.$url;
    }

    /**
     * Mengambil lokasi URL basis/root web
     * @since 1.0.1
     * @param $url adalah tambahan ke belakang alamat URL
     * @return string lokasi URL
     */
    public function plugin($name) 
    {
        return $this->import('plugin.'.$name);
    }

    public function isPlugin($route) 
    {
        if(preg_match('/^plugin\.([a-zA-Z0-9_\/\-]+)/i', $route, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }

    /**
     * Memanggil atau mengeksekusi suatu aksi/modul
     * @since 1.0.0
     * @param $router adalah alamat aksi/modul yang akan dieksekusi
     * @param $params adalah parameter tambahan yang dimasukkan ke dalam aksi/modul
     * @return string berupa hasil pembacaan bagian view
     */
    public function import($route, $params = array())
    {
        $is_plugin = false;
        if($plugin = $this->isPlugin($route)) {
            $route = $plugin;
            $is_plugin = true;
        }
        $info = $route == $this->config('route') ? $this->config('module_info') : Route::get()->getModuleInfo($route, $is_plugin);
        if($info['architecture'] == 'hmvc') {
            return Hmvc::get()->getViewContent($info, $params);
        } else {
            return Modular::get()->getViewContent($info, $params);
        }
    }

    /**
     * Membaca konten layout header
     * @since 1.0.0
     * @return konten layout header
     */
    public function getLayout($layout)
    {
        $path = $this->themeDir().$this->theme().'/'.$layout.'.php';
        if(!file_exists($path)) {
            throw new \Exception("Layout file not found in path $path", 404);
        }
        return $this->render($path);
    }

    /**
     * Memuat suatu widget dan menempelkannya pada suatu elemen HTML
     * @since 1.0.0
     * @param $element adalah ID elemen tujuan penempelan widget
     * @param $items adalah daftar widget yang akan ditempelkan dalam bentuk array
     */
    public function widget($element, $items)
    {
        Asset::get()->setWidget($element, $items);
    }

}