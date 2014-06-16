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

use Djokka\Helpers\Config;
use Djokka\Helpers\String;
use Djokka\Helpers\File;

/**
 * Kelas pustaka yang bertugas untuk mengalamatkan dan mengalokasikan rute untuk modul berdasarkan URL
 * yang sedang diakses dan mengatur segala hal yang berhubungan dengan alamat URL
 */
class Route extends Shortcut
{
    /**
     * Informasi URI (Uniform Resource Identifier) yang terkandung dalam URL
     */
    private $_uri_segments = array();

    /**
     * Informasi URI (Uniform Resource Identifier) yang terkandung dalam URL
     */
    private $_uri;

    /**
     * Nama direktori project web
     */
    private $_path;

    /**
     * Alamat URL (Uniform Resource Locator) awal
     */
    private $_base_url;

    private $_alias;

    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new static();
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
        $this->_path = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
        $this->_uri = $this->getUri();
        $this->_base_url = $this->getBaseUrl();
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
        return "{$protocol}://{$host}{$path}/";
    }

    /**
     * Mengambil alamat URL yang sekarang
     * @since 1.0.2
     * @return string lokasi root folder web
     */
    public function getUrl()
    {
        return $this->_base_url.$this->_uri;
    }

    /**
     * Mengambil informasi URI dari URL yang sedang diakses
     * @since 1.0.0
     * @return string URI
     */
    public function getUri()
    {
        $uri = explode('?', $_SERVER['REQUEST_URI'], 2);
        return substr($uri[0], strlen($this->_path.'/'), strlen($uri[0]));
    }

    /**
     * Mengambil informasi URI dari URL yang sedang diakses
     * @since 1.0.0
     * @return string URI
     */
    public function getUriSegments()
    {
        return $this->_uri_segments;
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
        if ($_SERVER['PHP_SELF'] != "-") {
            return "{$protocol}://{$host}{$url}";
        } else {
            return "file:///" . str_replace(DS, '/', $this->config('dir')) . "{$url}";
        }
    }

    private function aliasing()
    {
        $routes = $this->config('routes');
        $router = $this->_uri;
        $params = array();

        if (is_array($routes) && !empty($routes)) {
            foreach ($routes as $route) {
                $keys = array();
                $pattern = preg_replace_callback('/\(([a-zA-Z_](?:[a-zA-Z0-9_]+)?):(.*?)\)/i', function($matches) use(&$keys) {
                    $keys[] = $matches[1];
                    $group = $matches[2] !== null ? $matches[2] : '.+';
                    return '('.$group.')';
                }, $route[0]);
                $pattern = '/'.str_replace('/', '\/', $pattern).'/i';
                if (preg_match($pattern, $this->_uri, $matches)) {
                    $this->_alias = preg_replace_callback('/:([a-zA-Z_][a-zA-Z0-9_]*)/i', function($match) use($matches, $keys) {
                        $i = array_search($match[1], $keys) + 1;
                        return $matches[$i];
                    }, $route[1]);
                    if (isset($route[2])) {
                        $params = explode('/', preg_replace_callback('/:([a-zA-Z_][a-zA-Z0-9_]*)/i', function($match) use($matches, $keys) {
                            $i = array_search($match[1], $keys) + 1;
                            return $matches[$i];
                        }, $route[2]));
                    }
                    $router = $this->_alias;
                }
            }
        }
        return array(
            'router' => $router,
            'params' => $params
        );
    }

    /**
     * Memuat informasi rute berdasarkan formatnya. Informasi rute ini akan diteruskan
     * ke bagian kontroller
     * @since 1.0.0
     */
    public function load()
    {
        $this->_uri_segments = explode('/', $this->_uri);
        $this->config('base_url', $this->_base_url);
        $this->config('plugin_url', $this->urlPath($this->pluginDir()));

        $data = $this->aliasing();
        $hmvc = new Hmvc($data['router'], false, $data['params']);
        $this->config('module_info', $hmvc);
        $this->config('module', $hmvc->module);
        $this->config('action', $hmvc->action);
        $this->config('route', $hmvc->route);
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
        if (!empty($params)) {
            $attr = null;
            $i = 0;
            switch ($format) {
                case 'get':
                    foreach ($params as $key => $value) {
                        $value = urlencode($value);
                        if ($i > 0) {
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

    /**
     * Mengambil URL suatu modul
     * @param mixed $route string Rute modul
     * @return string
     */
    private function getLinkFromRoute($route)
    {
        // Menentukan lokasi URL
        $url = null;
        if(!is_numeric(strpos($route, '/'))) {
            $url = $this->_base_url . $this->config('module') . '/' . $route;
        }else {
            if($route[0] != '/') {
                $url = $this->_base_url . $this->config('module') . '/' . $route;
            } else {
                $url = $this->_base_url . substr($route, 1, strlen($route));
            }
        }
        return $url;
    }

    public function buildUrl($params)
    {
        switch (count($params)) {
            case 0:
                return $this->_base_url;
            case 1:
                return $this->getLinkFromRoute($params[0]);
        }
    }
}