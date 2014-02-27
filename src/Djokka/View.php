<?php

/**
 * Memproses bagian view
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.1
 */

namespace Djokka;

use Djokka\Base;
use Djokka\View\Asset;

/**
 * Kelas pustaka yang bertugas untuk memproses dan mengendalikan bagian view yang terdapat di dalam suatu modul
 */
class View extends Base
{
    /**
     * Konten web
     */
    private $content;

    /**
     * Menampung instance dari kelas
     * @since 1.0.1
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.1
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
     * Mengambil lokasi direktori view
     * @param mixed $viewName string Nama view
     * @return string
     */
    private function getPath($viewName)
    {
        return BASE_DIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->_module .
            DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewName . '.php';
    }

    /**
     * Mengambil konten web
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Menentukan konten web
     * @param mixed $content string Konten web
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * Mengambil daftar view yang telah terpanggil
     * @return array
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Mengambil indeks terakhir view yang terpanggil
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Mengecek apakah bagian pengolah diaktifkan atau tidak
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Menetapkan status apakah pengolah tema digunakan atau tidak
     * @param mixed $condition boolean
     */
    public function setUseTheme($condition) {
        $this->use_theme = $condition;
    }

    /**
     * Memproses konten web berdasarkan informasi modul
     * @param mixed $info array Informasi terkait modul yang akan diproses
     * @param mixed $instance object Instance dari modul yang akan diproses
     * @return string
     */
    public function renderContent($info, $instance) {
        $view = $instance->getView();
        $theme = $this->themeDir() . $this->config('theme') . '/';
        $path = $this->realPath($theme . 'views/' . $info['module'] . '/'. $view['name'] . '.php');

        if(!file_exists($path)) {
            $path = $this->realPath($info['module_dir'] . '/views/' . $view['name'] . '.php');
            if(!file_exists($path)) {
                throw new \Exception("View file not found in path $path", 404);
            }
        }

        if(!$info['is_plugin']) {
            $this->content = utf8_decode($instance->render($path, $view['vars']));
            parent::setCore($instance);
        } else {
            $instance->render($path, $params);
            return $instance->render($path, $params);
        }
    }

    /**
     * Memproses konten tema
     * @param mixed $content string Konten web yang akan diproses ke tema
     * @return string
     */
    public function renderTheme($content) 
    {
        // Jika dalam mode JSON
        if($this->config('json') === true || HANDLE_ERROR === true) {
            /*header('Content-type: application/json');
            echo json_encode($content);
            return;*/
        }

        $theme_path = $this->realPath($this->themeDir().$this->config('theme').DS.$this->config('layout').'.php');
        if(!file_exists($theme_path)) {
            throw new \Exception("Layout file not found in path {$theme_path}", 404);
        }
        if(Controller::getCore() != null) {
            $theme_content = Controller::getCore()->render($theme_path);
        } else {
            $theme_content = Controller::get()->render($theme_path);
        }
        $content = Asset::getInstance()->render($theme_content);
        //$content = preg_replace('/[\r\n\t]/i', '', $content);
        //$content = preg_replace('/\s{1,}/i', ' ', $content);
        print $content;
    }

    /**
     * Mengambil konten view
     * @param object $instance Instance modul yang akan memproses
     * @param string $view Nama view yang akan diproses
     * @param array $params Parameter yang akan diekstrak ke bagian view
     * @return string
     */
    public function getView($instance, $view, $params = array()) {
        $path = $this->themeDir().$this->config('theme').DS.'views'.DS.$this->config('module').DS.$view.'.php';
        if(!file_exists($path)) {
            $path = $this->moduleDir().$this->config('module').DS.'views'.DS.$view.'.php';
            if(!file_exists($path)) {
                throw new \Exception("View file not found in path $path", 404);
            }
        }
        return $instance->render($path, $params);
    }

}