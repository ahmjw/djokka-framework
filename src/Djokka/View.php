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

use Djokka\View\Asset;
use Djokka\Helpers\Config;
use Djokka\Helpers\File;

/**
 * Kelas pustaka yang bertugas untuk memproses dan mengendalikan bagian view yang terdapat di dalam suatu modul
 */
class View
{
    /**
     * Konten web
     */
    private $_content;

    /**
     * Menandai view telah diaktifkan atau belum
     */
    private $_activated = false;

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

    public function isActivated()
    {
        return $this->_activated;
    }

    /**
     * Mengambil konten web
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Memproses konten web berdasarkan informasi modul
     * @param mixed $info array Informasi terkait modul yang akan diproses
     * @param mixed $instance object Instance dari modul yang akan diproses
     * @return string
     */
    public function renderContent($info, $instance) {
        $view = $instance->getView();
        $theme = File::getInstance()->themeDir() . Config::getInstance()->getData('theme') . '/';
        $path = File::getInstance()->realPath($theme . 'views/' . $info['module'] . '/'. $view['name'] . '.php');

        if(!file_exists($path)) {
            $path = File::getInstance()->realPath($info['module_dir'] . '/views/' . $view['name'] . '.php');
            if(!file_exists($path)) {
                throw new \Exception("View of module '$info[route]' is not found: $path", 404);
            }
        }

        if(!$this->_activated && !$info['is_plugin']) {
            $this->_activated = true;
            $this->_content = utf8_decode($instance->outputBuffering($path, $view['vars']));
            Controller::setCore($instance);
        } else {
            return $instance->outputBuffering($path, $view['vars']);
        }
    }

    /**
     * Memproses konten tema
     * @param mixed $content string Konten web yang akan diproses ke tema
     * @return string
     */
    public function renderOutput($route)
    {
        $content = Controller::getInstance()->import($route);
        // Jika dalam mode JSON
        if(Config::getInstance()->getData('json') === true || HANDLE_ERROR === true) {
            /*header('Content-type: application/json');
            echo json_encode($content);
            return;*/
        }

        $theme_path = File::getInstance()->realPath(File::getInstance()->themeDir().Config::getInstance()->getData('theme').DS.Config::getInstance()->getData('layout').'.php');
        if(!file_exists($theme_path)) {
            throw new \Exception("Layout file not found in path {$theme_path}", 404);
        }
        if(Controller::getCore() != null) {
            $theme_content = Controller::getCore()->outputBuffering($theme_path);
        } else {
            $theme_content = Controller::getInstance()->outputBuffering($theme_path);
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
        $path = File::getInstance()->themeDir().Config::getInstance()->getData('theme').DS.'views'.DS.Config::getInstance()->getData('module').DS.$view.'.php';
        if(!file_exists($path)) {
            $path = $this->moduleDir().Config::getInstance()->getData('module').DS.'views'.DS.$view.'.php';
            if(!file_exists($path)) {
                throw new \Exception("View file not found in path $path", 404);
            }
        }
        return $instance->outputBuffering($path, $params);
    }

}