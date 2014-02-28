<?php

namespace Djokka\Controllers;

use Djokka\Controller;
use Djokka\Helpers\Config;

class ActiveRecord extends Controller
{
	private $_config;

	public function actionIndex()
	{
		$model = $this->model('Builder');
		$success = false;
		$config = $this->config('app_config');

		$app_config_dir = $this->realPath($config['dir'] . $config['app_path'] . $config['config_path']).DS;
		//$new_config = Config::getInstance()->render($app_config_dir);

		// Retrieve App database configuration
		$app_db_file = $app_config_dir.'db.php';
		if(file_exists($app_db_file)) {
			$app_db_config = include($app_db_file);
			$ide_db_file = $this->configDir().'db.php';
			$ide_db_config = include($ide_db_file);
			if($app_db_config !== $ide_db_config) {
				copy($app_db_file, $ide_db_file);
			}

			// Read database configuration and fill to IDE configuration
			if(is_array($ide_db_config)) {
				$model->host = $ide_db_config[0]['hostname'];
				$model->user = $ide_db_config[0]['username'];
				$model->pass = $ide_db_config[0]['password'];
				$model->database = $ide_db_config[0]['database'];
				$this->config('db', $ide_db_config);
			}
		}

		$tables = array();
		$_tables = $this->lib('Db')->getTables();
		if(!empty($_tables)) {
			foreach ($_tables as $table) {
				$part = explode('_', $table);
				$class = '';
				if(!empty($part)) {
					foreach ($part as $_part) {
						$class .= ucfirst($_part);
					}
				}
				$tables[] = array(
					'name'=>$table,
					'class'=>$class,
					'is_exists'=>false
				);
			}
		}

		if(!empty($_POST)) {
			$model->input($_POST);
			print_r($model);
			exit();
			if($model->validate()) {
				$success = true;
			}
		}
		$this->view('index', array(
			'model'=>$model,
			'success'=>$success,
			'tables'=>$tables
		));
	}
}