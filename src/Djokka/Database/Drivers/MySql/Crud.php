<?php

/**
 * Mengakses database MySQL untuk mengakses CRUD (Create Read Update Delete)
 * @since 1.0.3
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 */

namespace Djokka\Database\Drivers\MySql;

use Djokka\Database\ICrud;
use Djokka\Database\Connection;
use Djokka\Model\ModelCollection;

/**
 * Menjalankan CRUD pada database MySQL
 */
class Crud implements ICrud
{
    /**
     * Data perintah SQL
     */
    private $_sql = array(
        'select' => null,
        'from'   => null,
        'where'  => null,
        'group'  => null,
        'order'  => null,
        'limit'  => null,
        'insert' => null,
        'values' => null,
        'query'  => null
    );

    /**
     * Koneksi database
     */
    private $_connection;

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
        $this->_connection = Connection::getInstance();
    }

    /**
     * Membentuk perintah SQL SELECT
     * @param string $str Nama field yang akan disaring
     * @return object
     */
    public function select($str = '*') 
    {
        $this->_sql['select'] = $str;
        $this->_sql['query'] = 'SELECT '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL FROM
     * @param string $str Nama tabel yang akan disaring
     * @return object
     */
    public function from($str) 
    {
        $this->_sql['from'] = $str;
        $this->_sql['query'] .= ' FROM '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL WHERE untuk penyaringan
     * @return object
     */
    public function where($params = array()) 
    {
        $sql = $this->_sql['query'] . ' WHERE ';
        if(is_array($params)) {
            $criteria = $params[0];
            $criterias = array_slice($params, 1);
            $i = 0;
            $where = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                $i++;
                return "'".addslashes($criterias[$i-1])."'";
            }, $criteria);
            $sql .= $where;
            $this->_sql['where'] = $where;
        } else {
            $sql .= $params;
            $this->_sql['where'] = $params;
        }
        $this->_sql['query'] = $sql;
        return $this;
    }

    /**
     * Membentuk perintah SQL ORDER BY
     * @param string $str Nama field yang akan diurutkan
     * @return object
     */
    public function order($str) 
    {
        $this->_sql['order'] = $str;
        $this->_sql['query'] .= ' ORDER BY '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL GROUP BY
     * @param string $str Nama field yang menjadi grup
     * @return object
     */
    public function group($str) 
    {
        $this->_sql['group'] = $str;
        $this->_sql['query'] .= ' GROUP BY '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL LIMIT
     * @param string $str Teks pembatas untuk membatasi data
     * @return object
     */
    public function limit($str) 
    {
        $this->_sql['limit'] = $str;
        $this->_sql['query'] .= ' LIMIT '.$str;
        return $this;
    }

    /**
     * Mengambil jumlah data yang dihasilkan dari perintah SQL yang dijalankan
     * @return int
     */
    public function count() 
    {
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
    public function delete() 
    {
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
     * Mengeksekusi perintah SQL
     * @return object
     */
    public function execute() 
    {
        if($resource = $this->_connection->query($this->_sql['query'])) {
            foreach ($this as $key => $value) {
                $this->_sql[$key] = null;
            }
            return $resource;
        }
    }

    private function initSelection($tableName, $primary_key, $params)
    {
        $use_pk_opt = false;
        $num_params = count($params);

        switch ($num_params) {
            case 1:
                $field = is_array($params) && isset($params[1]['select']) ? $params[1]['select'] : '*';
                break;
            case 2:
                $use_pk_opt = $params[1];
                $params = $params[0];
                $field = $primary_key;

                if($field == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                break;
        }

        if($num_params > 0) {
            // Membentuk query SQL
            if(is_array($params[0])) {
                $db = $this->select(isset($params[0]['select']) ? $params[0]['select'] : '*')
                    ->from($tableName);
                if(isset($params[0]['where'])){
                    $this->where($params[0]['where']);
                }
                if(isset($params[0]['group'])){
                    $this->group($params[0]['group']);
                }
                if(isset($params[0]['order'])){
                    $this->order($params[0]['order']);
                }
            } else {
                $db = $this->select($field)
                    ->from($tableName);
                if($primary_key == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                $where = array($primary_key.'=?', $params[0]);
                $this->where($where);
            }
        } else {
            $this->select()->from($tableName);
        }
    }

    /**
     * Mengambil satu record/baris dari suatu tabel menggunakan model
     * @param string $tableName Nama tabel
     * @param string $primary_key Kunci utama tabel
     * @param array $params Parameter tambahan untuk penyaringan data
     * @since 1.0.3
     * @return object
     */
	public function find($tableName, $primary_key, $params)
	{
        $this->initSelection($tableName, $primary_key, $params);
        // Membaca record dari database
        $resource = $this->execute();
        if($row = $resource->fetch_assoc()) {
            return $row;
        }
	}

    /**
     * Mengambil nilai field dari perintah SQL yang menyaring satu field
     * @param string $tableName Nama tabel
     * @param string $primary_key Kunci utama tabel
     * @param array $params Parameter tambahan untuk penyaringan data
     * @since 1.0.3
     * @return mixed
     */
    public function findData($tableName, $primary_key, $params)
    {
        $this->initSelection($tableName, $primary_key, $params);
        // Membaca record dari database
        $resource = $this->execute();
        if($row = $resource->fetch_assoc()) {
            $record = clone $this;
            foreach ($row as $key => $value) {
                return stripslashes($value);
            }
        }
    }

	/**
     * Mengambil lebih dari satu record/baris dari suatu tabel menggunakan model
     * @param object $model Object model
     * @param array $params Parameter tambahan untuk mengatur data yang dihasilkan
     * @since 1.0.3
     * @return array
     */
    public function findAll($model, $params) 
    {
        $this->select(isset($params['select']) ? $params['select'] : '*');
        if(!isset($params['from'])){
        	$this->from($model->table());
        } else {
        	$this->from($params['from']);
        }
        if(isset($params['where'])){
            $this->where($params['where']);
        }
        if(isset($params['group'])){
            $this->group($params['group']);
        }
        if(isset($params['order'])){
            $this->order($params['order']);
        }
        // Mengaktifkan paginasi jika memungkinkan
        if(isset($params['limit'])){
            $this->initPager($params['limit']);
        }

        // Mengambil semua record dari database
        $collection = new ModelCollection($this->_sql['query'], $model);
        return $collection;
    }

    /**
     * Mengambil data pembagian halaman
     * @param object $model Object model
     * @return array
     */
    public function getPager($model)
    {
        $primary_key = $model->getPrimaryKey();
        if($primary_key !== null) {
            $field = $primary_key;
        } else {
            $fields = $model->schema('fields');
            $field = $fields[0];
        }

        $sql = "SELECT " . $field . " FROM " . $model->table();
        $resource = $this->_connection->query($sql);
        $total = $resource->num_rows;
        $num_page = ceil($total / $this->_pager['limit']);
        return array($this->_pager['page'], $num_page, $total);
    }

    private function initPager($data)
    {
        $page = 1;
        $offset = 0;

        if(is_array($data) && count($data) == 2) {
            list($limit, $page) = $data;
            $page = !empty($page) ? $page : 1;
        } else {
            if(is_numeric(strpos($data, ','))) {
                list($offset, $limit) = explode(',', $data, 2);
            } else {
                $limit = $data;
                $offset = 0;
            }
        }

        if($page > 1) {
            $offset = ($page - 1) * $limit;
        }

        $this->limit($offset.', '.$limit);
        $this->_pager = array(
            'limit'  => (int)$limit,
            'offset' => (int)$offset,
            'page'   => (int)$page
        );
    }
}