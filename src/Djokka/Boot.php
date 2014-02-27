<?php

/**
 * Melakukan boot/pemuatan sistem
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @since 1.0.3
 * @version 1.0.3
 */

namespace Djokka;

use Djokka\Helpers\Config;

define('DJOKKA', true);
define('DS', DIRECTORY_SEPARATOR);
defined('HANDLE_ERROR') or define('HANDLE_ERROR', false);
define('SYSTEM_DIR', __DIR__ . DS . '..' . DS . '..' . DS);

include_once 'Base.php';

/**
 * Kelas pustaka yang digunakan untuk melakukan booting
 */
class Boot extends Base
{
    /**
     * Instance dari kelas ini
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Mengambil instance kelas ini secara Singleton Pattern
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
     * Mengaktifkan semua otomatisasi pada sistem
     * @since 1.0.0
     */
    public function registerAutoload()
    {
        // Error and exception handling
        if(HANDLE_ERROR === true) {
            register_shutdown_function(array(__CLASS__, 'onShutdown'));
            set_error_handler(array(__CLASS__, 'handleError'), E_ALL ^ E_NOTICE);
            set_exception_handler(array(__CLASS__, 'handleException'));
            ini_set('display_errors', 'off');
            error_reporting(E_ALL ^ E_NOTICE);
        }
        // Internal class autoloader
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Mengambil error terakhir ketika proses berakhir
     * @since 1.0.3
     */
    public static function onShutdown()
    {
        if (($error = error_get_last()) !== null) {
            self::handleError($error["type"], $error["message"], $error["file"], $error["line"]);
        }
        //var_dump($error);
    }

    /**
     * Menangani error dan melemparnya sebagai Exception
     * @param mixed $num Kode error
     * @param mixed $str Pesan error
     * @param mixed $file Lokasi berkas yang ditemukan error
     * @param mixed $line Baris kode pada berkas yang ditemukan error
     * @param optional $context Argumen atau parameter
     * @throws \ErrorException untuk menjadikan error sebagai Exception
     * @since 1.0.3
     */
    public static function handleError($num, $str, $file, $line, $context = null)
    {
        self::handleException(new \ErrorException( $str, 0, $num, $file, $line));
    }

    /**
     * Menampilkan Exception atau error sebagai HTML
     * @param mixed $e object Instance object Exception
     * @since 1.0.3
     */
    public static function handleException(\Exception $e)
    {
        if(Config::getInstance()->config('error_redirect') === true && $e->getCode() == 403) {
            $page = Config::getInstance()->config('module').'/'.Config::getInstance()->config('action');
            if($page != $redirect = Config::getInstance()->config('module_forbidden')) {
                Controller::getInstance()->redirect('/' . $redirect);
            } else {
                $this->exceptionOutput($exception);
            }
        }
        ob_end_clean();
        $path = SYSTEM_DIR . 'resources' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'view.php';
        include_once($path);
        exit();
    }

    /**
     * Memuat secara otomatis suatu kelas pustaka, kontroller, model, dan komponen
     * @since 1.0.0
     * @param $class adalah nama kelas yang sedang dimuat
     */
    public function autoload($class)
    {
        $path = null;
        if(preg_match('/^'.__NAMESPACE__.'(.*)$/i', $class, $match)) {
            $path = $this->realPath(__DIR__.$this->realPath($match[1]).'.php');
            if(!file_exists($path)) {
                throw new \Exception("Class file not found in path $path", 404);
            }
            include_once($path);
        } else {
            if(preg_match('/^[a-zA-Z0-9_]+Model$/i', $class, $match)) {
                $path = $this->moduleDir().'models'.DIRECTORY_SEPARATOR.$class.'.php';
                if(!file_exists($path)) {
                    throw new \Exception("Model file not found at path $path", 404);
                }
            } else {
                $path = $this->componentDir().$class.'.php';
                if(!file_exists($path)) {
                    throw new \Exception("Component file not found at path $path", 404);
                }
            }
            include_once($path);
        }
    }

    /**
     * Bootloader, menjalankan sistem web
     * @param string $route Rute modul yang langsung ingin dieksekusi
     * @since 1.0.0
     */
    public function run($route = null)
    {
        if($route === null) {
            Route::getInstance()->load();
            $route = $this->config('module').'/'.$this->config('action');
        }
        $content = Controller::getInstance()->import($route);
        
        if(HANDLE_ERROR === true && !empty(self::$errors)) {
        } else {
            View::getInstance()->renderTheme($content);
        }
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
                Config::getInstance()->merge($config);
            } else {
                Config::getInstance()->merge(array(
                    'dir'=>$config,
                ));
                Config::getInstance()->render();
            }
        } else {
            Config::getInstance()->render();
        }
        return $this;
    }

    /**
     * Mengubah suatu string menjadi format path yang benar
     * @param string $path Lokasi direktori/berkas yang akan dibenarkan
     * @since 1.0.0
     * @return string
     */
    public function realPath($path) {
        return preg_replace("/([\/\\\]+)/i", DS, $path);
    }

    /**
     * Membaca, menambah atau mengubah nilai konfigurasi
     */
    public function config() {
        switch (func_num_args()) {
            case 0:
                return Config::getInstance()->getConfig();
            case 1:
                if(!is_array(func_get_arg(0))) {
                    return Config::getInstance()->getData(func_get_arg(0));
                } else {
                    return Config::getInstance()->merge(func_get_arg(0));
                }
            case 2:
                Config::getInstance()->setData(func_get_arg(0), func_get_arg(1));
                break;
        }
    }
}