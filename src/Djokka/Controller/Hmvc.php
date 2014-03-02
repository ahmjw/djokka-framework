<?php

/**
 * Memproses modul yang menggunakan arsitektur HMVC
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.1
 */

namespace Djokka\Controller;

use Djokka\View;
use Djokka\Route;
use Djokka\Model\Pager;
use Djokka\View\Asset;
use Djokka\Controller as Core;
use Djokka\Helpers\String;
use Djokka\Helpers\User;

/**
 * Kelas pendamping yang membantu kelas Djokka\Controller untuk memproses modul yang menggunakan arsitektur HMVC
 */
class Hmvc extends Core
{
    /**
     * Menampung instance dari kelas
     * @since 1.0.1
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.1
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * Mengambil konten view pada modul yang menggunakan arsitektur HMVC
     * @param mixed $info Informasi modul
     * @param array $params Parameter yang akan dikirimkan ke fungsi aksi
     * @return string
     */
    public function getViewContent($info, $params = array()) {
        // Mengumpulkan informasi aksi
        if (!file_exists($info['path'])) {
            throw new \Exception("Class of module is not found: $info[path]", 404);
        }
        include_once($info['path']);
        if(!class_exists($info['class'])) {
            throw new \Exception("Class $class is not declared in file $path", 500);
        }
        $instance = new $info['class'];

        // Self router aliasing
        if(!$info['is_widget'] && method_exists($instance, 'routes') && ($routes = call_user_func(array($instance, 'routes')))) {
            foreach ($routes as $route) {
                $keys = array();
                $pattern = preg_replace_callback('/\(([a-zA-Z_](?:[a-zA-Z0-9_]+)?):(.*?)\)/i', function($matches) use(&$keys) {
                    $keys[] = $matches[1];
                    $group = $matches[2] !== null ? $matches[2] : '.+';
                    return '('.$group.')';
                }, $route[0]);
                $pattern = '/'.str_replace('/', '\/', $pattern).'/i';
                if(preg_match($pattern, Route::getInstance()->getUri(), $match)) {
                    $values = array_slice($match, 1);
                    $params = array();
                    foreach ($values as $i => $value) {
                        $params[$keys[$i]] = $value;
                    }
                    $info['function'] = 'action'.ucfirst($route[1]);
                    $info['params'] = $params;
                    break;
                }
            }
        }
        
        // Mengeksekusi suatu aksi
        if(!method_exists($instance, $info['function'])) {
            throw new \Exception("Method $info[function]() is not defined in class $info[class] in file $info[path]", 404);
        }
        if(method_exists($instance, 'accessControl') && ($access = call_user_func(array($instance, 'accessControl')))) {
            if(!empty($access)) {
                foreach ($access as $rule) {
                    if(preg_match('/(^(?:'.$info['action'].')\s*\,|\,\s*(?:'.$info['action'].')\s*\,|\s*(?:'.$info['action'].')$)/i', $rule['actions'], $match)) {
                        if(!(bool)$rule['condition']) {
                            throw new \Exception("You doesn't have a credential to access this page", 403);
                        }
                    }
                }
            }
        }
        $return = call_user_func_array(array(
            $instance, $info['function']), $info['params']
        );

        if($this->config('json') === false && $instance->isUseView()) {
            return View::getInstance()->renderContent($info, $instance);
        } else {
            return $return;
        }
    }

}