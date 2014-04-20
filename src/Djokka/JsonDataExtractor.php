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
			$this->data = array('valid' => true);
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

	public function render()
	{
		header('Content-type: application/json');
        echo json_encode($this->data);
	}
}