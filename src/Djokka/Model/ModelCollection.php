<?php

namespace Djokka\Model;

class ModelCollection
{
	private $resource;
    private $parameters = array();
	private $model;
	public $RowCount;
	public $FieldCount;

    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.0
     */
    private static $instance;

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

	public function __get($property)
	{
		if($property == 'Rows') {
			$this->Rows = array();
            while ($row = $this->resource->fetch_assoc()) {
                $record = clone $this->model;
                foreach ($row as $key => $value) {
                    $record->{$key} = stripslashes($value);
                }
                $this->Rows[] = $record;
            }
            $this->resource->free_result();
		}
		return $this->{$property};
	}

	public function setModel($model)
	{
		$this->model = $model;
	}

	public function setDb($db)
	{
		foreach ($db as $key => $value) {
			if(!isset($this->{$key})) {
				$this->{$key} = $value;
			}
		}
		$this->resource = $db->execute();
		$this->RowCount = $this->resource->num_rows;
		$this->FieldCount = $this->resource->field_count;
	}

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

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
}