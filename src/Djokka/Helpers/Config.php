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

use Djokka\Base;

/**
 * Kelas pembantu yang bertugas mengelola konfigurasi web
 */
class Config extends Base
{
    /**
     * Menampung data konfigurasi web
     * @since 1.0.0
     */
    private $data = array(
        // System path
        'theme_path'=>'themes', // Lokasi folder tema
        'asset_path'=>'assets', // Lokasi folder aset
        'module_path'=>'protected/modules', // Lokasi folder module
        'plugin_path'=>'assets/plugins', // Lokasi folder plugin
        'component_path'=>'protected/components', // Lokasi folder komponen
        'model_path'=>'protected/models', // Lokasi folder model
        'config_path'=>'protected/config', // Lokasi folder konfigurasi
        'app_path'=>null, // Lokasi folder project/aplikasi web

        // Error handler
        'error_redirect'=>false, // Menandai apakah dilakukan pengalihan halaman saat terjadi error
        'module_error'=>'index/error', // Nama modul tujuan pengalihan jika terjadi error
        'module_forbidden'=>'index/signin', // Nama modul tujuan pengalihan saat terjadi error akses ditolak

        // Controller configurarion
        'main_module'=>'index', // Nama modul utama
        'modular_parent'=>null, // Nama kelas induk untuk modul arsitektur modular
        'module'=>null, // Nama modul yang sedang diakses
        'architecture'=>null, // Arsitektur yang akan digunakan

        // Router configuration
        'get_router'=>'r', // Nama key untuk format rute GET
        'route_format'=>'path', // Format rute yang akan digunakan
        'route_max_depth'=>20, // Maksimum kedalaman hirarki modul
        'route_params'=>array(), // Parameter yang tersedia untuk Route-Aliasing
        'router_action'=>null, // Aksi yang menjadi target untuk Route-Aliasing
        'ref_dir'=>null, // Lokasi folder untuk mengganti lokasi folder standar pada suatu kondisi tertentu
        'router'=>null, // Pengolah rute untuk Route-Aliasing
        'asset_url'=>null, // URL untuk aset
        'theme_url'=>null, // URL untuk tema

        // View configuration
        'theme'=>'default', // Nama tema yang sedang digunakan web
        'layout'=>'index', // Nama layout yang sedang digunakan web
        'json'=>false, // Menandai apakah output web menggunakan format JSON atau tidak
        'debug_json_mode'=>false, // Menandai apakah debug dilakukan menggunakan format JSON atau tidak
        'pager'=>null, // Data sementar untuk pager/pembagi halaman

        'connection'=>0 // Indeks koneksi database yang akan digunakan pada fitur multi-database
    );

    /**
     * Peta kelas yang akan menjadi anggota pustaka yang dapat dipanggil
     */
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
     * Menampung instance dari kelas
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

    /**
     * Konstruktor kelas
     */
    public function __construct() {
        if(isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['DOCUMENT_ROOT'])) {
            $dir = String::get()->unlastPart('/', $_SERVER['SCRIPT_NAME']);
            if($dir == '') {
                $dir = String::get()->unlastPart('\\', $_SERVER['SCRIPT_NAME']);
            }
            $this->data['dir'] = $this->realPath($_SERVER['DOCUMENT_ROOT'].$dir.DS);
        }
    }

    /**
     * Membaca nilai konfigurasi yang diletakkan di dalam berkas konfigurasi dan memasukkannya ke dalam sistem
     */
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

    /**
     * Mengambil data peta kelas
     * @return array
     */
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

    /**
     * Menetapkan konfigurasi secara langsung
     * @param mixed $data array Pasangan key dan value konfigurasi yang akan dimasukkan
     */
    public function setConfig($data)
    {
        $this->data = $data;
    }

    /**
     * Mengambil nilai konfigurasi berdasarkan key
     * @param mixed $data string Nama key konfigurasi yang akan diambil
     * @return int|string|float|object|array|bool
     */
    public function getData($data)
    {
        if(isset($this->data[$data])) {
            return $this->data[$data];
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
        $this->data[$data] = $value;
    }

}