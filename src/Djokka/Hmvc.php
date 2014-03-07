<?php

/**
 * Memproses modul yang menggunakan arsitektur HMVC
 * @since 1.0.3
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 */

namespace Djokka;

use Djokka\Helpers\File;
use Djokka\Helpers\String;
use Djokka\Helpers\Config;

/**
 * The Djokka Framework HMVC Engine class
 */
class Hmvc
{
    /**
     * The module name
     * @since 1.0.3
     */
    public $module = 'index';

    /**
     * The action name
     * @since 1.0.3
     */
    public $action = 'index';

    /**
     * The route of module
     * @since 1.0.3
     */
    public $route;

    /**
     * String of router
     * @since 1.0.3
     */
    public $router;

    /**
     * The function name
     * @since 1.0.3
     */
    public $function;

    /**
     * The class name
     * @since 1.0.3
     */
    public $class;

    /**
     * The parameters of function
     * @since 1.0.3
     */
    public $params  = array();

    /**
     * The root directory of modules
     * @since 1.0.3
     */
    public $dir;

    /**
     * The path of current module's controller
     * @since 1.0.3
     */
    public $path;

    /**
     * The root directory of current module
     * @since 1.0.3
     */
    public $module_dir;

    /**
     * Marks the module as plugin
     * @since 1.0.3
     */
    public $is_plugin = false;

    /**
     * Marks the action as widget
     * @since 1.0.3
     */
    public $is_widget = false;

    /**
     * The constructor of this class
     * @since 1.0.3
     */
    public function __construct($router, $is_plugin = false, $is_widget = false)
    {
        // Initialize fields
        $this->router = $router;
        $this->is_plugin = $is_plugin;
        $this->is_widget = $is_widget;
        $this->trace();
    }

    private function trace()
    {
        $this->dir = !$this->is_plugin ? File::getInstance()->moduleDir() : File::getInstance()->pluginDir();

        if (is_numeric(strrpos($this->router, '/'))) {
            $routes = explode('/', $this->router, Config::getInstance()->getData('route_max_depth'));
            $i = 0;
            $temp_mod = null;
            $prev_mod = null;

            foreach ($routes as $route) {
                if (!$route) continue;
                if ($i == 0) {
                    $this->module = $route;
                    $temp_mod .= $route . '/';
                } else {
                    $temp_mod .= 'modules/' . $route . '/';
                }
                $this->path = File::getInstance()->realPath($this->dir . $temp_mod);
                if (!file_exists($this->path)) {
                    $this->action = $route;
                    $this->path = File::getInstance()->realPath($this->dir . $prev_mod);
                    break;
                }
                if ($i > 0) {
                    $this->module .= '/'.$route;
                    $prev_mod .= 'modules/' . $route . '/';
                } else {
                    $prev_mod .= $route . '/';
                }
                $i++;
            }
            $this->params = array_slice($routes, $i + 1);
        } else {
            $this->module = !empty($this->router) ? $this->router: 'index';
            $this->path = $this->dir . $this->module . DS;
        }

        // Update the fields
        $last_part = ucfirst(String::getInstance()->lastPart('/', $this->module));
        $this->route = $this->module . '/' . $this->action;
        $this->function = $this->is_widget ? 'widget' . ucfirst($this->action) : 'action' . ucfirst($this->action);
        $this->class = 'Djokka\\'.(!$this->is_plugin ? 'Controllers' : 'Plugins') . '\\' . $last_part;
        $this->module_dir = $this->path;
        $this->path = File::getInstance()->realPath($this->path . DS . $last_part . '.php');
    }
}