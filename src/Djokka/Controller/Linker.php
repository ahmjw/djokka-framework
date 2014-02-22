<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.1
 */

namespace Djokka\Controller;

use Djokka\Route;

/**
 * Kelas Djokka\Controller adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Linker extends \Djokka
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

    public function getSeparator()
    {
        return $this->config('route_format') == 'get' ? '?r=' : '/';
    }

    public function appendGet($module)
    {
        $params = Route::get()->urlParam('get', $module);
        $params = substr($params, 1, strlen($params));
        if(!isset($module[0])) {
            return Route::get()->getBaseUrl().'/'.Route::get()->getUri().'?'.$params;
        } else {
            if(!is_numeric(strpos($module[0], '/'))) {
                return Route::get()->getBaseUrl().'/'.
                    $this->config('module').'/'.$module[0].'?'.$params;
            } else {
                return Route::get()->getBaseUrl().$module[0].'?'.$params;
            }
        }
    }

    public function appendLink($module, $params)
    {
        return $this->getLink($module).'?'.Route::get()->urlParam('get', $params);
    }

    public function renderParameter($params)
    {
        return Route::get()->urlParam($this->config('route_format'), $params);
    }

    public function getLink($module)
    {
        // Menentukan pemisah
        $sprtr = $this->getSeparator();
        // Menentukan lokasi URL
        $url = null;
        $base = Route::get()->getBaseUrl();
        if(!is_numeric(strpos($module, '/'))) {
            $url = $base.$sprtr.$this->config('module').$sprtr.$module;
        } else if($module[0] == '-') {
            $url = $base.$sprtr.$this->config('module').'-'.substr($module, 1, strlen($module));
        }else {
            if($module[0] != '/') {
                $url = $base.$sprtr.$this->config('module').$sprtr.$module;
            } else {
                $url = $base.$sprtr.substr($module, 1, strlen($module));
            }
        }
        return $url;
    }

    public function getLinkParameter($module, $parameter)
    {
        // Menentukan pemisah
        $sprtr = $this->getSeparator();
        // Menentukan lokasi URL
        if(is_string($parameter)) {
            $params = $this->renderParameterString($parameter);
        } else {
            $params = $this->renderParameter($parameter);
        }
        if(!is_numeric(strpos($module, '/'))) {
            return Route::get()->getBaseUrl().$sprtr.$this->config('module').'/'.$module.$params;
        } else {
            if($module[0] != '/') {
                return Route::get()->getBaseUrl().$sprtr.$this->config('module').'/'.$module.$params;
            } else {
                return Route::get()->getBaseUrl().$sprtr.substr($module, 1, strlen($module)).$params;
            }
        }
    }

    public function renderParameterString($parameter)
    {
        if(preg_match_all('/(.+?)=(.*?)(?:&|$)/i', $parameter, $matches)){
            $params = array();
            for ($i=0; $i < count($matches); $i++) { 
                $params[$matches[1][$i]] = $matches[2][$i];
            }
            return $this->renderParameter($params);
        } else {
            return $parameter;
        }
    }
}