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

use Djokka\Base;
use Djokka\Model\SchemaCollection;
use Djokka\Model\TableCollection;
use Djokka\Model\ModelCollection;
use Djokka\Model\Validation;
use Djokka\Helpers\String;

/**
 * Kelas pustaka yang bertugas untuk memproses dan mengendalikan model yang terdapat di dalam suatu modul
 */
class Model extends Base
{
    /**
     * @deprecated
     */
    const FIND = 1;

    /**
     * @deprecated
     */
    const FIND_ALL = 2;

    /**
     * @deprecated
     */
    const SCALAR = 3;

    /**
     * Instance dari kelas ini
     */
    private static $instance;

    /**
     * Data penting yang dibutuhkan oleh model
     */
    protected $____dataset = array(
        'is_new'=>true,
        'module'=>null,
        'params'=>array(),
        'externals'=>array(),
        'updates'=>array()
    );

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

    /**
     * Mengambil instance suatu model dari peta model
     * @param mixed $class Nama kelas model
     * @param mixed $module Nama modul tempat meletakkan model tersebut
     * @param optional $is_new apakah model akan dimuat sebagai data baru atau data lama
     * @return object
     */
    private static function getObject($class, $module, $is_new = true)
    {
        $schema = SchemaCollection::get();
        if(!$schema->existsModel($class)) {
            $object = new $class;
            $object->____dataset['module'] = $module;
            $schema->models($module, $object);
            
            if(!$schema->existsModule($module) && $object->table() != null) {
                $data = new \stdClass();
                $data->TableStructure = TableCollection::get()->table($object->table());
                $data->Labels = $object->labels();
                $schema->module($module, $data);
            }
        } else {
            $object = $schema->models($module);
        }
        if($is_new === false) {
            $object->setIsNew(false);
        }
        return $object;
    }

    /**
     * Konstruktor kelas
     */
    public function __construct() {
        $this->preload();
    }

    /**
     * Melakukan pemanggilan fungsi ketika dilakukan pembacaan property
     * @param string $property Nama property/field
     * @return mixed
     */
    public function __get($property) {
        if(in_array('_'.$property, get_class_methods($this))) {
            return call_user_func(array($this, '_'.$property));
        }
        return $this->{$property};
    }

    /**
     * Memasukkan data ke dalam peta tabel ketika terjadi inisialisasi
     * @param mixed $property string Nama properti
     * @param mixed $value Nilai dari properti
     */
    public function __set($property, $value) {
        if(in_array('table', get_class_methods($this))) {
            /*$fields = SchemaCollection::get()->tables[$this->table()]['fields'];
            if($fields != null) {
                if(!in_array($property, $fields)) {
                    $this->____dataset['externals'][] = $property;
                }
            }*/
        }
        $this->{$property} = $value;
    }

    /**
     * Fungsi yang digunakan untuk menetapkan nama tabel yang diwakili oleh model
     * @return string
     */
    public function table()
    {
    }

    /**
     * Fungsi yang digunakan untuk menetapkan label-label yang digunakan oleh model
     * @return array
     */
    public function labels()
    {
    }

    /**
     * Fungsi yang digunakan untuk menetapkan aturan-aturan validasi yang digunakan oleh model
     * @return string
     */
    public function rules()
    {
    }

    /**
     * Memasukkan data model ke dalam pemetaan
     */
    private function preload()
    {
        if($this->labels() != null) {
            $this->schema('labels', $this->labels());
        }
        if($this->table() != null) {
            if(!TableCollection::get()->exists($this->table()))
            {
                if($desc = $this->db()->desc()) {
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
                        $temp['describe'][$field] = $info;
                    }
                    $temp['fields'] = $fields;
                    $temp['primary_key'] = $pkey;
                    TableCollection::get()->table($this->table(), $temp);
                }
            }
        }
    }

    /**
     * Mengambil dan mengubah informasi yang terkandung di dalam suatu model
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka nilai baliknya adalah semua informasi yang
     *   terkandung di dalam suatu model
     * - Jika memasukkan satu parameter, maka nilai baliknya adalah nilai informasi
     *   yang diambil berdasarkan nama atribut
     * - Jika memasukkan dua parameter, maka dia akan mengubah nilai suatu informasi
     *   berdasarkan atribut yang dimasukkan
     * @return nilai informasi model
     */
    public function dataset()
    {
        switch (func_num_args()) {
            case 0:
                return $this->____dataset;
            case 1:
                if (isset($this->____dataset[func_get_arg(0)])) {
                    return $this->____dataset[func_get_arg(0)];
                }
                break;
            case 2:
                $this->____dataset[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    /**
     * Mengambil skema tabel yang digunakan oleh model
     * @param optional $use_module boolean menentukan apakah pencarian menggunakan module
     * @return array
     */
    public function schema($use_module = false)
    {
        if(in_array('table', get_class_methods($this))) {
            if($use_module === true) {
                SchemaCollection::get()->setCurrentModule($this->____dataset['module']);
                return SchemaCollection::get();
            } else {
                return SchemaCollection::get()->module($this->____dataset['module']);
            }
        }
    }

    /**
     * Menentukan aturan validasi terhadap suatu model
     * @param mixed $field string Nama field yang akan diberi aturan
     * @param mixed $rules aturan validasi yang akan diberikan pada field tersebut
     * @param optional $params array Parameter yang dibutuhkan oleh aturan validasi yang digunakan
     */
    public function setRules($field, $rules, $params = array())
    {
        Validation::get()->setRules($field, $rules, $params);
    }

    /**
     * Mengecek suatu model, apakah termasuk model baru atau model untuk suatu record
     * @since 1.0.0
     * @return boolean status model sebagai baru atau bukan
     */
    public function isNew()
    {
        return $this->____dataset['is_new'];
    }

    /**
     * Menetapkan status model sebagai data baru atau data lama
     * @param mixed $status boolean status model sebagai data lama atau data baru
     */
    public function setIsNew($status)
    {
        $this->____dataset['is_new'] = (bool)$status;
    }

    /**
     * Menetapkan status model sebagai data baru
     */
    public function setNew()
    {
        $this->____dataset['is_new'] = true;
    }

    /**
     * Mengambil nama field yang menjadi primary key dari tabel yang diwakili oleh model
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->defval($this->schema()->TableStructure['primary_key'], $this->dataset('primary_key'));
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
        if($this->schema() != null) {
            return isset($this->schema()->Labels[$property]) ? $this->schema()->Labels[$property] : ucfirst($property);
        } else {
            return ucfirst($property);
        }
    }

    /**
     * Memasukkan suatu nilai ke dalam properti model
     * @since 1.0.0
     * @param $data daftar pengisian nilai dalam bentuk array. Nama properti model
     * berlaku sebagai key/indeks dan nilai properti berlaku sebagai value
     * @param $clean untuk menentukan apakah pengisian nilai disertai penyaringan atau
     * tidak. Jika nilainya TRUE, maka pengisian nilai disertai penyaringan. Jika nilainya
     * FALSE, maka pengisian nilai tidak disertai penyaringan
     */
    public function input($data, $clean = true)
    {
        if(!empty($data)) {
            foreach ($data as $key => $value) {
                if(!$this->isNew() && !in_array($key, $this->____dataset['updates'])) {
                    $this->____dataset['updates'][] = $key;
                }
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * Mengosongkan nilai field/properti pada model
     */
    public function clear() 
    {
        foreach ($this as $key => $value) {
            $this->{$key} = null;
        }
    }

    /**
     * Mengambil objek asli model
     * @param optional $is_new boolean Objek diambil sebagai data baru atau data lama
     */
    public function origin($is_new = false)
    {
        return self::getObject(get_class($this), $this->____dataset['module'], (bool)$is_new);
    }

    /**
     * Mengecek suatu properti model memiliki error atau tidak
     * @since 1.0.0
     * @return boolean status memiliki error atau tidak. Bernilai TRUE jika terdapat
     * error pada properti tersebut. Bernilai FALSE jika tidak terdapat error pada
     * properti tersebut
     */
    public function hasError()
    {
        return count($this->error()) > 0;
    }

    /**
     * Mengambil semua error atau error berdasarkan nama properti model
     * @since 1.0.0
     * @param $key adalah nama properti untuk menyaring error
     * @param $message adalah pesan error yang akan ditampilkan
     * @return informasi error atau void
     */
    public function error()
    {
        switch (func_num_args()) {
            case 0:
                return Validation::get()->errors;
            case 1:
                if (isset(Validation::get()->errors[func_get_arg(0)])) {
                    return Validation::get()->errors[func_get_arg(0)];
                }
                break;
            case 2:
                Validation::get()->errors[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    /**
     * Melakukan validasi terhadap model. Hasil validasi akan memberikan informasi error
     * ke dalam properti model dan memberikan status model valid atau tidak
     * @since 1.0.0
     * @return status validasi, bernilai TRUE jika model valid, bernilai FALSE jika
     * model tidak valid
     */
    public function validate()
    {
        $validated = Validation::get()->validate($this);
        Validation::get()->clearRules();
        return $validated;
    }

    /**
     * Melakukan validasi terhadap model. Hasil validasi akan memberikan informasi error
     * ke dalam properti model dan memberikan status model valid atau tidak
     * @param mixed $property Properti/field yang akan diabaikan dalam validasi
     * @since 1.0.1
     * @return status validasi, bernilai TRUE jika model valid, bernilai FALSE jika
     * model tidak valid
     */
    public function unvalidate($property = null)
    {
        if($property !== null) {
            if(!is_array($property)) {
                Validation::get()->unvalidates = array_merge(
                    Validation::get()->unvalidates,
                    array($property)
                );
            } else {
                Validation::get()->unvalidates = array_merge(
                    Validation::get()->unvalidates, $property
                );
            }
        } else {
            Validation::get()->unvalidate = true;
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
        if(!$this->isNew()) {
            throw new \Exception("This operation just active in new instance", 500);
        }
        if(!$this->validate()) {
            return;
        }
        $into = $values = null;
        if($availables === null) {
            $availables = array();
            $schema = $this->schema()->TableStructure;
            foreach ($schema['fields'] as $field) {
                if($schema['describe'][$field]['Extra'] != 'auto_increment' && isset($this->{$field})) {
                    $availables[] = $field;
                }
            }
        }
        $i = 0;
        $count = count($availables) - 1;
        foreach ($availables as $field) {
            $into .= $field;
            $values .=  "'".Db::get()->getConnection()->real_escape_string($this->{$field})."'";
            if($i < $count) {
                $into .= ', ';
                $values .= ', ';
            }
            $i++;
        }
        if($resource = $this->db()->insert($into, $values)->execute()) {
            $this->{$this->getPrimaryKey()} = Db::get()->getConnection()->insert_id;
            return $resource;
        }
    }

    /**
     * Mengubah data lama
     * @since 1.0.0
     * @param optional $availables array Nama-nama field yang akan digunakan
     * @return boolean
     */
    public function update($availables = null)
    {
        if(!$this->validate()) {
            return;
        }
        if($availables === null) {
            $fields = $this->schema()->TableStructure['fields'];
            if($fields == null) {
                throw new \Exception("No field in update list", 500);
            }
            $availables = array();
            foreach ($fields as $field) {
                if(isset($this->{$field})) {
                    $availables[] = $field;
                }
            }
        }

        $set = null;
        $count = count($availables) - 1;
        $i = 0;
        foreach ($availables as $field) {
            $set .= $field." = '".Db::get()->getConnection()->real_escape_string($this->{$field})."'";
            if($i < $count) {
                $set .= ', ';
            }
            $i++;
        }
        if($resource = $this->db()->update($set)->execute()) {
            return $resource;
        }
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
        $sql = Db::get()->replaceWith($args);
        // Membaca record dari database
        $this->db()->Query = $sql;
        $resource = $this->db()->execute();
        if($row = $resource->fetch_assoc()) {
            $record = clone $model;
            foreach ($row as $key => $value) {
                $record->{$key} = stripslashes($value);
            }
            $resource->free_result();
            return $record;
        }
    }

    /**
     * Mengambil nilai field dari perintah SQL yang menyaring satu field
     * @since 1.0.0
     * @return int|string|float
     */
    public function findData()
    {
        $use_pk_opt = false;
        switch (func_num_args()) {
            case 1:
                $params = func_get_arg(0);
                $field = is_array($params) && isset($params['select']) ? $params['select'] : '*';
                break;
            case 2:
                $params = func_get_arg(0);
                $use_pk_opt = (bool)func_get_arg(1);
                $field = $this->getPrimaryKey();
                if($field == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                break;
        }
        if(func_num_args() > 0) {
            $table = $this->table();
            $clear = isset($params['clear']) ? $params['clear'] : true;

            // Membentuk query SQL
            $db = $this->db()->select($field);
            if(is_array($params)) {
                if(isset($params['where'])){
                    $this->____dataset['params'] = array('where'=>$params['where']);
                    $db->Where($params['where']);
                }
                if(isset($params['group'])){
                    $db->Group($params['group']);
                }
                if(isset($params['order'])){
                    $db->Order($params['order']);
                }
            } else {
                $primary = $this->getPrimaryKey();
                if($primary == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                $where = array($primary.'=?', $params);
                $this->____dataset['params']['where'] = $where;
                $db->where($where);
            }
        } else {
            $db = $this->db()->select();
        }
        // Membaca record dari database
        $resource = $this->db()->execute();
        if($row = $resource->fetch_assoc()) {
            $record = clone $this;
            foreach ($row as $key => $value) {
                return stripslashes($value);
            }
        }
    }

    /**
     * Mengambil satu record/baris dari suatu tabel menggunakan model
     * @since 1.0.0
     * @return object
     */
    public function find() {
        $use_pk_opt = false;
        switch (func_num_args()) {
            case 1:
                $params = func_get_arg(0);
                $field = is_array($params) && isset($params['select']) ? $params['select'] : '*';
                break;
            case 2:
                $params = func_get_arg(0);
                $use_pk_opt = (bool)func_get_arg(1);
                $field = $this->getPrimaryKey();
                if($field == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                break;
        }
        if(func_num_args() > 0) {
            $table = $this->table();
            $clear = isset($params['clear']) ? $params['clear'] : true;

            // Membentuk query SQL
            $db = $this->db()->select($field);
            if(is_array($params)) {
                if(isset($params['where'])){
                    $this->____dataset['params'] = array('where'=>$params['where']);
                    $db->Where($params['where']);
                }
                if(isset($params['group'])){
                    $db->Group($params['group']);
                }
                if(isset($params['order'])){
                    $db->Order($params['order']);
                }
            } else {
                $primary = $this->getPrimaryKey();
                if($primary == null) {
                    throw new \Exception("This table or view doesn't have a primary key", 500);
                }
                $where = array($primary.'=?', $params);
                $this->____dataset['params']['where'] = $where;
                $db->where($where);
            }
        } else {
            $db = $this->db()->select();
        }
        // Membaca record dari database
        $resource = $this->db()->execute();
        if($row = $resource->fetch_assoc()) {
            $record = clone $this;
            foreach ($row as $key => $value) {
                $record->{$key} = stripslashes($value);
            }
            $resource->free_result();
            return $record;
        }
    }

    /**
     * Mengambil lebih dari satu record/baris dari suatu tabel menggunakan model
     * @param array $params Parameter tambahan untuk mengatur data yang dihasilkan
     * @since 1.0.0
     * @return array
     */
    public function findAll($params = array()) {
        $table = $this->table();
        $field = is_array($params) && isset($params['select']) ? $params['select'] : '*';
        $clear = isset($params['clear']) ? $params['clear'] : true;
        if(!isset($params['from'])){
            $db = $this->db()->select($field);
        } else {
            $db = $this->db($params['from'])->select($field);
        }
        if(isset($params['where'])){
            $db->Where($params['where']);
        }
        if(isset($params['group'])){
            $db->Group($params['group']);
        }
        if(isset($params['order'])){
            $db->Order($params['order']);
        }
        // Mengaktifkan paginasi jika memungkinkan
        if(isset($params['limit'])){
            $db->limit($params['limit']);
            if($current = $this->config('pager')) {
                $this->config('pager', array_merge($current, array(
                    'table'=>$this->table(),
                    'select'=>isset($params['select']) ? $params['select'] : null,
                    'from'=>isset($params['from']) ? $params['from'] : null,
                    'primary_key'=>$this->getPrimaryKey(),
                    'where'=>isset($params['where']) ? $params['where'] : null
                )));
            }
        }
        // Mengambil semua record dari database
        $collection = new ModelCollection();
        $collection->setParameters($params);
        $collection->setModel($this);
        $collection->setDb($db);
        return $collection;
    }

    /**
     * Memuat berkas model dari mengambil objek model
     * @param mixed $name string Nama model yang akan dimuat
     * @param optional $is_new boolean status apakah model dimuat sebagai data baru atau data lama
     * @since 1.0.0
     * @return object
     */
    public function load($name, $is_new = false) {
        if(preg_match('/^\/([a-zA-Z][a-zA-Z0-9]+)$/i', $name, $match)) {
            $path = $this->modelDir()."$match[1].php";
            $class = 'Djokka\\Models\\'.$match[1];
        } else {
            $path = $this->moduleDir().$this->config('module').DS."models".DS."$name.php";
            $class = 'Djokka\\Models\\'.$name;
        }
        $path = $this->realPath($path);
        if(!file_exists($path)) {
            throw new \Exception("Model file not found in path $path", 404);
        }
        include_once($path);
        if(!class_exists($class)) {
            throw new \Exception("Class $class is not defined in file $path", 500);
        }
        return $instance = $class::getObject($class, $name, $is_new);
    }


    /**
     * Memuat pengeksekusi perintah SQL untuk model
     * @param optional $from string Nama tabel atau dipadukan dengan perintah JOIN
     * @return objek kelas {@link Djokka\Db}
     */
    public function db($from = null) {
        Db::get()->From = $this->defval($from, $this->table());
        if(isset($this->____dataset['params']['where'])) {
            Db::get()->Where = $this->____dataset['params']['where'];
        }
        return Db::get();
    }

    /**
     * Memanggil view melalui model
     * @param string $name Nama view
     * @param array $params Data yang akan diekstrak ke view
     * @deprecated
     * @return string
     */
    public function view($name, $params = array())
    {
        return Controller::get()->getView($name, $params);
    }
}