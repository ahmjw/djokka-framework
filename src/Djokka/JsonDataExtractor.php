<?php

namespace Djokka;

use Djokka\Helpers\Config;

class JsonDataExtractor extends Shortcut
{
	private $data;

	private static $instance;

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	public function __construct()
	{
		Config::getInstance()->render();
		$func = 'action' . rawurldecode($_GET['action']);
		if (method_exists($this, $func)) {
			call_user_func(array($this, $func));
		} else {
			throw new \Exception("Called method is not found", 500);
		}
	}

	public function actionGetTables()
	{
		$this->data = $this->db('MySql', 'Table')->getTables();
	}

	public function actionTableSchema()
	{
		$name = rawurldecode($_GET['name']);
		$this->data = $this->db('MySql', 'Table')->getSchema($name);
	}

	public function actionAllConfig()
	{
		Config::getInstance()->render();
		$this->data = Config::getInstance()->getConfig();
	}

	public function actionModelSchema()
	{
		$name = rawurldecode($_GET['name']);
		$model = $this->model('/' . $name);
		if (isset($_GET['check_validity']) && $_GET['check_validity'] == true) {
			$this->data = array('valid_code' => true, 'valid_table' => true);
		} else {
			if ($model !== null) {
				$this->data = array(
					'name' => $name,
					'table' => $model->table(),
					'labels' => $model->labels(),
					'rules' => $model->rules(),
					'schema' => $model->schema(),
				);
			}
		}
	}

	public function actionModuleSchema()
	{
		$name = rawurldecode($_GET['name']);
		$info = new Hmvc($name);
		$path = $this->moduleDir() . $name;
		$views = $actions = array();
		$models = array();

		if (file_exists($info->path)) {
			include($info->path);

			$ref = new \ReflectionClass($info->class);
			$methods = $properties = array();
			$imethods = $iproperties = array();
			$constants = $ref->getConstants();

			foreach ($ref->getMethods() as $method) {
				if ($info->class == $method->class) {
					$methods[] = $method->name;
					if (preg_match('/action([a-zA-Z0-9_]+)/i', $method->name, $match)) {
						$actions[] = lcfirst($match[1]);
					}
				} else {
					$imethods[] = array(
						'name' => $method->name,
						'class' => $method->class,
					);
				}
			}
			foreach ($ref->getProperties() as $prop) {
				if ($info->class == $prop->class) {
					$properties[] = $prop->name;
				} else {
					$iproperties[] = array(
						'name' => $prop->name,
						'class' => $prop->class,
					);
				}
			}
			$reflection = array(
				'properties' => $properties,
				'methods' => $methods,
				'constants' => $constants,
				'inherit' => array(
					'properties' => $iproperties,
					'methods' => $imethods,
				)
			);

			$t_path = $path . DS . 'views';
			if (file_exists($t_path)) {
				$views = $this->lib('File')->getFiles($t_path);
			}
			$t_path = $path . DS . 'models';
			if (file_exists($t_path)) {
				$models = $this->lib('File')->getFiles($t_path);
			}

			$class = $info->class;
			$instance = new $class;

			$access_control = $routes = array();
			if (method_exists($instance, 'accessControl')) {
				$access_control = $instance->accessControl();
			}
			if (method_exists($instance, 'routes')) {
				$routes = $instance->routes();
			}

			$this->data = array(
				'info' => $info,
				'actions' => $actions,
				'access_control' => $access_control,
				'routes' => $routes,
				'views' => $views,
				'models' => $models,
				'reflector' => $reflection,
			);
		}
	}

	public function render()
	{
		header('Content-type: application/json');
        echo json_encode($this->data);
	}
}