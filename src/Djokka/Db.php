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

namespace Djokka;

/**
 * Kelas pustaka yang bertugas menyediakan akses database
 */
class Db
{
    use TShortcut;

    /**
     * Perintah SQL yang dihasilkan
     */
    public $Query;

    /**
     * Pembentuk perintah SQL SELECT
     */
    public $Select;

    /**
     * Pembentuk perintah SQL FROM
     */
    public $From;

    /**
     * Pembentuk perintah SQL WHERE
     */
    public $Where;

    /**
     * Pembentuk perintah SQL GROUP BY
     */
    public $Group;

    /**
     * Pembentuk perintah SQL ORDER BY
     */
    public $Order;

    /**
     * Pembentuk perintah SQL LIMIT
     */
    public $Limit;

    /**
     * Pembentuk perintah SQL INSERT INTO
     */
    public $Insert;

    /**
     * Pembentuk perintah SQL VALUES
     */
    public $Values;

    /**
     * Koneksi database yang digunakan
     */
    private $connection;

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
        return $this->getArrays('DESC '.$this->From);
    }

    /**
     * Membentuk perintah SQL UPDATE
     * @param array $data Data yang menjadi masukan untuk mengubah data
     * @return object
     */
    public function update($data) {
        $sql = "UPDATE {$this->From} SET ";
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
        if(isset($this->Where)) {
            $sql .= $this->where($this->Where)->Query;
        }
        $this->Query = $sql;
        return $this;
    }

    /**
     * Membentuk perintah SQL INSERT INTO
     * @return object
     */
    public function insert() {
        $sql = "INSERT INTO {$this->From} ";
        switch (func_num_args()) {
            case 0:
                break;
            case 1:
                break;
            case 2:
                $sql .= '('.func_get_arg(0).') VALUES('.func_get_arg(1).')';
                break;
        }
        $this->Query = $sql;
        return $this;
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
        $connection = $this->connection;
        return preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
            $i++;
            return "'".$connection->real_escape_string($criterias[$i-1])."'";
        }, $criteria);
    }

    /**
     * Membentuk perintah SQL WHERE untuk penyaringan
     * @return object
     */
    public function where() {
        $sql = $this->Query.' WHERE ';
        switch (func_num_args()) {
            case 1:
                $arg = func_get_arg(0);
                if(is_array($arg)) {
                    $criteria = $arg[0];
                    $criterias = array_slice($arg, 1);
                    $i = 0;
                    $connection = $this->connection;
                    $where = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                        $i++;
                        return "'".addslashes($criterias[$i-1])."'";
                    }, $criteria);
                    $sql .= $where;
                    $this->Where = $where;
                } else {
                    $sql .= $arg;
                    $this->Where = $arg;
                }
                break;
            default:
                $args = func_get_args();
                $criteria = $args[0];
                $criterias = array_slice($args, 1);
                $i = 0;
                $connection = $this->connection;
                $sql .= preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                    $i++;
                    return "'".addslashes($criterias[$i-1])."'";
                }, $criteria);
                $this->Where = $criteria;
                break;
        }
        $this->Query = $sql;
        return $this;
    }

    /**
     * Mengambil jumlah data yang dihasilkan dari perintah SQL yang dijalankan
     * @return int
     */
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

    /**
     * Membentuk perintah SQL DELETE untuk menghapus data
     * @return object
     */
    public function delete() {
        $this->Query = 'DELETE ';
        $this->From = ' FROM '.$this->From;
        $this->Query .= $this->From;
        $sql = '';
        if(func_num_args() > 0) {
            $args = func_get_args();
            $sql .= ' WHERE ';
            $criteria = $args[0];
            $criterias = array_slice($args, 1);
            $i = 0;
            $connection = $this->connection;
            $sql .= preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                $i++;
                return "'".addslashes($criterias[$i-1])."'";
            }, $criteria);
            $this->Query .= $sql;
            $this->Where = $args;
        } else {
            $this->Query = $this->where($this->Where)->Query;
        }
        return $this;
    }

    /**
     * Membentuk perintah SQL SELECT
     * @param string $str Nama field yang akan disaring
     * @return object
     */
    public function select($str = '*') {
        $this->Select = $str;
        $this->Query = 'SELECT '.$str;
        $this->From = ' FROM '.$this->From;
        $this->Query .= $this->From;
        return $this;
    }

    /**
     * Membentuk perintah SQL FROM
     * @param string $str Nama tabel yang akan disaring
     * @return object
     */
    public function from($str) {
        $this->From = $str;
        $this->Query .= ' FROM '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL GROUP BY
     * @param string $str Nama field yang menjadi grup
     * @return object
     */
    public function group($str) {
        $this->Group = $str;
        $this->Query .= ' GROUP BY '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL ORDER BY
     * @param string $str Nama field yang akan diurutkan
     * @return object
     */
    public function order($str) {
        $this->Order = $str;
        $this->Query .= ' ORDER BY '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL LIMIT
     * @param string $str Teks pembatas untuk membatasi data
     * @return object
     */
    public function limit($str) {
        $this->Limit = $str;
        $this->Query .= ' LIMIT '.$str;
        return $this;
    }

    /**
     * Mengeksekusi perintah SQL
     * @return object
     */
    public function execute() {

        if($resource = $this->query($this->Query)) {
            foreach ($this as $key => $value) {
                if($key == 'connection') continue;
                $this->{$key} = null;
            }
            return $resource;
        }
    }

    /**
     * Mengosongkan properti (salah)
     * @deprecated
     */
    public function getProperty()
    {
        foreach ($this as $key => $value) {
            if($key == 'connection') continue;
            $this->{$key} = null;
        }
    }

    /**
     * Membuka koneksi database
     * @since 1.0.0
     */
    public function connect()
    {
        $dbs = $this->config('db');
        $config = $dbs[$this->config('connection')];
        if($config == null) {
            throw new \Exception("No database configuration", 500);
        }
        switch ($config['driver']) {
            case 'MySql':
                @$this->connection = new \Mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);
                if($this->connection->connect_error) {
                    throw new \Exception($this->connection->connect_error, 500);
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
        if(!isset($this->connection)) {
            $this->connect();
        }
        if(is_array($sql)) {
            $criterias = array_slice($sql, 1);
            $i = 0;
            $connection = $this->connection;
            $sql = preg_replace_callback('/\?/i', function($matches) use($connection, $criterias, &$i) {
                $i++;
                return "'".$connection->real_escape_string($criterias[$i-1])."'";
            }, $sql);
            $sql = $sql[0];
        }
        if($this->connection && !$this->connection->connect_error) {
            $resource = $this->connection->query($sql);
            if($this->connection->error) {
                throw new \Exception($this->connection->error . ' -> ' . $sql, 500);
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
            $connection = $this->connection;
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
                $connection = $this->connection;
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
                $connection = $this->connection;
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
                $connection = $this->connection;
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
                $connection = $this->connection;
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