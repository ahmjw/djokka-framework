<?php

/**
 * Mengalamatkan atau mengalokasikan rute untuk modul berdasarkan alamat URL yang sedang diakses
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka;

use Djokka\Base;
use Djokka\Helpers\String;

/**
 * Kelas pustaka yang bertugas untuk mengalamatkan dan mengalokasikan rute untuk modul berdasarkan URL
 * yang sedang diakses dan mengatur segala hal yang berhubungan dengan alamat URL
 */
class Route extends Base
{
    /**
     * Menampung informasi modul yang sedang diakses
     * @since 1.0.0
     */
    private $modules = array();

    /**
     * Informasi URI (Uniform Resource Identifier) yang terkandung dalam URL
     */
    private $uri;

    /**
     * Nama direktori project web
     */
    private $path;

    /**
     * Alamat URL (Uniform Resource Locator) awal
     */
    private $base_url;

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
     * Konstruktor kelas Djokka\route
     * @since 1.0.0
     */
    public function __construct()
    {
        // Mengisi properti yang dibutuhkan
        $this->path = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
        $this->uri = $this->getUri();
        $this->base_url = $this->getBaseUrl();
    }

    /**
     * Mengambil informasi basis URL web
     * @since 1.0.0
     * @return string alamat basis URL web
     */
    public function getBaseUrl()
    {
        $host = $_SERVER['HTTP_HOST'];
        $protocol = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ? 'https' : 'http';
        $path = str_replace('/'.basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['PHP_SELF']);
        return "{$protocol}://{$host}{$path}";
    }

    /**
     * Mengambil alamat URL yang sekarang
     * @since 1.0.2
     * @return string lokasi root folder web
     */
    public function getUrl()
    {
        return $this->base_url.'/'.$this->uri;
    }

    /**
     * Mengambil informasi URI dari URL yang sedang diakses
     * @since 1.0.0
     * @return string URI
     */
    public function getUri()
    {
        $uri = explode('?', $_SERVER['REQUEST_URI'], 2);
        return substr($uri[0], strlen($this->path.'/'), strlen($uri[0]));
    }

    /**
     * Mengambil alamat URL berdasarkan lokasi folder
     * @since 1.0.0
     * @param $path adalah lokasi folder yang ingin dijadikan URK
     * @return string URL
     */
    public function urlPath($path)
    {
        $host = $_SERVER['HTTP_HOST'];
        $protocol = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ? 'https' : 'http';
        $path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $path);
        $url = str_replace('\\', '/', $path);
        return "{$protocol}://{$host}{$url}";
    }

    /**
     * Memuat informasi rute berdasarkan formatnya. Informasi rute ini akan diteruskan
     * ke bagian kontroller
     * @since 1.0.0
     */
    public function load()
    {
        switch ($this->config('route_format')) {
            case 'path':
                $router = $this->uri;
                break;
            case 'get':
                $router = $_GET[$this->config('get_router')];
                break;
            default:
                throw new \Exception("Route format is not supported", 500);
        }
        if(preg_match('/models|views|controllers/i', $router)) {
            throw new \Exception("Page is not accessible", 500);
        }
        $info = $this->getModuleInfo($router);
        //$this->url_params = $params;
        $this->uris = explode('/', $this->uri);
        $this->config('module_info', $info);
        $this->config('module', $info['module']);
        $this->config('action', $info['action']);
        $this->config('route', $info['route']);
    }

    /**
     * Mengambil informasi rute berdasarkan router/pembuat alur
     * @since 1.0.0
     * @param string $router Alur terformat yang akan dimasukkan
     * @param bool $is_plugin Menandakan modul tersebut adalah plugin atau bukan
     * @return informasi rute dalam bentuk array
     */
    public function getModuleInfo($router, $is_plugin = false)
    {
        $dir = !$is_plugin ? $this->moduleDir() : $this->pluginDir();
        $module = 'index';
        $action = 'index';
        $path = '';
        $class = null;
        $home_class = null;
        $partial_class = null;
        $route = null;
        $is_partial = false;
        $has_sub = false;
        $params = array();

        if(is_numeric(strrpos($router, '/'))) {
            if($router[0] != '/') {
                $has_sub = true;
                // Untuk rute get
                if($this->config('route_format') == 'get') {
                    $module = substr($router, 0, strrpos($router, '/'));
                    $length = strlen($router);
                    $action = substr($router, -($length - strrpos($router, '/')) + 1);
                    $params = $_GET;
                } else {
                    // Untuk rute path
                    $routes = explode('/', $router, $this->config('route_max_depth'));
                    // Trace route
                    $i = 0;
                    $trace_proc = null;
                    $prev_module = '';

                    foreach ($routes as $route) {
                        if(!$route) continue;
                        if($i == 0) $module = $route;
                        if($i > 0) {
                            $trace_proc .= 'modules/' . $route . '/';
                        } else {
                            $trace_proc .= $route . '/';
                        }
                        $path = $this->realPath($dir.$trace_proc);
                        if(!file_exists($path)) {
                            $action = $i > 0 ? $route : $routes[1];
                            $path = $this->realPath($dir.$prev_module);
                            break;
                        }
                        if($i > 0) {
                            $module .= '/'.$route;
                            $prev_module .= 'modules/' . $route . '/';
                        } else {
                            $prev_module .= $route . '/';
                        }
                        $i++;
                    }
                    $action = $action != null ? $action : 'index';
                    $params = array_slice($routes, $i + 1);
                }
            }
        } else {
            $module = !empty($router) ? $router: $this->config('main_module');
            $path = $dir . $module . '/';
        }

        $class = $has_sub && is_numeric(strrpos($module, '/')) ? 
            ucfirst(String::get()->lastPart('/', $module)) : ucfirst($module);
        $path = $this->realPath($path.DS.$class.'.php');
        $architecture = $router != $this->config('module_error') && file_exists($path) ? 'hmvc' : 'modular';

        if($architecture == 'hmvc') {
            return array(
                'architecture'=>$architecture,
                'module'=>$module,
                'action'=>$action,
                'route'=>$module.'/'.$action,
                'function'=>'action'.ucfirst($action),
                'class'=>'Djokka\\'.(!$is_plugin ? 'Controllers' : 'Plugins').'\\'.$class,
                'params'=>$params,
                'dir'=>$dir,
                'path'=>$path,
                'is_plugin'=>$is_plugin
            );
        } else {
            return array(
                'architecture'=>$architecture,
                'module'=>$module,
                'action'=>$action,
                'route'=>$module.'/'.$action,
                'dir'=>$dir,
                'path'=>$dir.$router.'.php',
                'is_plugin'=>$is_plugin
            );
        }
    }

    /**
     * Membentuk parameter yang akan ditambahkan ke dalam URL
     * @since 1.0.0
     * @param $format adalah rute format yang akan digunakan
     * @param $params adalah parameter yang akan diproses dalam bentuk array
     * @return objek instance kelas
     */
    public function urlParam($format, $params = array())
    {
        if(!empty($params)) {
            $attr = null;
            $i = 0;
            switch ($format) {
                case 'get':
                    foreach ($params as $key => $value) {
                        $value = urlencode($value);
                        if($i > 0) {
                            $attr .= '&';
                        }
                        $attr .= "{$key}={$value}";
                        $i++;
                    }
                    break;
                case 'path':
                    foreach ($params as $key => $value) {
                        $value = urlencode($value);
                        $attr .= "/$key/$value";
                    }
                    break;
            }
            return $attr;
        }
    }

}