<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka;

/**
 * Kelas Djokka\Db adalah kelas pustaka framework. Dipergunakan untuk keperluan akses
 * database
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Db extends \Djokka
{
    public $query;
    public $select;
    public $from;
    public $where;
    public $group;
    public $order;
    public $limit;
    public $insert;
    public $values;
    private $connection;

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

    public function call() {
        if(func_num_args() == 1) {
            $this->from = func_get_arg(0);
        }
        return $this;
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Mengambil skema suatu tabel model dari database
     * @since 1.0.1
     * @param $model adalah data model yang akan diambil skemanya
     * @return daftar skema tabel model dalam bentuk array
     */
    public function desc()
    {
        return $this->getArrays('DESC '.$this->from);
    }

    public function update($data) {
        $sql = "UPDATE {$this->from} SET ";
        if(is_array($data)) {
            $count = count($data)-1;
            $i = 0;
            foreach ($data as $key => $value) {
                $sql .= "$key = '".addslashes($value)."'";
                if($i < $count) {
                    $sql .= ', ';
                }
                $i++;
            }
        } else {
            $sql .= $data;
        }
        if(isset($this->where)) {
            $sql .= $this->where($this->where)->query;
        }
        $this->query = $sql;
        return $this;
    }

    public function insert() {
        $sql = "INSERT INTO {$this->from} ";
        switch (func_num_args()) {
            case 0:
                break;
            case 1:
                break;
            case 2:
                $sql .= '('.func_get_arg(0).') VALUES('.func_get_arg(1).')';
                break;
        }
        $this->query = $sql;
        return $this;
    }

    public function where() {
        $sql = $this->query.' WHERE ';
        switch (func_num_args()) {
            case 1:
                $arg = func_get_arg(0);
                if(is_array($arg)) {
                    $criteria = $arg[0];
                    $criterias = array_slice($arg, 1);
                    $i = 0;
                    $where = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                        $i++;
                        return "'".addslashes($criterias[$i-1])."'";
                    }, $criteria);
                    $sql .= $where;
                    $this->where = $where;
                } else {
                    $sql .= $arg;
                    $this->where = $arg;
                }
                break;
            default:
                $args = func_get_args();
                $criteria = $args[0];
                $criterias = array_slice($args, 1);
                $i = 0;
                $sql .= preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $criteria);
                $this->where = $criteria;
                break;
        }
        $this->query = $sql;
        return $this;
    }

    public function count() {
        switch (func_num_args()) {
            case 0:
                $this->select('COUNT(*) AS count');
                return $this->execute()
                    ->fetch_object()->count;
            case 1:
                $this->select('COUNT(*) AS count');
                return $this->where(func_get_arg(0))
                    ->execute()
                    ->fetch_object()->count;
        }
    }

    public function delete() {
        $this->select = $str;
        $this->query = 'DELETE '.$str;
        $this->from = ' FROM '.$this->from;
        $this->query .= $this->from;
        if(func_num_args() > 0) {
            $args = func_get_args();
            $sql .= ' WHERE ';
            $criteria = $args[0];
            $criterias = array_slice($args, 1);
            $i = 0;
            $sql .= preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                $i++;
                return "'".addslashes($criterias[$i-1])."'";
            }, $criteria);
            $this->query .= $sql;
            $this->where = $args;
        }
        return $this;
    }

    public function select($str = '*') {
        $this->select = $str;
        $this->query = 'SELECT '.$str;
        $this->from = ' FROM '.$this->from;
        $this->query .= $this->from;
        return $this;
    }

    public function from($str) {
        $this->from = $str;
        $this->query .= ' FROM '.$str;
        return $this;
    }

    public function group($str) {
        $this->group = $str;
        $this->query .= ' GROUP BY '.$str;
        return $this;
    }

    public function order($str) {
        $this->order = $str;
        $this->query .= ' ORDER BY '.$str;
        return $this;
    }

    public function limit($str) {
        $this->limit = $str;
        $this->query .= ' LIMIT '.$str;
        return $this;
    }

    public function execute() {
        if($resource = $this->query($this->query)) {
            foreach ($this as $key => $value) {
                if($key == 'connection') continue;
                $this->{$key} = null;
            }
            return $resource;
        }
    }

    /**
     * Membuka koneksi database
     * @since 1.0.0
     */
    public function connect()
    {
        $config = $this->config('db');
        if($config == null) {
            throw new \Exception("No database configuration", 500);
        }
        @$this->connection = new \Mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);
        if($this->connection->connect_error) {
            throw new \Exception($this->connection->connect_error, 500);
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
        if(!isset($this->connection)) {
            $this->connect();
        }
        if(is_array($sql)) {
            $criterias = array_slice($sql, 1);
            $i = 0;
            $sql = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                $i++;
                return "'".$this->connection->real_escape_string($criterias[$i-1])."'";
            }, $sql);
            $sql = $sql[0];
        }
        if($this->connection) {
            $resource = $this->connection->query($sql);
            if($this->connection->error) {
                throw new \Exception($this->connection->error, 500);
            }
            return $resource;
        }
    }

    /**
     * Mengambil lebih dari satu record dalam bentuk array
     * @since 1.0.0
     * @param $sql adalah query SQL yang akan dieksekusi
     * @return array record
     */
    public function getArrays($sql = null, $use_array = false)
    {
        if($sql != null) {
            if(is_array($sql)) {
                $criterias = array_slice($sql, 1);
                $i = 0;
                $sql = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                    $i++;
                    return "'".$this->connection->real_escape_string($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->query;
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
                $sql = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->query;
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
                $sql = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->query;
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
                $sql = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $sql);
                $sql = $sql[0];
            }
        } else {
            $sql = $this->query;
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

    public function getTableSchema($table)
    {
        $this->from = $table;
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
            $temp['primary'] = $pkey;
            return $temp;
        }
    }

}