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
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.2
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$_instance == null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * Mengakses koleksi struktur tabel
     */
	public function table() {
		switch (func_num_args()) {
			case 1:
                if (isset($this->{func_get_arg(0)})) {
				    return $this->{func_get_arg(0)};
                }
                break;
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