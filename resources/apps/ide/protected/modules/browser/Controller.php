<?php

namespace Djokka\Modules\Browser;

use Djokka\BaseController;
use Djokka\Helpers\Config;

class Controller extends BaseController
{
	private $_config;

	public function actionIndex()
	{
		$this->view('index');
	}
}