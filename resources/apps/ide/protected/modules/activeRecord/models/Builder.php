<?php

namespace Djokka\Models;

use Djokka\DataForm;

class Builder extends DataForm
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

	public function check()
	{
		if(file_exists($this->path)) {
			$this->error('className', 'This model is already exists');
		}
	}
}