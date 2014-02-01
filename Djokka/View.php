<?php

namespace Djokka;

use Djokka\View\Asset;

class View extends \Djokka 
{
    private $content;
    private $index = -1;
    private $views = array();
    private $active = false;
    private $use_theme = true;

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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getViews()
    {
        return $this->views;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setUseTheme($condition) {
        $this->use_theme = $condition;
    }

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

    public function renderTheme($content) 
    {
        // Jika dalam mode JSON
        if($this->config('json') === true || HANDLE_ERROR === true) {
            header('Content-type: application/json');
            echo json_encode($content);
            return;
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
     * @param $view adalah lokasi view
     * @param $params adalah parameter tambahan untuk dimasukkan ke view
     */
    public function mergeView($name, $params = array())
    {
        $view = array('view'=>$name, 'params'=>$params);
        $this->views = array_merge($this->views, array($view));
        $this->index++;
        $this->active = true;
    }

    public function getView($instance, $view, $params = array()) {
        $path = $this->themeDir().$this->config('theme').DS.'views'.DS.$module.DS.$view.'.php';
        if(!file_exists($path)) {
            $path = $this->moduleDir().$this->config('module').DS.'views'.DS.$view.'.php';
            if(!file_exists($path)) {
                throw new \Exception("View file not found in path $path", 404);
            }
        }
        return $instance->render($path, $params);
    }

}