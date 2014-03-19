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
     * Web content
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

    public function __destruct()
    {
        if (BaseController::getCore() !== null) {
            $this->showOutput();
        }
    }

    public function getLayoutPath()
    {
        $dir = File::getInstance()->themeDir();
        $theme = Config::getInstance()->getData('theme');
        $layout = Config::getInstance()->getData('layout');
        return File::getInstance()->realPath($dir . $theme . DS . $layout . '.php');
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

    public function getThemeViewPath($moduleName, $viewName)
    {
        $rootDir = File::getInstance()->themeDir();
        $theme = Config::getInstance()->getData('theme');
        return File::getInstance()->realPath($rootDir . $theme . '/views/' . $moduleName . '/'. $viewName . '.php');
    }

    /**
     * Memproses konten web berdasarkan informasi modul
     * @param mixed $hmvc array Informasi terkait modul yang akan diproses
     * @param mixed $instance object Instance dari modul yang akan diproses
     * @return string
     */
    public function renderView($instance, $module, $module_dir, $is_plugin) 
    {
        $view = $instance->getView();
        $path = $this->getThemeViewPath($module, $view['name']);

        if(!file_exists($path)) {
            $path = File::getInstance()->realPath($module_dir . '/views/' . $view['name'] . '.php');
            if(!file_exists($path)) {;
                throw new \Exception("View of ".$instance->getInfo('module_type')." '$module' is not found: $path", 404);
            }
        }

        $content = $instance->outputBuffering($path, $view['vars']);

        if((!$this->_activated || Boot::getInstance()->isErrorHandlerActive()) && !$is_plugin) {
            BaseController::setCore($instance);
            $this->_activated = true;
            $this->_content = $content;
        } else {
            return $content;
        }
    }

    /**
     * Mengambil konten view
     * @param object $instance Instance modul yang akan memproses
     * @param string $view Nama view yang akan diproses
     * @param array $params Parameter yang akan diekstrak ke bagian view
     * @return string
     */
    public function getView($instance, $view, $params = array()) {
        $moduleName = Config::getInstance()->getData('module');
        $path = $this->getThemeViewPath($moduleName, $view);
        if(!file_exists($path)) {
            $path = $this->moduleDir() . $moduleName . DS . 'views' . DS . $view . '.php';
            if(!file_exists($path)) {
                throw new \Exception("View file not found in path $path", 404);
            }
        }
        return $instance->outputBuffering($path, $params);
    }

    public function showOutput()
    {
        $path = View::getInstance()->getLayoutPath();
        if(!file_exists($path)) {
            throw new \Exception("Layout file not found in path {$path}", 404);
        }
        ob_end_clean();
        print Asset::getInstance()->render(BaseController::getCore()->outputBuffering($path));
    }
}