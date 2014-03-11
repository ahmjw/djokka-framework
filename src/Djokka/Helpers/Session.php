<?php

/**
 * Mengelola data session pada web
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Helpers;

/**
 * Kelas pembantu yang bertugas untuk mempermudah pengolahan data session pada web
 */
class Session
{

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$_instance == null) {
            session_start();
            self::$_instance = new static();
        }
        return self::$_instance;
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
    public function getSession() 
    {
        return $_SESSION['djokka'];
    }

    /**
     * Mengambil data session berdasarkan nama key
     * @param mixed $data string Nama key session
     * @return alltypes
     */
    public function getData($data) 
    {
        return isset($_SESSION['djokka'][$data]) ? $_SESSION['djokka'][$data] : null;
    }

    /**
     * Menetapkan data session berdasarkan nama key
     * @param mixed $data string Nama key session
     * @param mixed $value alltypes Nilai yang akan diberikan pada session sesuai nama key
     */
    public function setData($data, $value) 
    {
        $_SESSION['djokka'][$data] = $value;
    }

    /**
     * Fungsi ini digunakan untuk menghilangkan sesi dari sistem
     * @since 1.0.0
     */
    public function delete()
    {
        $params = func_get_arg(0);
        if (!is_array($params)) {
            unset($_SESSION['djokka'][$params]);
        } else {
            foreach ($params as $key) {
                unset($_SESSION['djokka'][$key]);
            }
        }
    }

    /**
     * Fungsi ini digunakan untuk mengecek apakah sesi sudah ada sebelumnya di dalam sistem
     * @since 1.0.0
     * @param $key adalah nama properti sesi yang tersimpan
     * @return boolean status ada atau tidaknya sesi
     */
    public function isExists($key)
    {
        return isset($_SESSION['djokka'][$key]);
    }
}