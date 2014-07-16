<?php

/**
 * Memproses bagian view
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.1
 */

namespace Djokka;

class ViewData
{
	public $name;

	public $vars;

	public function __construct($name, $vars)
	{
		$this->name = $name;
		$this->vars = $vars;
	}
}