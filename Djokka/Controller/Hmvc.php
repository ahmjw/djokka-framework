<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.1
 */

namespace Djokka\Controller;

use Djokka\View;
use Djokka\Model\Pager;
use Djokka\View\Asset;
use Djokka\Controller as Core;
use Djokka\Helpers\String;
use Djokka\Helpers\User;

/**
 * Kelas Djokka\Controller adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Hmvc extends Core
{
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

    public function getViewContent($info, $params = array()) {
        // Mengumpulkan informasi aksi
        include_once($info['path']);
        if(!class_exists($info['class'])) {
            throw new \Exception("Class $class is not declared in file $path", 500);
        }
        $instance = new $info['class'];
        if($request = $this->config('router_action')) {
            $info['function'] = 'action'.ucfirst($request);
            $info['params'] = $this->config('router_params');
            $this->config('router_action', null);
            $this->config('router_params', null);
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

        return View::get()->renderContent($info, $instance);
    }

}