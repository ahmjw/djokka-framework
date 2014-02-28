<?php

namespace Djokka\Models;

use Djokka\DataForm;

class Builder extends DataForm
{
	public $className = array();
	public $generate = array();

	public function rules()
	{
		return array(
			array('className', 'required'),
			array('className', 'regex', '/[A-Z][a-zA-Z0-9_]*/i')
		);
	}
}