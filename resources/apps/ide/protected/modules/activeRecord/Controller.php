<?php

namespace Djokka\Modules\ActiveRecord;

use Djokka\BaseController;
use Djokka\Helpers\Config;

class Controller extends BaseController
{
	private $_config;

	public function actionIndex()
	{
		$model = $this->model('Builder');
		$success = false;
		$config = $this->config('app_config');

		$app_config_dir = $this->realPath($config['dir'] . $config['app_path'] . $config['config_path']).DS;
		$app_model_dir = $this->realPath($config['dir'] . $config['app_path'] . $config['model_path']).DS;
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

		$tables = array(''=>'-- Select --');
		$_tables = $this->db()->getDriver('Table')->getTables();
		if(!empty($_tables)) {
			foreach ($_tables as $table) {
				$tables[$table] = $table;
			}
		}

		if(!empty($_POST)) {
			$model->input($_POST);
			$model->path = $app_model_dir.$model->className.'.php';
			if($model->validate()) {
				$labels = "return array(\r\n";
				$rules = "return array(\r\n";
				$required = $unique = $other = $others = $enum = '';
				$schema = $this->db()->getDriver('Table')->getSchema($model->tableName);
				$count = count($schema['describe']);
				$pkey = $schema['primary_key'];
				$i = 0;
				foreach ($schema['describe'] as $field => $item) {
					$labels .= "\t\t\t'$field' => '" . $this->makeLabel($field) . "',\r\n";
					if($item['Null'] == 'NO' && $item['Extra'] != 'auto_increment') {
						$required .= $field;
						if($i < $count - 1) {
							$required .= ', ';
						}
					}
					if($item['Key'] == 'UNI') {
						$unique .= $field;
						if($i < $count - 1) {
							$unique .= ', ';
						}
					}
					if(preg_match('/(.+)\((.+)\)/i', $item['Type'], $match)) {
						if($match[1] != 'enum') {
							$other .= "\t\t\tarray('$field', 'maxLength($match[2])'),\r\n";
						} else {
							$enum .= "\t\t\t'$field' => array($match[2]),\r\n";
						}
					}
					$i++;
				}
				$labels .= "\t\t);";
				if($required != '') {
					$rules .= "\t\t\tarray('".$required."', 'required'),\r\n";
				}
				if($unique != '') {
					$rules .= "\t\t\tarray('".$unique."', 'unique'),\r\n";
				}
				if($enum != '') {
					$others .= "\r\n\r\n\t/**\r\n".
						"\t * Declare enum of field\r\n".
						"\t * @return array\r\n".
						"\t */\r\n".
						"\tpublic function enums()\r\n".
						"\t{\r\n".
						"\t\treturn array(\r\n".$enum.
						"\t\t);\r\n".
						"\t}";
				}
				$rules .= $other;
				$rules .= "\t\t);";
				$this->lib('File')->write($model->path, $this->renderCode('model_formatter', array(
					'className'=>$model->className,
					'tableName'=>$model->tableName,
					'labels'=>$labels,
					'rules'=>$rules,
					'others'=>$others
				)));
				$success = true;
			}
		}
		$this->view('index', array(
			'model'=>$model,
			'success'=>$success,
			'tables'=>$tables,
		));
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

	private function makeClassName($text)
	{
		$part = explode('_', $text);
		$result = '';
		if(!empty($part)) {
			foreach ($part as $_part) {
				$result .= ucfirst($_part);
			}
		}
		return $result;
	}

	private function makeLabel($text)
	{
		$part = explode('_', $text);
		$result = '';
		if(!empty($part)) {
			$count = count($part);
			foreach ($part as $i => $_part) {
				$result .= ucfirst($_part);
				if($i < $count - 1) {
					$result .= ' ';
				}
			}
		}
		return $result;
	}
}