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
 * Kelas Djokka\User adalah kelas pustaka framework. Dipergunakan untuk mengakses dan
 * mengelola data user web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class User extends \Djokka
{

    /**
     * @var Menampung data user web
     * @access private
     * @since 1.0.0
     */
    private static $data;

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

    /**
     * Menentukan/set data user web
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka nilai baliknya adalah data user web
     * - Jika memasukkan satu parameter, maka dia akan melakukan set data user web
     * - Jika memasukkan dua parameter, maka dia akan melakukan set data user disertai
     *   dengan penyaringan tingkatan user web
     * @return data user web atau void
     */
    public function setData()
    {
        switch (func_num_args()) {
            case 0:
                return $this->session('user');
            case 1:
                $data = func_get_arg(0);
                if(is_object($data)){
                    $tmp = $data;
                    $data = new \stdClass();
                    foreach ($tmp as $key => $value) {
                        $data->{$key} = $value;
                    }
                }
                $this->session('user', $data);
                break;
            case 2:
                $type = func_get_arg(0);
                $data = func_get_arg(1);
                if(is_object($data)) {
                    $tmp = $data;
                    $data = new \stdClass();
                    foreach ($tmp as $key => $value) {
                        $data->{$key} = $value;
                    }
                }
                $this->session('user', array($type=>$data));
        }
    }
    
    /**
     * Mengosongkan atau menghapus data user web
     * @since 1.0.0
     */
    public function clear()
    {
        Session::get()->clear('user');
    }

    public function getUser() {
        return $this->session('user');
    }

    public function getUserByType($type) {
        $user = $this->session('user');
        if(is_array($user)) {
            return $user[$type];
        } else {
            return $user->{$type};
        }
    }

}