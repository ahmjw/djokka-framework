<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.1
 */

namespace Djokka\Controller;

use Djokka\Base;
use Djokka\Route;

/**
 * Kelas Djokka\Controller adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @since 1.0.0
 */
class Linker extends Base
{
    /**
     * Menampung instance dari kelas
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

    /**
     * Mengambil pembatas URL berdasarkan format rute
     * @return string
     */
    public function getSeparator()
    {
        return $this->config('route_format') == 'get' ? '?r=' : '/';
    }

    /**
     * Membentuk URL dengan melakukan penambatan metode GET
     * @param mixed $module string Rute module
     * @return string
     */
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

    /**
     * Membentuk URL dengan melakukan penambatan metode GET dengan tambahan parameter
     * @param mixed $module string Rute module
     * @param mixed $module array Parameter tambahan untuk URL
     * @return string
     */
    public function appendLink($module, $params)
    {
        return $this->getLink($module).'?'.Route::get()->urlParam('get', $params);
    }

    /**
     * Mengolah parameter untuk URL
     * @param mixed $params array Parameter tambahan untuk URL
     * @return string
     */
    public function renderParameter($params)
    {
        return Route::get()->urlParam($this->config('route_format'), $params);
    }

    /**
     * Mengambil URL suatu modul
     * @param mixed $module string Rute modul
     * @return string
     */
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

    /**
     * Mengambil URL suatu modul dengan tambahan parameter
     * @param mixed $module string Rute modul
     * @param mixed $parameter array Parameter tambahan untuk URL
     * @return string
     */
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

    /**
     * Mengelola parameter tambahan URL yang berupa string
     * @param mixed $parameter string Parameter tambahan untuk URL dengan tipe string
     * @return string
     */
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