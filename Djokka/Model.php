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

use Djokka\Model\SchemaCollection;
use Djokka\Model\TableCollection;
use Djokka\Model\ModelCollection;
use Djokka\Model\Validation;
use Djokka\Helpers\String;

/**
 * Kelas Djokka\Model adalah kelas pustaka framework. Dipergunakan untuk mengendalikan,
 * mengelola, dan mengakses data model
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Model extends \Djokka
{
    const FIND = 1;
    const FIND_ALL = 2;
    const SCALAR = 3;

    private static $instance;

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

    public function __construct() {
        $this->preload();
    }

    public function __get($property) {
        if(in_array('_'.$property, get_class_methods($this))) {
            return call_user_func(array($this, '_'.$property));
        }
        return $this->{$property};
    }

    public function __set($property, $value) {
        if(in_array('table', get_class_methods($this))) {
            $fields = SchemaCollection::get()->tables[$this->table()]['fields'];
            if($fields != null) {
                if(!in_array($property, $fields)) {
                    $this->____dataset['externals'][] = $property;
                }
            }
        }
        $this->{$property} = $value;
    }

    public function table()
    {
    }

    public function labels()
    {
    }

    public function rules()
    {
    }

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
                return $this->____dataset[func_get_arg(0)];
            case 2:
                $this->____dataset[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

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

    public function setIsNew($status)
    {
        $this->____dataset['is_new'] = (bool)$status;
    }

    public function setNew()
    {
        $this->____dataset['is_new'] = true;
    }

    public function getPrimaryKey()
    {
        return $this->defval($this->schema()->TableStructure['primary_key'], $this->dataset('primary_key'));
    }

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
        return $this->defval($this->schema()->Labels[$property], ucfirst($property));
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

    public function clear() 
    {
        foreach ($this as $key => $value) {
            $this->{$key} = null;
        }
    }

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
                return Validation::get()->errors[func_get_arg(0)];
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
     * Melakukan operasi penyimpanan model
     * @since 1.0.0
     * @param $params adalah parameter tambahan untuk mengatur proses penyimpanan model
     * @return objek resource hasil operasi penyimpanan model
     */
    public function save($availables = null)
    {
        return $this->isNew() ? $this->insert($availables) : $this->update($availables);
    }

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
     * Membentuk query SQL untuk operasi penyimpanan record model. Operasi penyimpanan
     * dapat berupa INSERT ataupun UPDATE
     * @since 1.0.0
     * @param $params adalah parameter tambahan untuk mengatur pembentukan query SQL
     * @return string query SQL yang telah terbentuk
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

    public function getData()
    {
    }

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

    public function load($name, $is_new = false) {
        if(preg_match('/^\/([a-zA-Z][a-zA-Z0-9]+)$/i', $name, $match)) {
            $path = $this->moduleDir()."models".DS."$match[1].php";
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

    public function db($from = null) {
        Db::get()->From = $this->defval($from, $this->table());
        if(isset($this->____dataset['params']['where'])) {
            Db::get()->Where = $this->____dataset['params']['where'];
        }
        return Db::get();
    }

    public function view($name, $params = array())
    {
        return Controller::get()->getView($name, $params);
    }
}