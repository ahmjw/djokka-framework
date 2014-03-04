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
use Djokka\Model\TableCollection;

/**
 * Menjalankan CRUD pada database MySQL
 */
class Query
{
    /**
     * Koneksi database
     */
    protected $_connector;

    /**
     * Data perintah SQL
     */
    protected $_data = array(
        'select' => null,
        'from'   => null,
        'where'  => null,
        'group'  => null,
        'order'  => null,
        'limit'  => null,
        'insert' => null,
        'update' => null,
        'values' => null,
        'query'  => null
    );

    /**
     * Data pembagi halaman
     */
    protected $_pager = array();
    
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
        $this->_connector = Connection::getInstance();
    }

    /**
     * Mengambil hasil perintah SQL yang terbentuk
     */
    public function getQuery()
    {
        return $this->_data['query'];
    }

    /**
     * Membentuk perintah SQL SELECT
     * @param string $str Nama field yang akan disaring
     * @return object
     */
    public function select($str = '*') 
    {
        $this->_data['select'] = $str;
        $postpend = '';
        if ($this->_data['query'] !== null) {
            $postpend = $this->_data['query'];
        }
        $this->_data['query'] = 'SELECT ' . $str . $postpend;
        return $this;
    }

    /**
     * Membentuk perintah SQL FROM
     * @param string $str Nama tabel yang akan disaring
     * @return object
     */
    public function from($str) 
    {
        $this->_data['from'] = $str;
        $this->_data['query'] .= ' FROM '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL WHERE untuk penyaringan
     * @return object
     */
    public function where($params = array()) 
    {
        $sql = $this->_data['query'] . ' WHERE ';
        if(is_array($params)) {
            $criteria = $params[0];
            $criterias = array_slice($params, 1);
            $i = 0;
            $where = preg_replace_callback('/\?/i', function($matches) use($criterias, &$i) {
                $i++;
                return "'".addslashes($criterias[$i - 1])."'";
            }, $criteria);
            $sql .= $where;
            $this->_data['where'] = $where;
        } else {
            $sql .= $params;
            $this->_data['where'] = $params;
        }
        $this->_data['query'] = $sql;
        return $this;
    }

    /**
     * Membentuk perintah SQL ORDER BY
     * @param string $str Nama field yang akan diurutkan
     * @return object
     */
    public function order($str) 
    {
        $this->_data['order'] = $str;
        $this->_data['query'] .= ' ORDER BY '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL GROUP BY
     * @param string $str Nama field yang menjadi grup
     * @return object
     */
    public function group($str) 
    {
        $this->_data['group'] = $str;
        $this->_data['query'] .= ' GROUP BY '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL LIMIT
     * @param string $str Teks pembatas untuk membatasi data
     * @return object
     */
    public function limit($str) 
    {
        $this->_data['limit'] = $str;
        $this->_data['query'] .= ' LIMIT '.$str;
        return $this;
    }

    /**
     * Membentuk perintah SQL UPDATE
     * @param array $data Data yang menjadi masukan untuk mengubah data
     * @return object
     */
    public function update($data)
    {
        $sql = 'UPDATE '.$this->_data['from'].' SET ';
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
        if ($this->_data['where'] !== null) {
            $sql .= ' WHERE ' . $this->_data['where'];
        }
        $this->_data['query'] = $sql;
        return $this;
    }

    /**
     * Membentuk perintah SQL INSERT INTO
     * @return object
     */
    public function insert()
    {
        $sql = 'INSERT INTO '.$this->_data['from'].' ';
        switch (func_num_args()) {
            case 0:
                break;
            case 1:
                break;
            case 2:
                $sql .= '('.func_get_arg(0).') VALUES('.func_get_arg(1).')';
                break;
        }
        $this->_data['query'] = $sql;
        return $this;
    }

    /**
     * Membentuk perintah SQL DELETE untuk menghapus data
     * @return object
     */
    public function delete() 
    {
        $this->_data['query'] = 'DELETE';
        $this->_data['from'] = ' FROM '.$this->_data['from'];
        $this->_data['query'] .= $this->_data['from'];
        if (func_num_args() > 0) {
            $this->where(func_get_args());
        }
        return $this;
    }

    /**
     * Mengeksekusi perintah SQL
     * @return object
     */
    public function execute() 
    {
        if($resource = $this->_connector->query($this->_data['query'])) {
            $this->clearData();
        }
        return $resource;
    }

    protected function clearData()
    {
        foreach ($this->_data as $key => $value) {
            $this->_data[$key] = null;
        }
    }
}