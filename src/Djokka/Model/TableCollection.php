<?php

/**
 * Membentuk koleksi tabel guna optimasi sistem
 * @since 1.0.2
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.2
 */

namespace Djokka\Model;

/**
 * Kelas pendampingyang membantu kelas Djokka\Model untuk optimasi
 */
class TableCollection
{

    /**
     * Menampung instance dari kelas
     * @since 1.0.2
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.2
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

    /**
     * Mengakses koleksi struktur tabel
     */
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

	/**
     * Mengecek apakah tabel telah dimuat atau belum
     * @param string $name Nama tabel
     * @return boolean
     */
	public function exists($name) {
		return isset($this->{$name});
	}

}