<?php

/**
 * Menyediakan akses database
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka\Database;

use Djokka\Helpers\Config;

/**
 * Kelas pustaka yang bertugas menyediakan akses database
 */
class Connection
{
    //use TShortcut;

    /**
     * Koneksi database yang digunakan
     */
    private $_connection;

    /**
     * @var Menampung instance dari kelas
     * @access private
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
     * Menginisialisasi data
     * @return object {@link Djokka\Db}
     */
    public function call() {
        if(func_num_args() == 1) {
            $this->From = func_get_arg(0);
        }
        return $this;
    }

    /**
     * Mengambil koneksi yang digunakan database
     * @return object
     */
    public function getConnection() {
        return $this->_connection;
    }

    /**
     * Mengamankan dari SQL injection menggunakan penyimbolan parameter
     * @param array $params Parameter yang akan digunakan untuk mengganti simbol
     * @return string
     */
    public function replaceWith($params = array())
    {
        $criteria = $params[0];
        $criterias = array_slice($params, 1);
        $i = 0;
        $connection = $this->_connection;
        return preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
            $i++;
            return "'".$connection->real_escape_string($criterias[$i-1])."'";
        }, $criteria);
    }

    /**
     * Membuka koneksi database
     * @since 1.0.0
     */
    public function connect()
    {
        $dbs = Config::getInstance()->getData('db');
        $config = $dbs[Config::getInstance()->getData('connection')];
        if($config == null) {
            throw new \Exception("No database configuration", 500);
        }
        switch ($config['driver']) {
            case 'MySql':
                @$this->_connection = new \Mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);
                if($this->_connection->connect_error) {
                    throw new \Exception($this->_connection->connect_error, 500);
                }
                break;
            case 'PostgreSql':
                $pdo = new \PDO('pgsql:dbname='.$config['database'].';host='.$config['hostname'].';user='.$config['username'].';password='.$config['password']);
                break;
            case 'Odbc':
                $pdo = new \PDO('odbc:datasource='.$config['datasource']);
                break;
        }
    }

    /**
     * Mengeksekusi query SQL
     * @since 1.0.0
     * @param $sql adalah query SQL yang akan dieksekusi
     * @return objek resource hasil eksekusi
     */
    public function query($sql)
    {
        if(!is_string($sql) && !is_array($sql)) {
            throw new \Exception("Query just allow string or array data type", 500);
        }
        if(!isset($this->_connection)) {
            $this->connect();
        }
        if(is_array($sql)) {
            $criterias = array_slice($sql, 1);
            $i = 0;
            $connection = $this->_connection;
            $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                $i++;
                return "'".$connection->real_escape_string($criterias[$i-1])."'";
            }, $sql);
            $sql = $sql[0];
        }
        if($this->_connection && !$this->_connection->connect_error) {
            $resource = $this->_connection->query($sql);
            if($this->_connection->error) {
                throw new \Exception($this->_connection->error . ' -> ' . $sql, 500);
            }
            return $resource;
        }
    }

    /**
     * Mengambil nilai field pada perintah SQL yang menyaring satu field
     * @param string $sql Perintah SQL
     * @return string|int|float
     */
    public function getData($sql) {
        if(is_array($sql)) {
            $criterias = array_slice($sql, 1);
            $i = 0;
            $connection = $this->_connection;
            $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                $i++;
                return "'".$connection->real_escape_string($criterias[$i-1])."'";
            }, $sql);
            $sql = $sql[0];
        }
        if($resource = $this->query($sql)) {
            $data = $resource->fetch_array();
            return $data[0];
        }
    }

    /**
     * Mengambil lebih dari satu record dalam bentuk array
     * @since 1.0.0
     * @param string $sql Perintah SQL yang akan dieksekusi
     * @param bool $use_array Menentukan data diambil sebagai array atau asosiasi saja
     * @return array record
     */
    public function getArrays($sql = null, $use_array = false)
    {
        if($sql != null) {
            if(is_array($sql)) {
                $criterias = array_slice($sql, 1);
                $i = 0;
                $connection = $this->_connection;
                $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                    $i++;
                    return "'".$connection->real_escape_string($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->Query;
        }
        if($resource = $this->query($sql)) {
            $rows = array();
            if(!$use_array) {
                while ($row = $resource->fetch_assoc()) {
                    foreach ($row as $key => $value) {
                        $row[$key] = stripslashes($value);
                    }
                    $rows[] = $row;
                }
            } else {
                while ($row = $resource->fetch_array()) {
                    foreach ($row as $key => $value) {
                        $row[$key] = stripslashes($value);
                    }
                    $rows[] = $row;
                }
            }
            $resource->free_result();
            return $rows;
        }
    }

    /**
     * Mengambil satu record dalam bentuk array
     * @since 1.0.0
     * @param $sql adalah nama kelas (opsional)
     * @return array record
     */
    public function getArray($sql = null)
    {
        if($sql != null) {
            if(is_array($sql)) {
                $criterias = array_slice($sql, 1);
                $i = 0;
                $connection = $this->_connection;
                $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->Query;
        }
        if($resource = $this->query($sql)) {
            $row = $resource->fetch_assoc();
            if($row != null) {
                foreach ($row as $key => $value) {
                    $row[$key] = stripslashes($value);
                }
            }
            $resource->free_result();
            return $row;
        }
    }

    /**
     * Mengambil lebih dari satu record dalam bentuk objek
     * @since 1.0.0
     * @param $sql adalah query SQL yang akan dieksekusi
     * @return objek record
     */
    public function getObjects($sql = null)
    {
        if($sql != null) {
            if(is_array($sql)) {
                $criterias = array_slice($sql, 1);
                $i = 0;
                $connection = $this->_connection;
                $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->Query;
        }
        if($resource = $this->query($sql)) {
            $rows = array();
            while ($row = $resource->fetch_object()) {
                foreach ($row as $key => $value) {
                    $row->{$key} = stripslashes($value);
                }
                $rows[] = $row;
            }
            $resource->free_result();
            return $rows;
        }
    }

    /**
     * Mengambil satu record dalam bentuk objek
     * @since 1.0.0
     * @param $sql adalah query SQL yang akan dieksekusi
     * @return objek record
     */
    public function getObject($sql = null)
    {
        if($sql != null) {
            if(is_array($sql)) {
                $criterias = array_slice($sql, 1);
                $i = 0;
                $connection = $this->_connection;
                $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->Query;
        }
        if($resource = $this->query($sql)) {
            $row = $resource->fetch_object();
            if($row != null) {
                foreach ($row as $key => $value) {
                    $row->{$key} = stripslashes($value);
                }
            }
            $resource->free_result();
            return $row;
        }
    }

    /**
     * Mengambil skema/struktur tabel
     * @param mixed $table string Nama tabel yang akan diambil
     * @return array
     */
    public function getTableSchema($table)
    {
        $this->From = $table;
        if($desc = $this->desc()) {
            $pkey = null;
            $temp = array();
            foreach ($desc as $schema) {
                $field = null;
                $info = array();
                foreach ($schema as $key => $value) {
                    if($key == 'Field') {
                        $field = $value;
                    } else {
                        $info[$key] = $value;
                        if($key == 'Key' && $value == 'PRI') {
                            $pkey = $field;
                        }
                    }
                }
                $fields[] = $field;
                $temp['schema'][$field] = $info;
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
        $items = $this->getArrays('SHOW TABLES', true);
        if(count($items) > 0) {
            $tables = array();
            foreach ($items as $item) {
                $tables[] = $item[0];
            }
            return $tables;
        }
    }

}