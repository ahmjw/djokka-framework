<?php

/**
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
 * Kelas yang digunakan untuk melakukan boot/pemuatan sistem
 * @since 1.0.0
 */
class Boot extends Base
{
    /**
     * Instance dari kelas ini
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Mengambil instance kelas ini secara Singleton Pattern
     * @since 1.0.0
     * @param $class adalah nama kelas (opsional)
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new static();
        }
        return self::$instance;
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
        //if (ob_get_level() > 0) {
            ob_end_clean();
        //}
        $path = SYSTEM_DIR . 'resources' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'view.php';
        echo View::getInstance()->outputBuffering($path, array(
            'e' => $e
        ));
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
            /*header('Content-type: application/json');
            echo json_encode(array(
                'errors'=>self::$errors
            ));*/
        } else {
            echo View::getInstance()->renderTheme($content);
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
     * Mengubah suatu string menjadi format path yang benar
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

    /**
     * Menampilkan semua eksepsi menjadi informasi error
     * @since 1.0.0
     * @param $exception adalah objek eksepsi dari sistem
     * @access private
     * @deprecated
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

    /**
     * Mengubah suatu string menjadi format path yang benar
     * @since 1.0.0
     * @deprecated
     * @return string
     */
    public function exception($code, $message)
    {
        throw new \Exception($message, $code);
    }

    /**
     * Menampilkan hasil error, termasuk ke header dokumen web
     * @since 1.0.0
     * @param $exception adalah objek eksepsi dari sistem
     * @deprecated
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
     * @deprecated
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

    /**
     * @deprecated
     */
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
}