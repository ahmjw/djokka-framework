<?php

namespace Djokka\Database\Drivers\MySql;

use Djokka\Db;
use Djokka\Database\Connection;

class Table
{
    /**
     * Menampung instance dari kelas
     * @since 1.0.3
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.3
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->_executor = Connection::getInstance();
    }

    /**
     * Mengambil skema suatu tabel model dari database
     * @since 1.0.1
     * @param $model adalah data model yang akan diambil skemanya
     * @return daftar skema tabel model dalam bentuk array
     */
    public function desc($tableName)
    {
        return $this->_executor->getArrays('DESC '.$tableName);
    }
}