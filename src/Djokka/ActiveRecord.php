<?php

/**
 * Memproses model yang terdapat di dalam modul
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka;

use Djokka\Model\SchemaCollection;
use Djokka\Model\TableCollection;
use Djokka\Model\Validation;
use Djokka\Helpers\String;

/**
 * Kelas pustaka yang bertugas untuk memproses dan mengendalikan model yang terdapat di dalam suatu modul
 */
abstract class ActiveRecord extends Model
{
    /**
     * Fungsi yang digunakan untuk menetapkan nama tabel yang diwakili oleh model
     * @return string
     */
    abstract function table();

    /**
     * Data penting yang dibutuhkan oleh model
     */
    public $_dataset = array(
        'is_new'=>false,
        'module'=>null,
        'driver'=>null,
        'params'=>array(),
        'externals'=>array()
    );

    /**
     * Konstruktor kelas
     */
    public function __construct() {
        $this->preload();
    }

    private function getDriver($name)
    {
        $class = $this->_dataset['driver'] . '\\' . $name;
        return $class::getInstance();
    }

    /**
     * Memasukkan data model ke dalam pemetaan
     */
    private function preload()
    {
        $config = $this->config('db');
        $this->_dataset['driver'] = 'Djokka\\Database\\Drivers\\' . $config[0]['driver'];

        $this->schema('labels', $this->labels());
        if (!TableCollection::getInstance()->exists($this->table()))
        {
            $desc = $this->getDriver('Table')->desc($this->table());
            if ($desc !== null) {
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
                $this->schema($temp);
            }
        }
    }

    /**
     * Mengambil skema tabel yang digunakan oleh model
     * @param optional $use_module boolean menentukan apakah pencarian menggunakan module
     * @return array
     */
    public function schema()
    {
        $data = TableCollection::getInstance()->table($this->table());
        switch (func_num_args()) {
            case 0:
                return $data;
            case 1:
                if (!is_array(func_get_arg(0)) && isset($data[func_get_arg(0)])) {
                    return $data[func_get_arg(0)];
                } else {
                    TableCollection::getInstance()->table($this->table(), func_get_arg(0));
                }
                break;
            default:
                break;
        }
    }

    /**
     * Mengambil nama field yang menjadi primary key dari tabel yang diwakili oleh model
     * @return string
     */
    public function getPrimaryKey()
    {
        $pkey = $this->schema('primary_key');
        return $pkey !== null ? $pkey : $this->dataset('primary_key');
    }

    /**
     * Menetapkan nama field yang menjadi primary key dari tabel yang diwakili oleh model
     * @param mixed $key string Nama field
     */
    public function setPrimaryKey($key)
    {
        $this->dataset('primary_key', $key);
    }

    /**
     * Mengambil teks label suatu properti/atribut/field model
     * @since 1.0.0
     * @param $property adalah properti/atribut/field model yang akan diakses
     * @return nilai properti/atribut/field
     */
    public function label($property = null)
    {
        if ($this->schema() != null) {
            return isset($this->schema()->Labels[$property]) ? $this->schema()->Labels[$property] : ucfirst($property);
        } else {
            return ucfirst($property);
        }
    }

    /**
     * Melakukan operasi penyimpanan model (otomatis menentukan ditambah atau diubah)
     * @since 1.0.0
     * @param $availables adalah Daftar field yang akan disimpan datanya
     * @return objek resource hasil operasi penyimpanan model
     */
    public function save($availables = null)
    {
        return $this->isNew() ? $this->insert($availables) : $this->update($availables);
    }

    /**
     * Menambah data baru
     * @since 1.0.0
     * @param optional $availables array Nama-nama field yang akan digunakan
     * @return boolean
     */
    public function insert($availables = null)
    {
        if (!$this->isNew()) {
            throw new \Exception("This operation just active in new instance", 500);
        }
        if (!$this->validate()) {
            return false;
        }
        $into = $values = null;
        if ($availables === null) {
            $availables = array();
            $data = TableCollection::getInstance()->table($this->table());
            foreach ($data['fields'] as $field) {
                if ($data['describe'][$field]['Extra'] != 'auto_increment' && isset($this->{$field})) {
                    $availables[] = $field;
                }
            }
        }
        $i = 0;
        $count = count($availables) - 1;
        foreach ($availables as $field) {
            $into .= $field;
            $values .=  "'".Db::getInstance()->getConnection()->real_escape_string($this->{$field})."'";
            if ($i < $count) {
                $into .= ', ';
                $values .= ', ';
            }
            $i++;
        }
        if ($resource = $this->db()->insert($into, $values)->execute()) {
            $this->{$this->getPrimaryKey()} = Db::getInstance()->getConnection()->insert_id;
            return $resource;
        }
        return false;
    }

    /**
     * Mengubah data lama
     * @since 1.0.0
     * @param optional $availables array Nama-nama field yang akan digunakan
     * @return boolean
     */
    public function update($availables = null)
    {
        if (!$this->validate()) {
            return false;
        }
        if ($availables === null) {
            $data = TableCollection::getInstance()->table($this->table());
            $fields = $data['fields'];
            if ($fields == null) {
                throw new \Exception("No field in update list", 500);
            }
            $availables = array();
            foreach ($fields as $field) {
                if (isset($this->{$field})) {
                    $availables[] = $field;
                }
            }
        }

        $set = null;
        $count = count($availables) - 1;
        $i = 0;
        foreach ($availables as $field) {
            $set .= $field." = '".Db::getInstance()->getConnection()->real_escape_string($this->{$field})."'";
            if ($i < $count) {
                $set .= ', ';
            }
            $i++;
        }
        if ($resource = $this->db()->update($set)->execute()) {
            return $resource;
        }
        return false;
    }

    /**
     * Menghapus suatu objek record model
     * @since 1.0.0
     * @return objek resource hasil penghapusan record model
     */
    public function delete()
    {
        return $this->db()->query($this->db()->delete()->Query);
    }

    /**
     * Mengeksekusi perintah SQL dan mengikat hasilnya pada model
     * @since 1.0.0
     * @return object
     */
    public function query()
    {
        $args = func_get_args();
        $model = null;
        $args[0] = preg_replace_callback('/\{([a-zA-Z0-9_\/]*)\}/i', function($matches) use(&$model) {
            $model = $this->load(trim($matches[1]), false);
            return $model->table();
        }, $args[0]);
        $sql = Db::getInstance()->replaceWith($args);
        // Membaca record dari database
        $this->db()->Query = $sql;
        $resource = $this->db()->execute();
        if ($row = $resource->fetch_assoc()) {
            $record = clone $model;
            foreach ($row as $key => $value) {
                $record->{$key} = stripslashes($value);
            }
            $resource->free_result();
            return $record;
        }
    }

    /**
     * Mengambil jumlah data yang tertampung di dalam model
     * @param mixed $params Kondisi untuk melakukan filter
     * @since 1.0.3
     * @return int
     */
    public function count($condition = array())
    {
        return $this->getDriver('Crud')->count($this->table(), $condition);
    }

    /**
     * Mengambil nilai field dari perintah SQL yang menyaring satu field
     * @since 1.0.0
     * @return int|string|float
     */
    public function findData()
    {
        return $this->getDriver('Crud')->findData($this->table(), $this->getPrimaryKey(), func_get_args());
    }

    /**
     * Mengambil satu record/baris dari suatu tabel menggunakan model
     * @since 1.0.0
     * @return object
     */
    public function find()
    {
        $data = $this->getDriver('Crud')->find($this->table(), $this->getPrimaryKey(), func_get_args());
        if (!empty($data)) {
            $this->input($data);
            return $this;
        }
    }

    /**
     * Mengambil lebih dari satu record/baris dari suatu tabel menggunakan model
     * @param array $params Parameter tambahan untuk mengatur data yang dihasilkan
     * @since 1.0.0
     * @return array
     */
    public function findAll(array $params = array())
    {
        return $this->getDriver('Crud')->findAll($this, $params);
    }

    public function getPager()
    {
        return $this->getDriver('Crud')->getPager($this);
    }

    public function db($from = null)
    {
        $driver = $this->getDriver('Crud');
        if($from === null) {
            $driver->from($this->table());
        } else {
            $driver->from($from);
        }
        return $driver;
    }
}