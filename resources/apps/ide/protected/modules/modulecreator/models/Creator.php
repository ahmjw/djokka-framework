<?php

namespace Djokka\Models;

use Djokka\DataForm;

class Creator extends DataForm
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

	public function checkModule()
	{
		$path = $this->dir . DS . $this->name . DS . ucfirst($this->name);
		if (file_exists($path))
			$this->error('name', 'This module is already exists');
	}
}