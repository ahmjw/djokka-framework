<?php

/**
 * Mengolah data user pada web
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
 * Kelas pembantu yang bertugas mempermudah pengolahan data user pada wev
 */
class User extends Base
{

    /**
     * Menampung data user web
     * @since 1.0.0
     */
    private static $data;

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
            self::$_instance = new static();
        }
        return self::$_instance;
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

    /**
     * Mengambil data user
     * @return mixed
     */
    public function getUser() 
    {
        return $this->session('user');
    }

    /**
     * Mengambil data user berdasarkan jenis/tipe
     * @param mixed $type string Jenis/type user yang akan diambil
     * @return mixed
     */
    public function getUserByType($type) 
    {
        $user = $this->session('user');
        if(is_array($user)) {
            return $user[$type];
        } else {
            return $user->{$type};
        }
    }

}