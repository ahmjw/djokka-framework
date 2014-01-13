<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka\Helpers;

/**
 * Kelas Djokka\Config adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Config extends \Djokka
{
    /**
     * @var Menampung data konfigurasi web
     * @access private
     * @since 1.0.0
     */
    private $data = array(
        // System path
        'theme_path'=>'themes',
        'asset_path'=>'assets',
        'module_path'=>'protected/modules',
        'plugin_path'=>'assets/plugins',
        'component_path'=>'protected/components',
        'config_path'=>'protected/config',

        // Error handler
        'error_redirect'=>false,
        'module_error'=>'/index/error',
        'module_forbidden'=>'/index/signin',

        // Controller configurarion
        'main_module'=>'index',
        'modular_parent'=>null,
        'module'=>null,
        'architecture'=>null,

        // Router configuration
        'get_router'=>'r',
        'route_format'=>'path',
        'route_max_depth'=>20,
        'route_params'=>array(),
        'router_action'=>null,
        'ref_dir'=>null,
        'router'=>null,
        'asset_url'=>null,
        'theme_url'=>null,

        // View configuration
        'theme'=>'default',
        'layout'=>'index',
        'json'=>false,
        'debug_json_mode'=>false,

        'connection'=>0
    );

    private $class_map = array(
        'Session'=>'Djokka\\Helpers\\Session',
        'User'=>'Djokka\\Helpers\\User',
        'Config'=>'Djokka\\Helpers\\Config',
        'Html'=>'Djokka\\Helpers\\Html',
        'Email'=>'Djokka\\Helpers\\Email',
        'Image'=>'Djokka\\Helpers\\Image',
        'File'=>'Djokka\\Helpers\\File',
        'String'=>'Djokka\\Helpers\\String',
        'Db'=>'Djokka\\Db',
        'Route'=>'Djokka\\Route',
        'Asset'=>'Djokka\\View\\Asset',
        'Model'=>'Djokka\\Model'
    );

    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
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

    public function __construct() {
        if(isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['DOCUMENT_ROOT'])) {
            $dir = String::get()->unlastPart('/', $_SERVER['SCRIPT_NAME']);
            if($dir == '') {
                $dir = String::get()->unlastPart('\\', $_SERVER['SCRIPT_NAME']);
            }
            $this->data['dir'] = $this->realPath($_SERVER['DOCUMENT_ROOT'].$dir.DS);
        }
    }

    public function render() {
        $dir = $this->configDir();
        $path = $dir.'main.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                $this->merge($data);
            }
        }

        $path = $dir.'db.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                $this->merge(array(
                    'db'=>$data
                ));
            }
        }

        $path = $dir.'routes.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                $this->merge(array(
                    'routes'=>$data
                ));
            }
        }
        $path = $dir.'.debug';
        if(file_exists($path)) {
            $this->merge(array('debug_json_mode'=>true));
        }
    }

    public function getClassMap() {
        return $this->class_map;
    }

    /**
     * Memasukkan konfigurasi dari index.php menuju ke sistem web
     * @since 1.0.0
     * @param $data adalah data konfigurasi yang akan dimasukkan dalam bentuk array
     */
    public function merge($data = array())
    {
         $this->data = array_merge($this->data, $data);
    }

    /**
     * Mengecek eksistensi (sudah ada atau belum) suatu konfigurasi
     * @since 1.0.0
     * @param $key adalah nama atribut konfigurasi
     * @return data konfigurasi
     */
    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Fungsi yang dijalankan ketika kelas inti framework memanggil
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka nilai baliknya data semua konfigurasi
     * - Jika memasukkan satu parameter, maka nilai balikanya data konfigurasi sesuai
     *   atribut yang dimasukkan
     * - Jika memasukkan dua parameter, maka dia akan menentukan nilai terhadap
     *   konfigurasi dengan atribut yang dimasukkan
     * @return objek instance kelas
     */
    public function getConfig()
    {
        return $this->data;
    }
    public function setConfig($data)
    {
        return $this->data = $data;
    }

    public function getData($data)
    {
        return $this->data[$data];
    }

    public function setData($data, $value)
    {
        return $this->data[$data] = $value;
    }

}