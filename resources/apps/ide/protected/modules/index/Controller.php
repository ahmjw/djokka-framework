<?php

namespace Djokka\Modules\Index;

use Djokka\Controller as Base;

class Controller extends Base
{
	public function actionIndex()
	{
		$this->view('index');
	}
}