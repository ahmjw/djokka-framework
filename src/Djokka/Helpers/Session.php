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
 * Kelas Djokka\Session adalah kelas pustaka framework. Dipergunakan untuk mengakses,
 * mengelola, dan memanipulasi data sesi pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Session
{

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
            session_start();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Fungsi yang dipanggil oleh kelas inti framework
     * @since 1.0.0
     * @param - Jika tidak memasukkan parameter, maka nilai baliknya adalah keseluruhan
     *   sesi web
     * - Jika memasukkan satu parameter, maka nilai baliknya adalah nilai sesi sesuai
     *   properti yang dimasukkan
     * - Jika memasukkan dua parameter, maka dia akan mengubah sesi berdasarkan properti
     *   dengan nilai yang dimasukkan
     * @return sesi web
     */
    public function getSession() {
        return $_SESSION['djokka'];
    }

    public function getData($data) {
        return $_SESSION['djokka'][$data];
    }

    public function setData($data, $value) {
        $_SESSION['djokka'][$data] = $value;
    }

    /**
     * Fungsi ini digunakan untuk menghilangkan sesi dari sistem
     * @since 1.0.0
     * @param $key adalah nama sesi yang tersimpan
     */
    public function clear($key)
    {
        unset($_SESSION['djokka'][$key]);
    }

    /**
     * Fungsi ini digunakan untuk mengecek apakah sesi sudah ada sebelumnya di dalam sistem
     * @since 1.0.0
     * @param $key adalah nama properti sesi yang tersimpan
     * @return boolean status ada atau tidaknya sesi
     */
    public function exists($key)
    {
        return isset($_SESSION['djokka'][$key]);
    }

}