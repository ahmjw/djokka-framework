<?php

namespace Djokka\Models;

use Djokka\Model;

class Creator extends Model
{
	public $name;
	public $dir;

	public function rules()
	{
		return array(
			array('name', 'required, checkModule'),
			array('name', 'regex', '/[a-z][a-zA-Z0-9]*(?:\/[a-z][a-zA-Z0-9]*)*/i'),
		);
	}

	public function labels()
	{		
	}

	public function checkModule()
	{
		$path = $this->dir . DS . $this->name . DS . ucfirst($this->name);
		if (file_exists($path))
			$this->error('name', 'This module is already exists');
	}
}