<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.1
 */

namespace Djokka\Controller;

use Djokka\Route;
use Djokka\Controller as Core;
use Djokka\View\Asset;

/**
 * Kelas Djokka\Plugin adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.1
 */
class Plugin extends Core
{
    public $url;

    /**
     * Menampung instance dari kelas
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

    /**
     * Konstruktor kelas
     */
    public function __construct() {
        if(get_class($this) != __CLASS__) {
            $class = $this('String')->lastPart('\\', get_class($this));
            $path = $this->pluginDir().lcfirst(preg_replace('/([a-zA-Z0-9_]+)Plugin$/i', '$1', $class)).DS;
            $this->url = Route::get()->urlPath($path);
        }
    }

    /**
     * Menambahkan link file CSS atau Javascript hanya pada halaman yang sedang dibuka
     * @since 1.0.1
     * @param mixed $file string Alamat berkas CSS atau Javascript
     */
    public function asset($file) {
        Asset::get()->add($this->url.$file);
    }

}