<?php

/**
 * Membentuk koleksi model guna optimasi sistem
 * @since 1.0.2
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.2
 */

namespace Djokka\Model;

/**
 * Kelas pendampingyang membantu kelas Djokka\Model untuk optimasi
 */
class ModelCollection
{
    /**
     * Resource yang dihasilkan dari hasil eksekusi perintah SQL yang dilakukan database
     */
	private $resource;

    /**
     * Parameter yang dimasukkan untuk penyaringan hasil
     */
    private $parameters = array();

    /**
     * Model yang meminta koleksi
     */
	private $model;

    /**
     * Jumlah baris yang dihasilkan dari hasil eksekusi perintah SQL
     */
	public $rowCount;

    /**
     * Jumlah kolom yang dihasilkan dari hasil eksekusi perintah SQL
     */
	public $fieldCount;

    /**
     * Menampung instance dari kelas
     * @since 1.0.2
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.2
     * @return objek instance kelas
     */
    public static function get()
    {
        if(self::$instance == null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Memasukkan koleksi model ke dalam suatu properti
     * @param string $property Nama properti/field
     * @return mixed
     */
	public function __get($property)
	{
		if($property == 'rows') {
			$this->rows = array();
            while ($row = $this->resource->fetch_assoc()) {
                $record = clone $this->model;
                foreach ($row as $key => $value) {
                    $record->{$key} = stripslashes($value);
                }
                $this->rows[] = $record;
            }
            $this->resource->free_result();
		}
        if (isset($this->{$property})) {
          return $this->{$property};
        }
	}

    /**
     * Menetapkan objek model yang melakukan permintaan koleksi
     * @param mixed $model object Objek model
     */
	public function setModel($model)
	{
		$this->model = $model;
	}

    /**
     * Memberikan data ke dalam koleksi model
     * @param mixed $db object Objek dari kelas {@link Djokka\Db}
     */
	public function setDb($db)
	{
		foreach ($db as $key => $value) {
			if(!isset($this->{$key})) {
				$this->{$key} = $value;
			}
		}
		$this->resource = $db->execute();
		$this->rowCount = $this->resource->num_rows;
		$this->fieldCount = $this->resource->field_count;
	}

    /**
     * Mengambil objek model dari hasil parsing perintah SQL
     * @return object
     */
    public function getModel()
    {
        $model = $this->model;
        $data = $this->parameters['where'];
        if(preg_match_all('/(?:^\s*\?\s+|\s+[^=]+\s*\?\s+|(\w+)\s*(?:=|!=|>|>=|<|<=|<>|(?:L|l)(?:I|i)(?:K|k)(?:E|e)|(?:N|n)(?:O|o)(?:T|t)\s+(?:L|l)(?:I|i)(?:K|k)(?:E|e))\s*?)/i', $data[0], $matches)) {
            $params = array_slice($data, 1);
            for ($i=0; $i < count($matches[0]); $i++) {
                $property = trim($matches[1][$i]);
                if(!empty($property)) {
                    $model->{$property} = $params[$i];
                }
            }
        }
        return $model;
    }

    /**
     * Menetapkan parameter yang akan menyaring hasil
     * @param mixed $parameters array Parameter untuk penyaringan
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
}