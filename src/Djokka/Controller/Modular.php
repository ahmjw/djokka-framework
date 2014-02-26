<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.1
 */

namespace Djokka\Controller;

use Djokka\Route;
use Djokka\View;
use Djokka\Model\Pager;
use Djokka\View\Asset;
use Djokka\Controller as Core;
use Djokka\Helpers\String;
use Djokka\Helpers\User;

/**
 * Kelas Djokka\Controller adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @since 1.0.0
 */
class Modular extends Core
{
    private $router;
    private $dir;
    private $params;

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
     * Mengambil konten view pada modul yang menggunakan arsitektur modular
     * @param mixed $info array Informasi modul
     * @param optional $param array Parameter yang akan dikirimkan untuk fungsi aksi
     * @return string
     */
    public function getViewContent($info, $params) {
        // Memuat view yang terletak pada folder tema
        $theme_view = $this->realPath($this->themeDir().$this->theme().DS.'views'.DS.$info['route']);
        if(file_exists($theme_view)) {
            $theme_view = $theme_view.DS.'index.php';
        } else {
            $theme_view = $theme_view.'.php';
        }
        $theme_view = $this->realPath($theme_view);

        // Menentukan letak modul
        if(!file_exists($theme_view)) {
            $module_path = $info['path'];
            if(!file_exists($module_path)) {
                $module_info = pathinfo($module_path);
                $module_path = $module_info['dirname'].DS.$module_info['filename'].DS.'index.php';
            }
        } else {
            $module_path = $theme_view;
            View::get()->setUseTheme(false);
        }
        $module_path = $this->realPath($module_path);
        if(!file_exists($module_path)) {
            throw new \Exception("Module file not found in path {$module_path}", 404);
        }
        if($info['is_plugin']) {
            $plugin_info = pathinfo($module_path);
            Plugin::get()->url = Route::get()->urlPath($plugin_info['dirname'].DS);
            parent::setCore(Plugin::get());
        } else {
            if(($class = $this->config('modular_parent')) == null || $this->config('error_render') === true) {
                parent::setCore(parent::get());
            } else {
                $instance = new $class;
                parent::setCore($instance);
            }
        }
        $content = parent::getCore()->render($module_path, $params);
        if($this->config('json') === false) {
            View::getInstance()->setContent(utf8_decode($content));
        }
        return $content;
    }

}