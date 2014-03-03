<?php

namespace Djokka\Models;

use Djokka\Model;

class Builder extends Model
{
	public $tableName;
	public $className;
	public $path;

	public function rules()
	{
		return array(
			array('tableName, className, path', 'required'),
			array('className', 'regex', '/[A-Z][a-zA-Z0-9_]*/i'),
			array('className', 'check')
		);
	}

	public function labels()
	{
	}

	public function check()
	{
		if(file_exists($this->path)) {
			$this->error('className', 'This model is already exists');
		}
	}
}