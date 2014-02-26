<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.2
 */

namespace Djokka\Model;

/**
 * Kelas ini digunakan untuk membentuk koleksi skema/struktur tabel beserta aksesor modul untuk keperluan optimasi sistem
 * @since 1.0.2
 */
class SchemaCollection 
{
    /**
     * Nama modul yang sedang diakses
     */
    private $current_module;

    /**
     * Nama-nama module yang telah dimuat
     */
    private $modules = array();

    /**
     * Nama-nama model yang telah dimuat
     */
    private $models = array();

    /**
     * Menampung instance dari kelas
     * @since 1.0.2
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.2
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
     * Mengecek apakah modul telah dimuat atau belum
     * @param mixed $name string Nama modul
     * @return boolean
     */
    public function existsModule($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * Mengecek apakah model telah dimuat atau belum
     * @param mixed $name string Nama model
     * @return boolean
     */
    public function existsModel($name)
    {
        return isset($this->models[$name]);
    }

    /**
     * Menetapkan nama module yang sedang diakses
     * @param mixed $module string Nama modul
     */
    public function setCurrentModule($module)
    {
        $this->current_module = $module;
    }

    /**
     * Mengakses modul dari koleksi
     */
    public function module()
    {
        switch (func_num_args()) {
            case 0:
                return $this->modules;
            case 1:
                if (isset($this->modules[func_get_arg(0)])) {
                    return $this->modules[func_get_arg(0)];
                }
                break;
            case 2:
                $this->modules[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    /**
     * Mengakses model dari koleksi
     */
    public function models()
    {
        switch (func_num_args()) {
            case 0:
                return $this->models;
            case 1:
                return $this->models[func_get_arg(0)];
            case 2:
                $this->models[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

}