<?php

namespace Djokka\Modules\Modulecreator;

use Djokka\Controller as Base;

class Controller extends Base
{
	private $_dir;
	private $_config;

	public function actionIndex()
	{
		$model = $this->model('Creator');
		$success = false;
		if (!empty($_POST)) {
			$model->input($_POST);
			$config = $this->config('app_config');
			$this->_config = $config;
			$this->_dir = $model->dir = $this->realPath($config['dir'] . $config['app_path'] . $config['module_path']);

			if ($model->validate()) {
				$this->createModule($model->name);
				$success = true;
			}
		}
		$this->view('index', array(
			'model'=>$model,
			'success'=>$success
		));
	}

	private function makePath($dir, $path)
	{
		$_path = $this->realPath($dir.'/'.$path);
		if (!file_exists($_path)) {
			$temp = '';
			$path = $this->realPath($path);
			$part = explode(DS, $path);

			if (!empty($part)) {
				foreach ($part as $item) {
					$temp .= DS.$item;
					$_dir = $dir . $temp;
					if (!file_exists($_dir)) {
						mkdir($_dir);
					}
				}
			}
		}
		return $_path;
	}

	private function createModule($route)
	{
		$path = $this->realPath(str_replace('/', '/modules/', $route));
		$dir = $this->_dir.DS.$path;
		$temp = $this->makePath($this->_dir, $path);

		preg_match('/\/?([a-z][a-zA-Z0-9]+)$/i', $route, $match);
		$className = ucfirst($match[1]);
		$code = $this->renderCode('controller_formatter', array(
			'className'=>$className
		));
		$this->lib('File')->write($dir.DS.$className.'.php', $code);

		$temp = $this->makePath($temp, 'views');
		$this->lib('File')->write($temp.DS.'index.php', "<h1>Index of ".$className."</h1>".
			"\r\n\r\n<div class=\"alert alert-info\">If you see this text, this module is works</div>");
		return $dir;
	}

	private function renderCode($name, array $data = array())
	{
		$path = $this->dataDir().$name.'.php';
		$code = file_get_contents($path, FILE_USE_INCLUDE_PATH);
		foreach ($data as $key => $value) {
			$code = str_replace('(='.$key.')', $value, $code);
		}
		return $code;
	}
}