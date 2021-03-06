<?php

/**
 * This file is generated by Djokka Framework IDE
 */
namespace Djokka\Models;

use Djokka\ActiveRecord;

/**
 * This class is abstraction of table '(=tableName)'
 */
class (=className) extends ActiveRecord
{
	/**
	 * Declare the table name
	 * @return string
	 */
	public function table()
	{
		return '(=tableName)';
	}(=others)

	/**
	 * Declare form validation rules for model
	 * @return array
	 */
	public function rules()
	{
		(=rules)
	}

	/**
	 * Declare the labels for each property of model
	 * @return array
	 */
	public function labels()
	{
		(=labels)
	}
}