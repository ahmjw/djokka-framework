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
use Djokka\Model\ModelCollection;
use Djokka\Model\TableCollection;

/**
 * Menjalankan CRUD pada database MySQL
 */
class Crud extends Query implements ICrud
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

    public function insertImpl($model, array $availables = array())
    {
        $into = $values = null;
        if (empty($availables)) {
            $availables = array();
            $data = TableCollection::getInstance()->table($model->table());
            foreach ($data['fields'] as $field) {
                if ($data['describe'][$field]['Extra'] != 'auto_increment' && isset($model->{$field})) {
                    $availables[] = $field;
                }
            }
        }
        $i = 0;
        $count = count($availables) - 1;
        foreach ($availables as $field) {
            $into .= $field;
            $values .=  "'".addslashes($model->{$field})."'";
            if ($i < $count) {
                $into .= ', ';
                $values .= ', ';
            }
            $i++;
        }

        $this->from($model->table());
        $this->insert($into, $values);
        if ($resource = $this->execute()) {
            $pkey = $model->getPrimaryKey();
            $model->{$pkey} = $this->_connector->getConnection()->insert_id;
            $model->dataset('condition', array($pkey . ' =?', $model->{$pkey}));
            return $resource;
        }
        return false;
    }

    public function updateImpl($model, array $availables = array())
    {
        if (empty($availables)) {
            $data = TableCollection::getInstance()->table($model->table());
            $fields = $data['fields'];
            if ($fields == null) {
                throw new \Exception("No field in update list", 500);
            }
            $availables = array();
            foreach ($fields as $field) {
                if (isset($model->{$field})) {
                    $availables[] = $field;
                }
            }
        }

        $set = null;
        $count = count($availables) - 1;
        $i = 0;

        foreach ($availables as $field) {
            $set .= $field." = '".addslashes($model->{$field})."'";
            if ($i < $count) {
                $set .= ', ';
            }
            $i++;
        }
        $this->from($model->table());
        $this->update($set);
        if ($model->dataset('condition') !== null) {
            $this->where($model->dataset('condition'));
        }
        if ($resource = $this->execute()) {
            return $resource;
        }
        return false;
    }

    /**
     * Mengambil jumlah data yang dihasilkan dari perintah SQL yang dijalankan
     * @return int
     */
    public function countImpl($tableName, $primary_key, $params) 
    {
        $this->from($tableName);
        switch (count($params)) {
            case 0:
                $this->select('COUNT('.$primary_key.') AS count');
                break;
            case 1:
                $this->select('COUNT('.$primary_key.') AS count');
                $this->where($params[0]);
                break;
        }
        return $this->execute()->fetch_object()->count;
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
                $this->select(isset($params[0]['select']) ? $params[0]['select'] : '*')
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
                $this->select($field)
                    ->from($tableName);
                if($primary_key == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                $where = array($primary_key.'=?', !$use_pk_opt ? $params[0] : $params);
                $this->where($where);
            }
        } else {
            $this->select()->from($tableName);
        }
    }

    public function deleteImpl($tableName, $condition)
    {
        $this->from($tableName);
        $this->_data['query'] = 'DELETE' . $this->_data['query'];
        $this->where($condition);
        return $this->execute();
    }

    /**
     * Mengambil satu record/baris dari suatu tabel menggunakan model
     * @param string $tableName Nama tabel
     * @param string $primary_key Kunci utama tabel
     * @param array $params Parameter tambahan untuk penyaringan data
     * @since 1.0.3
     * @return object
     */
	public function findImpl($model, $params)
	{
        $this->initSelection($model->table(), $model->getPrimaryKey(), $params);
        $model->dataset('condition', $this->_data['where']);
        // Membaca record dari database
        $resource = $this->execute();
        if($row = $resource->fetch_assoc()) {
            $model->input($row);
            return $model;
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
    public function findDataImpl($tableName, $primary_key, $params)
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
    public function findAllImpl($model, $params) 
    {
        $this->_data['query'] = null;
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
        $collection = new ModelCollection($this->_data['query'], $model);
        $model->dataset('condition', $this->_data['where']);
        $model->dataset('from', $this->_data['from']);
        $this->clearData();
        return $collection;
    }

    /**
     * Mengambil data pembagian halaman
     * @param object $model Object model
     * @return array
     */
    public function getPagerImpl($model)
    {
        $primary_key = $model->getPrimaryKey();
        if($primary_key !== null) {
            $field = $primary_key;
        } else {
            $fields = $model->schema('fields');
            $field = $fields[0];
        }
        if ($model->dataset('from') !== null) {
            $this->from($model->dataset('from'));
            $this->select('0');
        } else {
            $this->select($field);
            $this->from($model->table());
        }
        if ($model->dataset('condition') !== null) {
            $this->where($model->dataset('condition'));
        }
        $resource = $this->execute();
        $total = $resource->num_rows;
        if (isset($this->_pager['limit'])) {
            $num_page = ceil($total / $this->_pager['limit']);
            $page = $this->_pager['page'];
        } else {
            $num_page = 0;
            $page = 1;
        }
        return array($page, $num_page, $total);
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