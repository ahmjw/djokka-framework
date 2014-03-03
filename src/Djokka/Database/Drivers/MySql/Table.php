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

    private function getData($sql, $is_numeric_key = false)
    {
        if($resource = $this->_connector->query($sql)) {
            $rows = array();
            while ($row = $resource->fetch_array()) {
                $rows[] = $row;
            }
            $resource->free_result();
            return $rows;
        }
    }

    public function __construct()
    {
        $this->_connector = Connection::getInstance();
    }

    /**
     * Mengambil skema suatu tabel model dari database
     * @since 1.0.1
     * @param $model adalah data model yang akan diambil skemanya
     * @return daftar skema tabel model dalam bentuk array
     */
    public function desc($tableName)
    {
        return $this->getData('DESC '.$tableName);
    }

    /**
     * Mengambil skema/struktur tabel
     * @param mixed $table string Nama tabel yang akan diambil
     * @return array
     */
    public function getSchema($table)
    {
        $this->From = $table;
        if($desc = $this->desc($table)) {
            $pkey = null;
            $temp = array();
            foreach ($desc as $schema) {
                $field = null;
                $info = array();
                foreach ($schema as $key => $value) {
                    if ($key == 'Field') {
                        $field = $value;
                    } else {
                        $info[$key] = $value;
                        if ($key == 'Key' && $value == 'PRI') {
                            $pkey = $field;
                        }
                    }
                }
                $fields[] = $field;
                $temp['describe'][$field] = $info;
            }
            $temp['fields'] = $fields;
            $temp['primary_key'] = $pkey;
            return $temp;
        }
    }

    /**
     * Mengambil daftar tabel yang terdapat di dalam database
     * @return array
     */
    public function getTables()
    {
        $data = $this->getData('SHOW TABLES', true);
        $tables = array();
        foreach ($data as $key => $item) {
            $tables[] = $item[0];
        }
        return $tables;
    }
}