<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \
 * @version 1.0.0
 */

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
 * Mempersingkat pembatas folder
 */
define('DS', DIRECTORY_SEPARATOR);
defined('HANDLE_ERROR') or define('HANDLE_ERROR', false);

/**
 * Nilai balik untuk pemanggilan file inti framework
 */
error_reporting(E_ALL ^ E_NOTICE);
return Djokka::get();

/**
 * Kelas Djokka adalah kelas inti framework. Dimuat pertama kali oleh
 * index.php pada root web. Kelas ini mengendalikan keseluruhan sistem.
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Djokka
{
    /**
     * @var Menampung instance dari kelas induk kontroller
     * @since 1.0.0
     * @access private
     */
    private static $core;

    private static $errors = array();

    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
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
            throw new Exception('Class library with name '.$subclass.' not found', 500);
        }
        return call_user_func(array($class_map[$subclass], 'get'));
    }

    public function lib($subclass)
    {
        $class_map = Config::get()->getClassMap();
        if(!isset($class_map[$subclass])) {
            throw new Exception('Class library with name '.$subclass.' not found', 500);
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
     * Menentukan konfigurasi awal sebelum web dijalankan
     * @since 1.0.0
     * @param $config adalah konfigurasi-konfigurasi dalam bentuk array
     */
    public function init($config = null)
    {
        $this->registerAutoload();
        if($config !== null) {
            if(is_array($config)) {
                Config::get()->merge($config);
            } else {
                Config::get()->merge(array(
                    'dir'=>$config,
                ));
                Config::get()->render();
            }
        } else {
            Config::get()->render();
        }
        return $this;
    }

    /**
     * Bootloader, menjalankan sistem web
     * @since 1.0.0
     */
    public function run($route = null)
    {
        if($route === null) {
            Route::get()->load();
            $route = $this->config('module').'/'.$this->config('action');
        }
        $content = Controller::get()->import($route);
        if(HANDLE_ERROR === true && !empty(self::$errors)) {
            header('Content-type: application/json');
            echo json_encode(array(
                'errors'=>self::$errors
            ));
        } else {
            echo View::get()->renderTheme($content);
        }
    }

    /**
     * Memuat secara otomatis suatu kelas pustaka, kontroller, model, dan komponen
     * @since 1.0.0
     * @param $class adalah nama kelas yang sedang dimuat
     */
    public function autoload($class)
    {
        $path = null;
        if(preg_match('/^'.__CLASS__.'/i', $class)) {
            $class = str_replace(__CLASS__.'\\', null, $class);
            $path = $this->realPath(__DIR__.DS.__CLASS__.DS.$class.'.php');
            if(!file_exists($path)) {
                throw new Exception("Class file not found in path $path", 404);
            }
            include_once($path);
        } else {
            if(preg_match('/^[a-zA-Z0-9_]+Model$/i', $class, $match)) {
                $path = $this->moduleDir().'models'.DS.$class.'.php';
                if(!file_exists($path)) {
                    throw new Exception("Model file not found at path $path", 404);
                }
            } else {
                $path = $this->componentDir().$class.'.php';
                if(!file_exists($path)) {
                    throw new Exception("Component file not found at path $path", 404);
                }
            }
            include_once($path);
        }
    }

    /**
     * Mengaktifkan semua otomatisasi pada sistem
     * @since 1.0.0
     */
    public function registerAutoload()
    {
        if(HANDLE_ERROR === true) {
            set_error_handler(array($this, 'errorHandler'), E_ALL ^ E_NOTICE);
        }
        spl_autoload_register(array($this, 'autoload'));
        set_exception_handler(array($this, 'exceptionHandler'));
    }

    /**
     * Menampilkan semua eksepsi menjadi informasi error
     * @since 1.0.0
     * @param $exception adalah objek eksepsi dari sistem
     * @access private
     */
    private function exceptionRender($exception)
    {
        ob_end_clean();
        $html = "<!DOCTYPE html><html lang=\"en\"><head>
            <title>Error {$exception->getCode()} &raquo; Djokka Framework</title></head>
            <style type=\"text/css\">body{font-family:Segoe UI, Arial, Times;background:#eee;}
            .container{background:#fff;border:1px solid #ccc;padding:20px;width:500px;margin:50px auto;
                border-radius:10px;-moz-border-radius:10px;-webkit-border-radius:10px;word-wrap:break-word;
                -o-border-radius:10px;-ms-border-radius:10px;}h1{margin-top:0px;text-align:center;}
                footer{width:500px;margin:auto;font-size:11px;text-align:right;}</style>
            <body><div class=\"container\"><h1>Error {$exception->getCode()}</h1>";
        $html .= "<header><p><b>{$exception->getMessage()}</b></p></header>";
        $html .= "<section><p>Thrown at file {$exception->getFile()}:{$exception->getLine()}</p>";
        $html .= '<ol>';
        foreach($exception->getTrace() as $i => $trace) {
            if(isset($trace['file'])) {
                $html .= '<li>'.$trace['file'].':'.$trace['line'].'<br/>';
                if(isset($trace['class'])) {
                    $html .= $trace['class'];
                }
                if(isset($trace['type'])) {
                    $html .= $trace['type'];
                }
                $html .= $trace['function'].'()</li>';
            } else {
                $html .= '<li>'.$trace['class'].$trace['type'].$trace['function'].'()</li>';
            }
        }
        $html .= '</ol></div></section><footer>Djokka Framework</footer></body></html>';
        echo $html;
    }

    public function exception($code, $message)
    {
        throw new \Exception($message, $code);
    }

    /**
     * Menampilkan hasil error, termasuk ke header dokumen web
     * @since 1.0.0
     * @param $exception adalah objek eksepsi dari sistem
     * @access private
     */
    private function exceptionOutput($exception)
    {
        if($this->config('error_redirect') === true) {
            try{
                ob_end_clean();
                header($_SERVER['SERVER_PROTOCOL'].' '.$exception->getCode().' '.$exception->getMessage());
                $this->config('architecture', 'modular');
                $this->config('error_render', true);
                $content = Controller::get()->import($this->config('module_error'), array(
                    'error'=>$exception
                ));
                $this->config('architecture', null);
                echo View::get()->renderTheme($content);
            } catch(Exception $exception) {
                header($_SERVER['SERVER_PROTOCOL'].' '.$exception->getCode().' '.$exception->getMessage());
                $this->exceptionRender($exception);
            }
        } else {
            header($_SERVER['SERVER_PROTOCOL'].' '.$exception->getCode().' '.$exception->getMessage());
            $this->exceptionRender($exception);
        }
    }

    /**
     * Menangani semua error yang dilemparkan
     * @since 1.0.0
     * @param $exception adalah objek eksepsi dari sistem
     * @access public
     */
    public function exceptionHandler($exception)
    {
        // Jika dalam mode JSON
        if($this->config('json') === true || HANDLE_ERROR === true) {
            header('Content-type: application/json');
            $traces = array();
            foreach ($exception->getTrace() as $i => $trace) {
                unset($trace['args']);
                $traces[] = $trace;
            }
            echo json_encode(array(
                'exception'=>array(
                    'code'=>$exception->getCode(),
                    'message'=>$exception->getMessage(),
                    'file'=>$exception->getFile(),
                    'line'=>$exception->getLine(),
                    'traces'=>$traces,
                )
            ));
            return;
        }
        // Jika terjadi error 403 dan pengalihan aktif
        if($this->config('error_redirect') === true && $exception->getCode() == 403) {
            $page = $this->config('module').'/'.$this->config('action');
            if($page != $redirect = $this->config('module_forbidden')) {
                Controller::get()->redirect('/' . $redirect);
            } else {
                $this->exceptionOutput($exception);
            }
        } else {
            $this->exceptionOutput($exception);
        }
    }

    public function errorHandler($level, $message, $file, $line, $context)
    {
        self::$errors[] = array(
            'level'=>$level,
            'message'=>$message,
            'file'=>$file,
            'line'=>$line,
            'context'=>$context
        );
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
            throw new Exception("Failed to importing object file. File not found in path $path", 404);
        }
        include_once($path);
        if(preg_match('/[a-zA-Z0-9_]+\/([a-zA-Z0-9_]+$)/i', $class, $match)) {
            $class = $match[1];
        }
        if(!class_exists($class)) {
            throw new Exception("Class $class is not defiend in file $path", 500);
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