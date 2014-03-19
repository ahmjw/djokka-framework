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

/**
 * Kelas pembantu yang bertugas mempermudah pengolahan data user pada wev
 */
class User
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
                return Session::getInstance()->getData('user');
            case 1:
                $data = new \stdClass();
                foreach (func_get_arg(0) as $key => $value) {
                    $data->{$key} = $value;
                }
                Session::getInstance()->setData('user', $data);
                break;
            case 2:
                $data = new \stdClass();
                foreach (func_get_arg(1) as $key => $value) {
                    $data->{$key} = $value;
                }
                Session::getInstance()->setData('user', array(func_get_arg(0)=>$data));
        }
    }
    
    /**
     * Mengosongkan atau menghapus data user web
     * @since 1.0.0
     */
    public function delete()
    {
        Session::getInstance()->delete('user');
    }

    /**
     * Mengambil data user
     * @return mixed
     */
    public function getUser() 
    {
        return Session::getInstance()->getData('user');
    }

    /**
     * Mengambil data user berdasarkan jenis/tipe
     * @param mixed $type string Jenis/type user yang akan diambil
     * @return mixed
     */
    public function getUserByType($type) 
    {
        $user = Session::getInstance()->getData('user');

        if (is_array($user) && isset($user[$type])) {
            if(is_array($user)) {
                return $user[$type];
            } else {
                return $user->{$type};
            }
        } else {
            return $user;
        }
    }

    public function isTypeExists($type)
    {
        $user = Session::getInstance()->getData('user');
        return is_array($user) && isset($user[$type]);
    }
}