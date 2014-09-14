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
    private $content;

    private $is_activated = false;

    private $js_files = array();

    private $js_codes = array();

    private $css_files = array();

    private $css_codes = array();

    private $widgets = array();

    private $append_items = array();

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

    public function __construct()
    {
        $this->_dom = new \DomDocument(1.0, 'UTF-8');
    }

    public function getLayoutPath()
    {
        $dir = File::getInstance()->themeDir();
        $theme = Config::getInstance()->getData('theme');
        $layout = Config::getInstance()->getData('layout');
        $extension = Config::getInstance()->getData('use_html_layout') === true ? 'html' : 'php';
        return File::getInstance()->realPath($dir . $theme . DS . $layout . '.' . $extension);
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
     * Mengeset konten web
     * @return string
     */
    public function setContent($content)
    {
        return $this->content = $content;
    }

    public function isActivated()
    {
        return $this->is_activated;
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
    public function renderView($instance, $data, $module, $module_dir) 
    {
        $path = $this->getThemeViewPath($module, $data->name);

        if(!file_exists($path)) {
            $path = File::getInstance()->realPath($module_dir . '/views/' . $data->name . '.php');
            if(!file_exists($path)) {
                throw new \Exception("View of ".$instance->getInfo('module_type')." '$module' is not found: $path", 404);
            }
        }

        $content = $instance->outputBuffering($path, $data->vars);

        if($instance->getInfo('is_core') || Boot::getInstance()->isFoundError()) {
            $this->is_activated = true;
            $this->content = $content;
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
        if (!$this->is_activated && Config::getInstance()->getData('application') === false) return;

        $path = View::getInstance()->getLayoutPath();
        if(!file_exists($path)) {
            throw new \Exception("Layout file not found in path {$path}", 404);
        }
        
        $core = BaseController::getCore();
        if ($core === null) {
            $core->outputBuffering($path);
        } else {
            $content = BaseController::getCore()->outputBuffering($path);
        }

        libxml_use_internal_errors(true);
        DomHtml::getInstance()->loadHtml($content);
        libxml_clear_errors();

        $headElement = DomHtml::getInstance()->getElementsByTagName('head')->item(0);
        $bodyElement = DomHtml::getInstance()->getElementsByTagName('body')->item(0);

        if ($bodyElement === null) {
            $bodyElement = $headElement;
        }
        
        if (Config::getInstance()->getData('use_html_layout') === true) {
            DomHtml::getInstance()->append(Config::getInstance()->getData('htmlcontent_id'), $this->content);
        }

        // For CSS files
        if (!empty($this->css_files)) {
            foreach ($this->css_files as $file => $params) {
                $link = $headElement->appendChild(new \DomElement('link'));
                $link->setAttribute('rel', 'stylesheet');
                $link->setAttribute('type', 'text/css');
                $link->setAttribute('href', $file);
            }
        }
        // For CSS codes
        if (!empty($this->css_codes)) {
            $style = $headElement->appendChild(new \DomElement('style'));
            $style->setAttribute('type', 'text/css');
            $value = null;
            foreach ($this->css_codes as $code) {
                $value .= $code;
            }
            $style->nodeValue = $value;
        }
        // For JS files
        if (!empty($this->js_files)) {
            foreach ($this->js_files as $file => $params) {
                $script = $bodyElement->appendChild(new \DomElement('script'));
                $script->setAttribute('language', 'javascript');
                $script->setAttribute('src', $file);
            }
        }
        // For JS codes
        if (!empty($this->js_codes)) {
            $script = $bodyElement->appendChild(new \DomElement('script'));
            $script->setAttribute('language', 'javascript');
            $value = null;
            foreach ($this->js_codes as $code) {
                $value .= $code;
            }
            $script->nodeValue = $value;
        }
        // For DOM append
        if(!empty($this->append_items)) {
            foreach ($this->append_items as $element => $items) {
                foreach ($items as $item) {
                    DomHtml::getInstance()->append($element, $item);
                }
            }
        }
        // For widgets
        if (!empty($this->widgets)) {
            foreach ($this->widgets as $element => $widgets) {
                foreach ($widgets as $module => $params) {
                    DomHtml::getInstance()->append($element, BaseController::getInstance()->import($params, null, true));
                }
            }
        }
        
        $errors = Boot::getInstance()->getErrors();
        if (count($errors) == 0) {
            print DomHtml::getInstance()->saveHtml();
        } else {
            if (ob_get_level() > 0) {
                //ob_end_clean();
            }
            $path = SYSTEM_DIR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'error_reporting.php';
            print BaseController::getInstance()->outputBuffering($path, array('errors'=>$errors));
        }
    }

    public function addScript($code)
    {
        $this->js_codes = array_merge($this->js_codes, array($code));
    }

    public function addStyle($code)
    {
        $this->css_codes = array_merge($this->css_codes, array($code));
    }

    private function removeWhiteSpace($text)
    {
        return preg_replace('/\s{2,}/i', null, $text);
    }

    public function addWidget($element, $items) {
        if(is_array($items)) {
            if(isset($this->widgets[$element])) {
                $this->widgets[$element] = array_merge($this->widgets[$element], $items);
            } else {
                $this->widgets[$element] = $items;
            }
        } else {
            if(isset($this->widgets[$element])) {
                $this->widgets[$element][] = $items;
            } else {
                $this->widgets[$element] = array($items);
            }
        }
    }

    public function addFile($source, $params = array())
    {
        if(preg_match('/\.(css|js)$/i', $source, $match)) {
            switch ($match[1]) {
                // For Javascript
                case 'js':
                    $this->js_files = array_merge($this->js_files, array($source => $params));
                    break;
                // For CSS
                case 'css':
                    $this->css_files = array_merge($this->css_files, array($source => $params));
                    break;
            }
        } else {
            throw new \Exception("Asset file is not supported", 500);
        }
    }

    public function addAppendItem($element, $content)
    {
        if(isset($this->append_items[$element])) {
            $this->append_items[$element][] = $content;
        } else {
            $this->append_items[$element] = array($content);
        }
    }
}