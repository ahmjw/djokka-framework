<?php

namespace Djokka\Modules\Index;

use Djokka\BaseController;

class Controller extends BaseController
{
	public function actionIndex()
	{
		$this->view('index');
	}
}