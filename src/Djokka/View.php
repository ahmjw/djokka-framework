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

    private $_js_files = array();

    private $_js_codes = array();

    private $_css_files = array();

    private $_css_codes = array();

    private $_widgets = array();

    private $_append_items = array();

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
        $extension = Config::getInstance()->getData('use_html_layout') === true ? 'html' : 'php';
        return File::getInstance()->realPath($dir . $theme . DS . $layout . '.' . $extension);
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
        $view = $instance->getViewData();
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
        $content = BaseController::getCore()->outputBuffering($path);
        libxml_use_internal_errors(true);
        DomHtml::getInstance()->loadHtml($content);
        libxml_clear_errors();

        $headElement = DomHtml::getInstance()->getElementsByTagName('head')->item(0);
        $bodyElement = DomHtml::getInstance()->getElementsByTagName('body')->item(0);
        if (Config::getInstance()->getData('use_html_layout') === true) {
            DomHtml::getInstance()->append(Config::getInstance()->getData('html_content_id'), $this->_content);
        }

        // For CSS files
        if (!empty($this->_css_files)) {
            foreach ($this->_css_files as $file => $params) {
                $link = $headElement->appendChild(new \DomElement('link'));
                $link->setAttribute('rel', 'stylesheet');
                $link->setAttribute('type', 'text/css');
                $link->setAttribute('href', $file);
            }
        }
        // For CSS codes
        if (!empty($this->_css_codes)) {
            $style = $headElement->appendChild(new \DomElement('style'));
            $style->setAttribute('type', 'text/css');
            $value = null;
            foreach ($this->_css_codes as $code) {
                $value .= $code;
            }
            $style->nodeValue = $this->removeWhiteSpace($value);
        }
        // For JS files
        if (!empty($this->_js_files)) {
            foreach ($this->_js_files as $file => $params) {
                $script = $bodyElement->appendChild(new \DomElement('script'));
                $script->setAttribute('language', 'javascript');
                $script->setAttribute('src', $file);
            }
        }
        // For JS codes
        if (!empty($this->_js_codes)) {
            $script = $bodyElement->appendChild(new \DomElement('script'));
            $script->setAttribute('language', 'javascript');
            $value = null;
            foreach ($this->_js_codes as $code) {
                $value .= $code;
            }
            $script->nodeValue = $this->removeWhiteSpace($value);
        }
        // For DOM append
        if(!empty($this->_append_items)) {
            foreach ($this->_append_items as $element => $items) {
                foreach ($items as $item) {
                    DomHtml::getInstance()->append($element, $item);
                }
            }
        }
        // For widgets
        if (!empty($this->_widgets)) {
            foreach ($this->_widgets as $element => $widgets) {
                foreach ($widgets as $module => $params) {
                    DomHtml::getInstance()->append($element, BaseController::getInstance()->import($params, null, true));
                }
            }
        }
        print DomHtml::getInstance()->saveHtml();
    }

    /**
     * Menempelkan suatu konten pada suatu elemen HTML
     * @since 1.0.0
     * @param $element adalah ID elemen HTML tempat penempelan konten
     * @param $content adalah konten yang akan ditempelkan pada elemen
     */
    public function append($element, $content)
    {
        if($content == null) return;

        libxml_use_internal_errors(true);
        $this->helper->loadHTML($content);
        libxml_clear_errors();
        $pAttach = $this->helper->getElementsByTagName('body');
        $document = $this->getElementById($element);

        if($document == null) return;
        if ($pAttach->length) {
            for ($i = 0; $i < $pAttach->item(0)->childNodes->length; $i++) {
                $document->appendChild($this->importNode($pAttach->item(0)->childNodes->item($i), true));
            }
        }
    }

    public function addScript($code)
    {
        $this->_js_codes = array_merge($this->_js_codes, array($code));
    }

    public function addStyle($code)
    {
        $this->_css_codes = array_merge($this->_css_codes, array($code));
    }

    private function removeWhiteSpace($text)
    {
        return preg_replace('/\s{2,}/i', null, $text);
    }

    public function addWidget($element, $items) {
        if(is_array($items)) {
            if(isset($this->_widgets[$element])) {
                $this->_widgets[$element] = array_merge($this->_widgets[$element], $items);
            } else {
                $this->_widgets[$element] = $items;
            }
        } else {
            if(isset($this->_widgets[$element])) {
                $this->_widgets[$element][] = $items;
            } else {
                $this->_widgets[$element] = array($items);
            }
        }
    }

    public function addFile($source, $params = array())
    {
        if(preg_match('/\.(css|js)$/i', $source, $match)) {
            switch ($match[1]) {
                // For Javascript
                case 'js':
                    $this->_js_files = array_merge($this->_js_files, array($source => $params));
                    break;
                // For CSS
                case 'css':
                    $this->_css_files = array_merge($this->_css_files, array($source => $params));
                    break;
            }
        } else {
            throw new \Exception("Asset file is not supported", 500);
        }
    }

    public function addAppendItem($element, $content)
    {
        if(isset($this->_append_items[$element])) {
            $this->_append_items[$element][] = $content;
        } else {
            $this->_append_items[$element] = array($content);
        }
    }
}