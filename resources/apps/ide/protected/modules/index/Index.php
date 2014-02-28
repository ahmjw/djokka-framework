<?php

namespace Djokka\Controllers;

use Djokka\Controller;

class Index extends Controller
{
	public function actionIndex()
	{
		$this->view('index');
	}
}