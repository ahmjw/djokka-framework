<?php

/**
 * This file is controller of module '(=className)'
 */

namespace Djokka\Modules\(=className);

use Djokka\BaseController;

/**
 * This class will controller of module '(=className)'
 */
class Controller extends BaseController
{
	/**
	 * The page for action 'index'
	 * @return void
	 */
	public function actionIndex()
	{
		$this->view('index');
	}
}