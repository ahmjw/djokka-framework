<?php

/**
 * Mengelola konfigurasi web
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Helpers;

/**
 * Kelas pembantu yang bertugas mengelola konfigurasi web
 */
class Config
{
    //use TShortcut;
    /**
     * Menampung data konfigurasi web
     * @since 1.0.0
     */
    private $_data = array(
        // System path
        'theme_path'     => 'themes', // Lokasi folder tema
        'asset_path'     => 'assets', // Lokasi folder aset
        'module_path'    => 'protected/modules', // Lokasi folder module
        'plugin_path'    => 'plugins', // Lokasi folder plugin
        'component_path' => 'protected/components', // Lokasi folder komponen
        'model_path'     => 'protected/models', // Lokasi folder model
        'data_path'      => 'protected/data', // Lokasi folder model
        'config_path'    => 'protected/config', // Lokasi folder konfigurasi
        'app_path'       => null, // Lokasi folder project/aplikasi web

        // Error handler
        'error_redirect'   => false, // Menandai apakah dilakukan pengalihan halaman saat terjadi error
        'module_error'     => 'index/error', // Nama modul tujuan pengalihan jika terjadi error
        'module_forbidden' => 'index/signin', // Nama modul tujuan pengalihan saat terjadi error akses ditolak

        // Router configuration
        'routes'           => array(), // Data route-aliasing
        'get_router'       => 'r', // Nama key untuk format rute GET
        'route_format'     => 'path', // Format rute yang akan digunakan
        'route_max_depth'  => 20, // Maksimum kedalaman rute
        'module'           => null, // Nama modul yang sedang diakses
        'action'           => null, // Nama action yang sedang diakses
        'route'           => null, // Rute yang sedang diakses
        'asset_url'        => null, // URL untuk aset
        'theme_url'        => null, // URL untuk tema
        'plugin_url'       => null,

        // View configuration
        'theme'            => 'default', // Nama tema yang sedang digunakan web
        'layout'           => 'index', // Nama layout yang sedang digunakan web
        'json'             => false, // Menandai apakah output web menggunakan format JSON atau tidak
        'use_html_layout'  => false,
        'application'      => true,
        'html_content_id'  => 'main',

        'connection'       => 0, // Indeks koneksi database yang akan digunakan pada fitur multi-database
        'database_driver'  => 'MySql',
        'user_config'      => array(),
    );

    /**
     * Peta kelas yang akan menjadi anggota pustaka yang dapat dipanggil
     */
    private $class_map = array(
        'Session'    => 'Helpers\\Session',
        'Cookie'    => 'Helpers\\Cookie',
        'User'       => 'Helpers\\User',
        'Config'     => 'Helpers\\Config',
        'Html'       => 'Helpers\\Html',
        'Email'      => 'Helpers\\Email',
        'Image'      => 'Helpers\\Image',
        'File'       => 'Helpers\\File',
        'String'     => 'Helpers\\String',
        'Route'      => 'Route',
        'Hmvc'       => 'Hmvc',
        'Controller' => 'BaseController',
    );

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
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

    /**
     * Konstruktor kelas
     */
    public function __construct() 
    {
        if(isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['DOCUMENT_ROOT'])) {
            $dir = String::getInstance()->unlastPart('/', $_SERVER['SCRIPT_NAME']);
            if($dir == '') {
                $dir = String::getInstance()->unlastPart('\\', $_SERVER['SCRIPT_NAME']);
            }
            $this->_data['dir'] = preg_replace("/([\/\\\]+)/i", DS, $_SERVER['DOCUMENT_ROOT'].$dir).DS;
        }
    }

    /**
     * Membaca nilai konfigurasi yang diletakkan di dalam berkas konfigurasi dan memasukkannya ke dalam sistem
     */
    public function render($dir = null) 
    {
        $is_auto = false;
        if($dir === null) {
            $is_auto = true;
            $dir = $this->_data['dir'].$this->_data['app_path'].$this->_data['config_path'].DS;
            $dir = File::getInstance()->realPath($dir);
        }
        $result = array();
        $path = $dir.'general.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                if($is_auto) {
                    $this->merge($data);
                } else {
                    $result = array_merge($result, $data);
                }
            }
        }

        $path = $dir.'db.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                if($is_auto) {
                    $this->merge(array(
                        'db'=>$data
                    ));
                } else {
                    $result = array_merge($result, array(
                        'db'=>$data
                    ));
                }
            }
        }

        $path = $dir.'user.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                if($is_auto) {
                    $this->merge(array(
                        'user_config'=>$data
                    ));
                } else {
                    $result = array_merge($result, array(
                        'user_config'=>$data
                    ));
                }
            }
        }

        $path = $dir.'routes.php';
        if(file_exists($path)) {
            $data = include($path);
            if(is_array($data)) {
                if($is_auto) {
                    $this->merge(array(
                        'routes'=>$data
                    ));
                } else {
                    $result = array_merge($result, array(
                        'routes'=>$data
                    ));
                }
            }
        }
        return $result;
    }

    /**
     * Mengambil data peta kelas
     * @return array
     */
    public function getClassMap() 
    {
        return $this->class_map;
    }

    /**
     * Memasukkan konfigurasi dari index.php menuju ke sistem web
     * @since 1.0.0
     * @param $data adalah data konfigurasi yang akan dimasukkan dalam bentuk array
     */
    public function merge($data = array())
    {
         $this->_data = array_merge($this->_data, $data);
    }

    /**
     * Mengecek eksistensi (sudah ada atau belum) suatu konfigurasi
     * @since 1.0.0
     * @param $key adalah nama atribut konfigurasi
     * @return data konfigurasi
     */
    public function isExists($key)
    {
        return isset($this->_data[$key]);
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
        return $this->_data;
    }

    public function getUserConfig()
    {
        return $this->_data['user_config'];
    }

    /**
     * Menetapkan konfigurasi secara langsung
     * @param mixed $data array Pasangan key dan value konfigurasi yang akan dimasukkan
     */
    public function setConfig($data)
    {
        $this->_data = $data;
    }

    /**
     * Mengambil nilai konfigurasi berdasarkan key
     * @param mixed $data string Nama key konfigurasi yang akan diambil
     * @return int|string|float|object|array|bool
     */
    public function getData($data)
    {
        if(isset($this->_data[$data])) {
            return $this->_data[$data];
        }
        return;
    }

    public function getUserConfigData($data)
    {
        if(isset($this->_data['user_config'][$data])) {
            return $this->_data['user_config'][$data];
        }
        return;
    }

    /**
     * Menetapkan nilai konfigurasi berdasarkan key
     * @param mixed $data string Nama key konfigurasi yang akan ditetapkan
     * @param mixed $value alltype Nilai konfigurasi yang akan diberikan pada konfigurasi berdasarkan key
     */
    public function setData($data, $value)
    {
        $this->_data[$data] = $value;
    }

    public function delete()
    {
        $params = func_get_arg(0);
        if (!is_array($params)) {
            unset($this->_data[$params]);
        } else {
            foreach ($params as $key) {
                unset($this->_data[$key]);
            }
        }
    }
}