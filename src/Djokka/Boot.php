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
    private $found_error = false;
    private $error_info = false;
    private $errors = array();

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
        // Error reporting
        if (error_reporting() > 0) {
            set_error_handler(array($this, 'onError'));
            register_shutdown_function(array($this, 'onShutdown'));
        }
        // Exception handler
        set_exception_handler(array($this, 'handleException'));
        // Internal class autoloader
        spl_autoload_register(array($this, 'autoload'));
    }

    public function onError($code, $message, $file, $line, $context = null)
    {
        $this->errors[] = array(
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line,
        );
    }

    /**
     * Get the last error on the PHP shutting down
     * @since 1.0.3
     */
    public function onShutdown()
    {
        if (($error = error_get_last()) !== null) {
            $this->error_info = $error;
            $this->handleException(new \ErrorException($error['message'], 500, $error["type"], $error["file"], $error["line"]));
        }
        die();
    }

    /**
     * Show the caught exception as HTML document
     * @param object $error The exception object. The object must be instance of Exception class
     * @since 1.0.3
     */
    public function handleException(\Exception $error)
    {
        if (Config::getInstance()->getData('json') === false) {
            $path = SYSTEM_DIR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'exception.php';
            $use_default_view = false;
            try {
                if (Config::getInstance()->getData('error_redirect') === true) {
                    if ($error->getCode() == 403) {
                        // Forbidden access
                        $page = Config::getInstance()->getData('module').'/'.Config::getInstance()->getData('action');
                        if ($page != ($redirect = Config::getInstance()->getData('module_forbidden'))) {
                            BaseController::getInstance()->redirect('/' . $redirect);
                        }
                    } else {
                        if (Config::getInstance()->getData('application') === false) {
                            // Show error with defined view
                            $this->found_error = true;
                            $moduleName = Config::getInstance()->getData('module_error');
                            BaseController::getInstance()->import($moduleName, array('error' => $error), true);
                            $this->found_error = false;
                            if (ob_get_level() > 0) {
                                ob_end_clean();
                            }
                            View::getInstance()->showOutput();
                        } else {
                            $path = Config::getInstance()->getData('dir') . Config::getInstance()->getData('component_path') . '/error.php';
                            $path = $this->realPath($path);
                            if (!file_exists($path)) {
                                throw new \Exception("Boot file is not found in path: $path", 404);
                            }
                            Config::getInstance()->setData('found_error', true);
                            include($path);
                        }
                    }
                } else {
                    $use_default_view = true;
                }
            } catch (\Exception $ex) {
                $use_default_view = true;
            }
            if ($use_default_view) {
                // Show error with default view
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                print BaseController::getInstance()->outputBuffering($path, array(
                    'error'=>$error,
                ));
            }
        } else {
            // Show error with JSON view
            self::$_instance->jsonOutput(array(
                'error' => array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ),
            ));
        }
    }

    /**
     * Gets error handler active status
     * @return bool
     * @since 1.0.3
     */
    public function isFoundError()
    {
        return $this->found_error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorInfo($key = null)
    {
        if ($key === null) {
            return $this->error_info;
        } else {
            return $this->error_info[$key];
        }
    }

    /**
     * Load the class file automatically on class instantiation.
     * It will loads class file of model and component
     * @since 1.0.1
     * @param string $className Name of the class
     */
    public function autoload($className)
    {
        $path = $this->realPath($this->componentDir() . $className . '.php');
        if (!file_exists($path)) {
            throw new \Exception("Component file not found at path $className", 404);
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
        if (Config::getInstance()->getData('application') === false) {
            BaseController::getInstance()->import($route);
            View::getInstance()->showOutput();
        } else {
            $path = Config::getInstance()->getData('dir') . Config::getInstance()->getData('component_path') . '/boot.php';
            $path = $this->realPath($path);
            if (!file_exists($path)) {
                throw new \Exception("Boot file is not found in path: $path", 404);
            }
            include($path);
        }
    }

    public function jsonOutput(array $data = array())
    {
        header('Content-type: application/json');
        echo json_encode($data);
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
        if (isset($_GET['djokka'])) {
            JsonDataExtractor::getInstance()->render();
            exit;
        }
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