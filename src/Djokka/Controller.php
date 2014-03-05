<?php

/**
 * Djokka Framework parent controller class file
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013-2014 Djokka Media
 */

namespace Djokka;

use Djokka\Route;
use Djokka\View\Asset;
use Djokka\Helpers\String;
use Djokka\Helpers\User;
use Djokka\Controller\Hmvc;
use Djokka\Controller\Modular;
use Djokka\Controller\Plugin;

/**
 * Parent class for all the module controller, not plugin module.
 * This class will provides access to models and views. The controller class
 * will control process inside a module.
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Controller extends Shortcut
{
    /**
     * Information of main view
     * @since 1.0.3
     */
    private $_view = array();

    /**
     * Instance of the core controller class. The system maybe load much controller object,
     * but only the core controller will set as main controller
     * @since 1.0.3
     */
    private static $_core;

    /**
     * Instance of this class
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Get the instance of this class via Singleton Pattern
     * @since 1.0.0
     * @return object Object of this class
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * Get the core controller instance
     * @since 1.0.3
     * @return object
     */
    public static function getCore()
    {
        return self::$_core;
    }

    /**
     * Set the controller object as core controller instance
     * @since 1.0.3
     * @param object $core The controller class object. The object must be
     * instance of Djokka\Controller class
     */
    public static function setCore(Controller $core)
    {
        self::$_core = $core;
    }

    /**
     * Sets the main view or loads the view
     * @param string $name Name of the view file without extension
     * @param array $vars The data that wants to extract to the view from controller
     * @return void|string
     * @since 1.0.0
     */
    public function view($name, array $vars = array())
    {
        if (empty($this->_view)) {
            $this->_view = array(
                'name' => $name,
                'vars' => $vars
            );
        } else {
            $info = $this->config('module_info');
            $path = $info['module_dir'].'views'.DS.$name . '.php';
            if (!file_exists($path)) {
                throw new \Exception("View of module '$info[route]' is not found: $path", 404);
            }
            return $this->outputBuffering($path, $vars);
        }
    }

    /**
     * Checks the controller is use view or no
     * @since 1.0.3
     * @return bool Returns TRUE if core controller is use view
     */
    public function isUseView()
    {
        return !empty($this->_view);
    }

    /**
     * Get the view information
     * @return array
     * @since 1.0.0
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Run output buffering to render the view
     * @param string $viewName Name of the view file without extension
     * @param array $vars Data that wants to extract to the view
     * @return string
     * @since 1.0.3
     */
    public function outputBuffering($viewName, array $vars = array())
    {
        ob_start();
        if (!empty($vars)) {
            extract($vars, EXTR_PREFIX_SAME, 'dj_');
        }
        if ($return = include($viewName)) {
            if ($return != 1) {
                return $return;
            }
        }
        return ob_get_clean();
    }

    /**
     * Get the core controller's view content
     * @since 1.0.0
     * @return string
     */
    public function getContent()
    {
        return View::getInstance()->getContent();
    }

    /**
     * Get an URI segment or all segments
     * @since 1.0.0
     * @param int $i Index of the URI segment
     * @return array|string
     */
    public function uri($i = null)
    {
        $uri_segments = Route::getInstance()->getUriSegments();
        if ($i === null) {
            return $uri_segments;
        } else {
            if (isset($uri_segments[$i])) {
                return $uri_segments[$i];
            }
        }
    }

    /**
     * Puts the Javascript code to body of layout HTML document
     * @param string $code Javascript code
     * @since 1.0.0
     */
    public function js($code) {
        Asset::getInstance()->js($code);
    }

    /**
     * Puts the CSS code to body of layout HTML document
     * @param string $code CSS code
     * @since 1.0.0
     */
    public function css($code) {
        Asset::getInstance()->css($code);
    }

    /**
     * Puts the link of Javascript or CSS file to the layout HTML document
     * @param string $url URL of the Javascript or CSS file
     * @since 1.0.0
     */
    public function asset($url) {
        Asset::getInstance()->add($url);
    }

    /**
     * Sets or gets the website theme
     * @since 1.0.0
     * @return void|string
     */
    public function theme()
    {
        if (func_num_args() == 0) {
            return $this->config('theme');
        } else {
            $this->config('theme', func_get_arg(0));
        }
    }

    /**
     * Sets or gets the website layout
     * @since 1.0.0
     * @return void|string
     */
    public function layout()
    {
        if (func_num_args() == 0) {
            return $this->config('layout');
        } else {
            return $this->config('layout', func_get_arg(0));
        }
    }

    /**
     * Gets the base URL
     * @since 1.0.0
     * @param string $url The string that will appends to the base URL
     * @return string
     */
    public function baseUrl($url = null)
    {
        return Route::getInstance()->base_url.'/'.$url;
    }

    /**
     * Calls the plugin module
     * @since 1.0.0
     * @param string $name Name of the plugin module
     * @return string
     */
    public function plugin($pluginName) 
    {
        return $this->import('plugin.' . $pluginName);
    }

    /**
     * Checks the module is plugin or not
     * @param string $route Route of the module that wants to check
     * @return bool Returns TRUE if the module is plugin
     */
    public function isPlugin($route) 
    {
        if (preg_match('/^plugin\.([a-zA-Z0-9_\/\-]+)/i', $route, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }

    /**
     * Calls and executes the other module
     * @since 1.0.0
     * @param string $route Route of the module that wants to call
     * @param array $params Paremeter that needs by the module
     * @param bool $is_widget Marks the module call as a widget or not
     * @return string
     */
    public function import($route,$params = null, $is_widget = false)
    {
        $is_plugin = false;
        if ($plugin = $this->isPlugin($route)) {
            $route = $plugin;
            $is_plugin = true;
        }
        if (!$is_widget) {
            $info = $route == $this->config('route') ? $this->config('module_info') : Route::getInstance()->getModuleInfo($route, $is_plugin);
        } else {
            $info = Route::getInstance()->getModuleInfo($route, $is_plugin, true);
        }
        return Hmvc::getInstance()->getViewContent($info, $params);
    }

    /**
     * Gets the content of layout file
     * @param string $layoutName Name of the layout file without extension
     * @since 1.0.0
     * @return string
     */
    public function getLayout($layoutName)
    {
        $path = $this->themeDir().$this->theme().'/'.$layoutName.'.php';
        if (!file_exists($path)) {
            throw new \Exception("Layout file not found in path $path", 404);
        }
        return $this->outputBuffering($path);
    }

    /**
     * Calls the widget or append the result to HTML document
     * @since 1.0.0
     * @param string $element ID of the HTML element that became append target
     * @param mixed $items The widget module or list of widget modules
     * @return string|void
     */
    public function widget($element, $items = null)
    {
        if ($items !== null) {
            Asset::getInstance()->setWidget($element, $items);
        } else {
            return $this->import($element, null, true);
        }
    }

    /**
     * Extracts extended data from controller to the view
     * @param array $data Data that wants to extract
     * @since 1.0.3
     */
    public function extract(array $data)
    {
        $this->_view['vars'] = array_merge($this->_view['vars'], $data);
    }
}