<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka\Model;

/**
 * Kelas Djokka\Validation adalah kelas pustaka framework. Dipergunakan untuk melakukan
 * validasi terhadap model
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Validation extends \Djokka
{
    public $errors = array();

    public $rules = array();

    public $unvalidates = array();

    public $unvalidate = false;

    /**
     * @var Menampung instance dari kelas
     * @access private
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
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

    public function setRules($field, $rules, $params)
    {
        $this->rules[] = array_merge($this->rules, array_merge(array(
            $field, $rules
        ), $params));
    }

    public function clearRules()
    {
        unset($this->rules);
    }

    /**
     * Melakukan validasi terhadap model
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @return boolean status hasil validasi, bernilai TRUE jika model valid, bernilai
     * FALSE jika model tidak valid
     */
    public function validate($model)
    {
        // Mengumpulkan informasi validasi
        if($this->unvalidate) return true;
        $rules = $model->rules();
        if($rules === null) return true;
        if(!empty($this->rules)) {
            $rules = array_merge($rules, $this->rules);
        }

        // Memecah aturan validasi
        foreach ($rules as $rule) {
            $fields = $rule[0];
            $action = $rule[1];
            $params = array_slice($rule, 2);
            if(!in_array($action, get_class_methods($this))) {
                if(in_array($action, get_class_methods($model))) {
                    // Melakukan validasi
                    foreach (explode(',', $fields) as $field) {
                        $field = trim($field);
                        if(($this->unvalidates && in_array($field, $this->unvalidates)) ||
                            $field == '' || $model->error($field) != null) continue;
                        call_user_func_array(array($model, $action), array(
                            'field'=>$field,
                            'params'=>$params
                        ));
                    }
                } else {
                    throw new \Exception("No validation with name $action", 500);
                }
            } else {
                // Melakukan validasi
                foreach (explode(',', $fields) as $field) {
                    $field = trim($field);
                    if(($this->unvalidates && in_array($field, $this->unvalidates)) ||
                        $field == '' || $model->error($field) != null) continue;
                    call_user_func_array(array($this, $action), array(
                        'model'=>$model,
                        'field'=>$field,
                        'params'=>$params
                    ));
                }
            }
        }
        return !$model->hasError();
    }

    /**
     * Melakukan validasi panjang data field pada model
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return status validasi
     */
    private function length($model, $field, $params = array())
    {
    }

    /**
     * Melakukan validasi apakah suatu field kosong atau tidak
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return status validasi
     */
    private function required($model, $field, $params = array())
    {
        if(empty($model->{$field})) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is required';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field cocok dengan pola yang dimasukkan
     * @since 1.0.1
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return status validasi
     */
    private function regex($model, $field, $params = array())
    {
        preg_match($params[0], $model->{$field}, $match);
        if($model->{$field} != $match[0]) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is not match with pattern';
            $model->error($field, $message);
        }
    }

    private function compare($model, $field, $params = array()) {
        if($model->{$field} != $params[0]) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is different';
            $model->error($field, $message);
        }   
    }

    /**
     * Melakukan validasi apakah suatu field berisi data unik atau tidak
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return status validasi
     */
    private function unique($model, $field, $params = array())
    {
        $primary = $model->schema('primary');
        $sql = 'SELECT COUNT('.$primary.') AS count FROM '.$model->table().
            ' WHERE '.$field.' = ? AND '.$primary.' != ?';
        $exists = $this('Db')->getArray(array($sql, $model->{$field}, $model->{$primary}));
        if($exists['count'] > 0) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is must be unique';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field mengandung format e-mail yang benar
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return status validasi
     */
    private function email($model, $field, $params = array())
    {
        if(!filter_var($model->{$field}, FILTER_VALIDATE_EMAIL)) {
            $model->error($field, $model->label($field).' is not valid email format');
        }
    }

}