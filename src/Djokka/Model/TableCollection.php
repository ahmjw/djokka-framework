<?php

namespace Djokka\Model;

class TableCollection
{

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

	public function table() {
		switch (func_num_args()) {
			case 1:
				return $this->{func_get_arg(0)};
			case 2:
				if(!is_array(func_get_arg(1))) {
					return $this->{func_get_arg(0)}[func_get_arg(1)];
				} else {
					$this->{func_get_arg(0)} = func_get_arg(1);
					break;
				}
			case 3:
				$this->{func_get_arg(0)}[func_get_arg(1)] = func_get_arg(2);
				break;
		}
	}

	public function exists($name) {
		return isset($this->{$name});
	}

}