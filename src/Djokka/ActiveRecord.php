<?php

/**
 * Memproses model yang terdapat di dalam modul
 * @since 1.0.3
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.3
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
        'is_new'     => false,
        'module'     => null,
        'driver'     => null,
        'condition'  => null,
    );

    /**
     * Konstruktor kelas
     */
    public function __construct()
    {
        $this->preload();
    }

    public function getDriver($name)
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
     * Mengecek status model, apakah menggunakan data lama atau data baru
     * @return bool
     */
    public function isNew()
    {
        return $this->_dataset['is_new'];
    }

    /**
     * Merubah status model menjadi model yang menggunakan data baru
     */
    public function setAsNew()
    {
        $this->_dataset['is_new'] = true;
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
     * @since 1.0.3
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
     * @since 1.0.3
     * @param $availables adalah Daftar field yang akan disimpan datanya
     * @return objek resource hasil operasi penyimpanan model
     */
    public function save(array $availables = array())
    {
        return $this->_dataset['is_new'] ? $this->insert($availables) : $this->update($availables);
    }

    /**
     * Menambah data baru
     * @since 1.0.3
     * @param optional $availables array Nama-nama field yang akan digunakan
     * @return boolean
     */
    public function insert(array $availables = array())
    {
        if (!$this->_dataset['is_new']) {
            throw new \Exception("This operation just active in new instance", 500);
        }
        if (!$this->validate()) {
            return false;
        }
        return $this->getDriver('Crud')->insertImpl($this, $availables);
    }

    /**
     * Mengubah data lama
     * @since 1.0.3
     * @param optional $availables array Nama-nama field yang akan digunakan
     * @return boolean
     */
    public function update(array $availables = array())
    {
        if (!$this->validate()) {
            return false;
        }
        return $this->getDriver('Crud')->updateImpl($this, $availables);
    }

    /**
     * Menghapus suatu objek record model
     * @since 1.0.3
     * @return objek resource hasil penghapusan record model
     */
    public function delete()
    {
        return $this->getDriver('Crud')->deleteImpl($this->table(), $this->dataset('condition'));
    }

    /**
     * Mengambil jumlah data yang tertampung di dalam model
     * @param mixed $params Kondisi untuk melakukan filter
     * @since 1.0.3
     * @return int
     */
    public function count()
    {
        return $this->getDriver('Crud')->countImpl($this->table(), $this->getPrimaryKey(), func_get_args());
    }

    /**
     * Mengambil nilai field dari perintah SQL yang menyaring satu field
     * @since 1.0.3
     * @return int|string|float
     */
    public function findData()
    {
        return $this->getDriver('Crud')->findDataImpl($this->table(), $this->getPrimaryKey(), func_get_args());
    }

    /**
     * Mengambil satu record/baris dari suatu tabel menggunakan model
     * @since 1.0.3
     * @return object
     */
    public function find()
    {
        return $this->getDriver('Crud')->findImpl($this, func_get_args());
    }

    /**
     * Mengambil lebih dari satu record/baris dari suatu tabel menggunakan model
     * @param array $params Parameter tambahan untuk mengatur data yang dihasilkan
     * @since 1.0.3
     * @return array
     */
    public function findAll(array $params = array())
    {
        return $this->getDriver('Crud')->findAllImpl($this, $params);
    }

    public function getPager()
    {
        return $this->getDriver('Crud')->getPagerImpl($this);
    }

    public function db($from = null)
    {
        $driver = $this->getDriver('Query');
        if($from === null) {
            $driver->from($this->table());
        } else {
            $driver->from($from);
        }
        if($this->_dataset['condition'] !== null) {
            $driver->where($this->_dataset['condition']);
        }
        return $driver;
    }
}