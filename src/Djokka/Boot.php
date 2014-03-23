<?php

/**
 * Djokka Framework boot class file
 * @since 1.0.1
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @since 1.0.3
 */

namespace Djokka;

use Djokka\Helpers\Config;

/**
 * Marks the file is loaded by system
 */
define('DJOKKA', true);

/**
 * Short constant for directory separator
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Marks the error handling is activate or no
 */
defined('HANDLE_ERROR') or define('HANDLE_ERROR', false);

/**
 * Define the system directory or root directory of Djokka Framework
 */
define('SYSTEM_DIR', __DIR__ . DS . '..' . DS . '..' . DS);

/**
 * Boot class is using to booting system to run the website.
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.3
 */
class Boot extends Shortcut
{
    /**
     *
     */
    private static $_errorHandlerActive = false;

    /**
     * Instance of this class
     * @since 1.0.1
     */
    private static $_instance;

    /**
     * Get the instance of this class via Singleton Pattern
     * @since 1.0.1
     * @return object
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * Register and activate autoloader and handler
     * @since 1.0.1
     */
    public function registerAutoload()
    {
        // Error and exception handling
        if (HANDLE_ERROR === true) {
            register_shutdown_function(array(__CLASS__, 'onShutdown'));
            set_error_handler(array(__CLASS__, 'handleError'), E_ALL ^ E_NOTICE);
            ini_set('display_errors', 'off');
            error_reporting(E_ALL ^ E_NOTICE);
        }
        set_exception_handler(array(__CLASS__, 'handleException'));
        // Internal class autoloader
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Get the last error on the PHP shutting down
     * @since 1.0.3
     */
    public static function onShutdown()
    {
        if (self::$_errorHandlerActive) return;
        if (($error = error_get_last()) !== null) {
            self::handleError($error["type"], $error["message"], $error["file"], $error["line"]);
        }
    }

    /**
     * Handle error and throw as exception
     * @param int $num Error code
     * @param string $str Error message
     * @param string $file File path that has error
     * @param int $line The line number in file that has error
     * @param array $context Arguments from the PHP system
     * @throws ErrorException to catch by exception handler
     * @since 1.0.3
     */
    public static function handleError($num, $str, $file, $line, $context = null)
    {
        self::handleException(new \ErrorException( $str, 0, $num, $file, $line));
    }

    /**
     * Show the caught exception as HTML document
     * @param object $e The exception object. The object must be instance of Exception class
     * @since 1.0.3
     */
    public static function handleException(\Exception $e)
    {
        $path = SYSTEM_DIR . 'resources' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'view.php';

        try {
            if (Config::getInstance()->getData('error_redirect') === true) {
                if ($e->getCode() == 403) {
                    $page = Config::getInstance()->getData('module').'/'.Config::getInstance()->getData('action');
                    if ($page != ($redirect = Config::getInstance()->getData('module_forbidden'))) {
                        BaseController::getInstance()->redirect('/' . $redirect);
                    }
                } else {
                    self::$_errorHandlerActive = true;
                    $moduleName = Config::getInstance()->getData('module_error');
                    BaseController::getInstance()->import($moduleName, array('error' => $e), true);
                }
            } else {
                print BaseController::getInstance()->outputBuffering($path, array('e'=>$e));
            }
        } catch (\Exception $ex) {
            print BaseController::getInstance()->outputBuffering($path, array('e'=>$ex));
        }
    }

    /**
     * Gets error handler active status
     * @return bool
     * @since 1.0.3
     */
    public static function isErrorHandlerActive()
    {
        return self::$_errorHandlerActive;
    }

    /**
     * Load the class file automatically on class instantiation.
     * It will loads class file of model and component
     * @since 1.0.1
     * @param string $className Name of the class
     */
    public function autoload($className)
    {
        $path = $this->realPath($this->componentDir().$className.'.php');
        if (!file_exists($path)) {
            throw new \Exception("Component file not found at path $path", 404);
        }
        include_once($path);
    }

    /**
     * Runs the website
     * @param string $route Module route that wants to load directly
     * @since 1.0.1
     */
    public function run($route = null)
    {
        if ($route === null) {
            Route::getInstance()->load();
            $route = $this->config('route');
        }
        BaseController::getInstance()->import($route);
    }

    /**
     * Loads the internal application provided By Djokka Framework
     * @since 1.0.3
     * @return bool Returns TRUE if the internal applications is successfully loaded
     */
    private function loadInternalApp()
    {
        if (isset($_GET['djokka']) && !empty($_GET['djokka'])) {
            Config::getInstance()->merge(array(
                'app_config' => $this->config(),
                'dir' => SYSTEM_DIR.'resources'.DS.'apps'.DS.$_GET['djokka'],
                'theme_path' => '../../themes',
                'asset_path' => '../../assets',
            ));
            return true;
        }
        return false;
    }

    /**
     * Initialize the preload configurations
     * @since 1.0.1
     * @param array|string $config The preload configurations
     * @return object Object of this class
     */
    public function init($config = null)
    {
        $this->registerAutoload();
        if (!$this->loadInternalApp()) {
            if ($config !== null) {
                if (is_array($config)) {
                    Config::getInstance()->merge($config);
                } else {
                    Config::getInstance()->merge(array(
                        'dir'=>$config,
                    ));
                    Config::getInstance()->render();
                }
            } else {
                Config::getInstance()->render();
            }
        }
        return $this;
    }
}