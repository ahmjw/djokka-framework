<?php

/**
 * Mengontrol proses pada modul
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.1
 */

namespace Djokka;

use Djokka\Base;
use Djokka\Route;
use Djokka\View\Asset;
use Djokka\Helpers\String;
use Djokka\Helpers\User;
use Djokka\Controller\Hmvc;
use Djokka\Controller\Modular;
use Djokka\Controller\Plugin;

/**
 * Kelas pustaka yang bertugas mengontrol atau mengendalikan proses di dalam modul
 */
class Controller extends Base
{
    /**
     * Nama view
     * @since 1.0.3
     */
    private $_view;

    /**
     * Menampung instance dari kelas
     * @since 1.0.1
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.1
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
     * Memanggil bagian view yang akan dijadikan konten
     * @param string $name Nama view yang akan dipanggil
     * @param array $vars Data yang akan diekstrak ke view tersebut
     */
    public function view($name, array $vars = array())
    {
        $this->_view = array(
            'name' => $name,
            'vars' => $vars
        );
    }

    /**
     * Mengecek apakah modul menggunakan view atau tidak
     * @return boolean
     */
    public function isUseView()
    {
        return !empty($this->_view);
    }

    /**
     * Memanggil data view
     * @deprecated
     */
    public function getView()
    {
        return $this->_view;
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
     * Run output buffering to render the view
     * @param mixed $viewName string Name of the view
     * @param mixed $vars Array data to extract to the view
     * @return string Output buffering result from the view file
     */
    public function outputBuffering($viewName, array $vars = null)
    {
        ob_start();
        if (!empty($vars)) {
            extract($vars);
        }
        include $viewName;
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
        return View::getInstance()->getContent();
    }

    /**
     * Mengambil potongan URL
     * @since 1.0.0
     * @param $i adalah indeks potongan URL yang berupa angka
     * @return string potongan URL
     */
    public function uri($i = null)
    {
        $uris = Route::getInstance()->uris;
        if($i === null) {
            return $uris;
        } else {
            if (isset($uris[$i])) {
                return $uris[$i];
            }
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
        $params = Route::getInstance()->url_params;
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

    /**
     * Memasukkan kode Javascript ke badan HTML
     * @param mixed $code string Kode Javascript yang akan dimasukkan
     * @since 1.0.0
     */
    public function js($code) {
        Asset::getInstance()->js($code);
    }

    /**
     * Memasukkan kode CSS ke badan HTML
     * @param mixed $code string Kode CSS yang akan dimasukkan
     * @since 1.0.0
     */
    public function css($code) {
        Asset::getInstance()->css($code);
    }

    /**
     * Memasukkan link untuk berkas Javascript atau CSS
     * @param mixed $url string Lokasi berkas yang akan dimasukkan
     * @since 1.0.0
     */
    public function asset($url) {
        Asset::getInstance()->add($url);
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
        return Route::getInstance()->base_url.'/'.$url;
    }

    /**
     * Memanggil plugin
     * @since 1.0.1
     * @param string $name Nama plugin yang akan dipanggil
     * @return string Konten plugin
     */
    public function plugin($name) 
    {
        return $this->import('plugin.'.$name);
    }

    /**
     * Mengecek status suatu modul, apakah termasuk plugin atau modul biasa
     * @param string $route Rute modul yang akan dicek
     * @return boolean
     */
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
     * @param string $route adalah alamat aksi/modul yang akan dieksekusi
     * @param array $params adalah parameter tambahan yang dimasukkan ke dalam aksi/modul
     * @return string berupa hasil pembacaan bagian view
     */
    public function import($route, $params = array(), $is_widget = false)
    {
        $is_plugin = false;
        if($plugin = $this->isPlugin($route)) {
            $route = $plugin;
            $is_plugin = true;
        }
        $info = $route == $this->config('route') ? $this->config('module_info') : Route::getInstance()->getModuleInfo($route, $is_plugin, $is_widget);
        return Hmvc::getInstance()->getViewContent($info, $params);
    }

    /**
     * Membaca konten layout
     * @param string $layout Nama layout yang akan dibaca
     * @since 1.0.0
     * @return string konten layout
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
        Asset::getInstance()->setWidget($element, $items);
    }

}