<?php

namespace Djokka\Modules\Builder;

use Djokka\Controller as Base;

class Controller extends Base
{
	private $_dir;
	private $_module_path;

	public function actionIndex()
	{
		$config = $this->config('app_config');
		$this->_dir = $config['dir'] . $config['app_path'];

		$module_path = $this->realPath($this->_dir . $config['module_path']);
		$config_path = $this->realPath($this->_dir . $config['config_path']);
		$model_path = $this->realPath($this->_dir . $config['model_path']);
		$component_path = $this->realPath($this->_dir . $config['component_path']);
		$data_path = $this->realPath($this->_dir . $config['data_path']);
		$asset_path = $this->realPath($this->_dir . $config['asset_path']);
		$theme_path = $this->realPath($this->_dir . $config['theme_path']);
		$plugin_path = $this->realPath($this->_dir . $config['plugin_path']);

		if ($_POST) {
			$temp = $this->makePath($this->_dir, $config['module_path']);
			$this->lib('File')->copyDir($this->dataDir().DS.'themes', $theme_path);

			$this->makePath($this->_dir, $config['asset_path']);
			$this->lib('File')->copyDir($this->assetDir(), $asset_path);

			$this->makePath($this->_dir, $config['config_path']);
			$this->lib('File')->write($config_path.DS.'db.php', $this->renderCode('config_db'));
			$this->lib('File')->write($config_path.DS.'general.php', $this->renderCode('config_general'));
			$this->lib('File')->write($config_path.DS.'routes.php', $this->renderCode('config_routes'));

			$this->makePath($this->_dir, $config['model_path']);
			$this->makePath($this->_dir, $config['component_path']);
			$this->makePath($this->_dir, $config['data_path']);
			$this->makePath($this->_dir, $config['theme_path']);
			$this->makePath($this->_dir, $config['plugin_path']);
		}
		$data = array(
			'module'=>array(
				'path'=>$module_path,
				'is_exists'=>file_exists($module_path)
			),
			'config'=>array(
				'path'=>$config_path,
				'is_exists'=>file_exists($config_path)
			),
			'model'=>array(
				'path'=>$model_path,
				'is_exists'=>file_exists($model_path)
			),
			'component'=>array(
				'path'=>$component_path,
				'is_exists'=>file_exists($component_path)
			),
			'data'=>array(
				'path'=>$data_path,
				'is_exists'=>file_exists($data_path)
			),
			'asset'=>array(
				'path'=>$asset_path,
				'is_exists'=>file_exists($asset_path)
			),
			'theme'=>array(
				'path'=>$theme_path,
				'is_exists'=>file_exists($theme_path)
			),
			'plugin'=>array(
				'path'=>$plugin_path,
				'is_exists'=>file_exists($plugin_path)
			),
		);
		$this->view('index', array(
			'data'=>$data
		));
	}

	private function makePath($dir, $path)
	{
		$_path = $this->realPath($dir.DS.$path);
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