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
     * Nilai indeks view pada pemanggilan HMVC
     */
    private $index = -1;

    /**
     * Daftar view yang telah terpanggil
     */
    private $views = array();

    /**
     * Menandai pengolah view telah aktif atau belum
     */
    private $active = false;

    /**
     * Menandai sistem mengaktifkan pengolah tema atau tidak
     */
    private $use_theme = true;

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
    public static function getInstance($class = __CLASS__)
    {
        if(self::$instance == null) {
            self::$instance = new $class;
        }
        return self::$instance;
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
        // Melakukan pembacaan view
        if($this->active && !empty($this->views)) {
            $route = $this->views[$this->index]['view'];
            $params = $this->views[$this->index]['params'];
            $theme_path = $this->themeDir().$this->config('theme').'/';
            $path = $this->realPath("{$theme_path}views/$info[module]/{$route}.php");
            if(!$info['is_partial']) {
                if(!file_exists($path)) {
                    $path = $this->realPath("$info[dir]$info[module]/views/{$route}.php");
                    if(!file_exists($path)) {
                        throw new \Exception("View file not found in path $path", 404);
                    }
                }
            } else {
                $path = $this->realPath("$info[dir]/$info[home_class]/views/$info[partial_class]/{$route}.php");
                if(!file_exists($path)) {
                    throw new \Exception("View file not found in path $path", 404);
                }
            }
            // Menentukan kelas induk kontroller
            $this->active = false;
            if($this->index == 0 && !$info['is_plugin']) {
                $this->content = utf8_decode($instance->render($path, $params));
                parent::setCore($instance);
            } else {
                $instance->render($path, $params);
                return $instance->render($path, $params);
            }
        } else {
            $this->use_theme = false;
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
        if($this->use_theme) {
            $theme_path = $this->realPath($this->themeDir().$this->config('theme').DS.$this->config('layout').'.php');
            if(!file_exists($theme_path)) {
                throw new \Exception("Layout file not found in path {$theme_path}", 404);
            }
            if(parent::getCore() != null) {
                $theme_content = parent::getCore()->render($theme_path);
            } else {
                $theme_content = Controller::get()->render($theme_path);
            }
            $content = Asset::get()->render($theme_content);
        }
        //$content = preg_replace('/[\r\n\t]/i', '', $content);
        //$content = preg_replace('/\s{1,}/i', ' ', $content);
        return $content;
    }

    /**
     * Memanggil suatu view
     * @since 1.0.0
     * @param string $name Lokasi view
     * @param array $params Parameter tambahan untuk dimasukkan ke view
     */
    public function mergeView($name, $params = array())
    {
        $view = array('view'=>$name, 'params'=>$params);
        $this->views = array_merge($this->views, array($view));
        $this->index++;
        $this->active = true;
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