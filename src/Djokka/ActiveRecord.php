<?php

/**
 * Djokka Framework active record class file
 * @since 1.0.1
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013-2014 Djokka Media
 */

namespace Djokka;

use Djokka\Model\SchemaCollection;
use Djokka\Model\TableCollection;
use Djokka\Model\Validation;
use Djokka\Helpers\String;

/**
 * Parent class for all model with database connection, that call as Active Record.
 * This class will provides the record access to your database and validate before saving changes.
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.1
 */
abstract class ActiveRecord extends Model
{
    /**
     * Declare the table name for model abstraction
     * @since 1.0.3
     * @return string
     */
    abstract function tableName();

    /**
     * Important information for model to works
     * @since 1.0.3
     */
    protected $_dataset = array(
        'is_new'     => false,
        'module'     => null,
        'driver'     => null,
        'condition'  => null,
    );

    /**
     * This class constructor
     * @since 1.0.1
     */
    public function __construct()
    {
        $this->preload();
    }

    public function __get($field)
    {
        $func = 'get' . ucfirst($field);
        if (method_exists($this, $func)) {
            $this->{$field} = call_user_func(array($this, $func));
        }
        return $this->{$field};
    }

    /**
     * Getting the driver subclass object by default connection
     * @param string $name Name of the subclass name.
     * The subclass name that you can put is: Table and Query
     * @since 1.0.3
     * @return object Subclass object of Djokka\Database\Drivers\[Driver name]\[Subclass name]
     */
    public function getDriver($name)
    {
        $class = $this->_dataset['driver'] . '\\' . $name;
        return $class::getInstance();
    }

    /**
     * Loads the table schema and store to the table collector object
     * @since 1.0.1
     */
    private function preload()
    {
        $config = $this->config('db');
        if (!isset($config[0]['driver'])) {
            throw new \Exception("Database driver name must be declared", 500);
        }
        $driver = $config[0]['driver'];
        if (!isset($config[0]['driver'])) {
            $driver = $this->config('database_driver');
        }
        if (trim($driver) == "") {
            throw new \Exception("No database driver to load", 500);
        }
        $this->_dataset['driver'] = 'Djokka\\Database\\Drivers\\' . $driver;

        $this->table('labels', $this->labels());
        if (!TableCollection::getInstance()->exists($this->tableName()))
        {
            $desc = $this->getDriver('Table')->desc($this->tableName());
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
                $this->table($temp);
            }
        }
    }

    /**
     * Access the table schema that stored in table collector
     * @since 1.0.2
     * @return mixed
     */
    public function table()
    {
        $data = TableCollection::getInstance()->table($this->tableName());
        switch (func_num_args()) {
            case 0:
                return $data;
            case 1:
                if (!is_array(func_get_arg(0)) && isset($data[func_get_arg(0)])) {
                    return $data[func_get_arg(0)];
                } else {
                    TableCollection::getInstance()->table($this->tableName(), func_get_arg(0));
                }
                break;
            default:
                break;
        }
    }

    /**
     * Checking the model status
     * @return bool Marks as new record if returns TRUE and marks as exists record
     * if returns FALSE
     * @since 1.0.1
     */
    public function isNew()
    {
        return $this->_dataset['is_new'];
    }

    /**
     * Directly change the model status as new record
     * @since 1.0.1
     */
    public function setAsNew()
    {
        $this->_dataset['is_new'] = true;
        return $this;
    }

    /**
     * Get the primary key of the table
     * @return string
     * @since 1.0.1
     */
    public function getPrimaryKey()
    {
        $pkey = $this->table('primary_key');
        return $pkey !== null ? $pkey : $this->dataset('primary_key');
    }

    /**
     * Set the primary key of the table if has no primary key
     * @param string $key Field name of table
     * @since 1.0.1
     */
    public function setPrimaryKey($key)
    {
        $this->dataset('primary_key', $key);
    }

    /**
     * Get the field label of model
     * @param string $field Name of model field
     * @since 1.0.1
     * @return string
     */
    public function label($field = null)
    {
        $labels = $this->labels();
        if ($labels !== null && isset($labels[$field])) {
            return  $labels[$field];
        } else {
            return ucfirst($field);
        }
    }

    /**
     * Get the field label of model
     * @param string $field Name of model field
     * @since 1.0.1
     * @return string
     */
    public function enum($field = null)
    {
        $enums = $this->enums();
        if ($enums !== null && isset($enums[$field])) {
            return  $enums[$field];
        } else {
            return array();
        }
    }

    /**
     * Save the record automatically. The record will inserting if marks as new recod and
     * the record will be updating if marks as exists record
     * @param array $availables Declare manually that fields wants to apply
     * @since 1.0.1
     * @return bool Returns TRUE if saving succeed and FALSE if saving fails
     */
    public function save(array $availables = array())
    {
        return $this->_dataset['is_new'] ? $this->insert($availables) : $this->update($availables);
    }

    /**
     * Inserting the new record
     * @param array $availables Declare manually that fields wants to apply
     * @since 1.0.1
     * @return bool Returns TRUE if inserting succeed and FALSE if inserting fails
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
     * Updating the exists record
     * @param array $availables Declare manually that fields wants to apply
     * @since 1.0.1
     * @return bool Returns TRUE if updating succeed and FALSE if updating fails
     */
    public function update(array $availables = array())
    {
        if (!$this->validate()) {
            return false;
        }
        return $this->getDriver('Crud')->updateImpl($this, $availables);
    }

    /**
     * Deleting the exists record. Before use this function, load the exists record with
     * function find().
     * @since 1.0.1
     * @return bool Returns TRUE if deleting succeed and FALSE if deleting fails
     */
    public function delete()
    {
        return $this->getDriver('Crud')->deleteImpl($this->tableName(), $this->dataset('condition'));
    }

    /**
     * Counts the records
     * @since 1.0.1
     * @return int
     */
    public function count()
    {
        return $this->getDriver('Crud')->countImpl($this->tableName(), $this->getPrimaryKey(), func_get_args());
    }

    /**
     * Get a field or single value
     * @since 1.0.2
     * @return int|string|float
     */
    public function findData()
    {
        return $this->getDriver('Crud')->findDataImpl($this->tableName(), $this->getPrimaryKey(), func_get_args());
    }

    /**
     * Get a record from table
     * @since 1.0.3
     * @return object Object of model contains record
     */
    public function find()
    {
        return $this->getDriver('Crud')->findImpl($this, func_get_args());
    }

    /**
     * Get records from table and store as model collection
     * @param array $params Options for filter the record
     * @since 1.0.1
     * @return object Object of Djokka\Model\ModelCollection
     */
    public function findAll(array $params = array())
    {
        return $this->getDriver('Crud')->findAllImpl($this, $params);
    }

    /**
     * Get the paging information
     * @since 1.0.3
     * @return array Results sequentially as: [0] page number, [1] count of pages and [2] total records
     */
    public function getPager()
    {
        return $this->getDriver('Crud')->getPagerImpl($this, func_get_args());
    }

    /**
     * Load the database driver subclass 'Query' to runs the query builder
     * @param string $from Specified the table name or joins with other table
     * @since 1.0.1
     * @return object Object of class Djokka\Driver\[Driver name]\Query
     */
    public function q()
    {
        return $this->getDriver('Crud')->qImpl($this, func_get_args());
    }
}