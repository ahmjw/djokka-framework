<?php

namespace Djokka\Model;

class SchemaCollection 
{
    private $current_module;
    private $modules = array();
    private $models = array();

    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.1
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.1
     * @param $class adalah nama kelas (opsional)
     * @return objek instance kelas
     */
    public static function get($class = __CLASS__)
    {
        if(self::$instance == null) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function existsModule($name)
    {
        return isset($this->modules[$name]);
    }

    public function existsModel($name)
    {
        return isset($this->models[$name]);
    }

    public function setCurrentModule($module)
    {
        $this->current_module = $module;
    }

    public function module()
    {
        switch (func_num_args()) {
            case 0:
                return $this->modules;
            case 1:
                return $this->modules[func_get_arg(0)];
            case 2:
                $this->modules[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    public function models()
    {
        switch (func_num_args()) {
            case 0:
                return $this->models;
            case 1:
                return $this->models[func_get_arg(0)];
            case 2:
                $this->models[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

}