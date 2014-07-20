<?php

/**
 * Melakukan validasi data model
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Model;

use Djokka\Database\Connection;

/**
 * Kelas pendamping yang membantu kelas Djokka\Model untuk melakukan validasi terhadap data model
 */
class Validation
{
    /**
     * Daftar error yang dihasilkan
     */
    public $errors = array();

    public $success = array();

    /**
     * Daftar aturan validasi yang telah dimuat
     */
    public $rules = array();

    /**
     * Daftar nama field yang validasinya akan diabaikan
     */
    public $unvalidates = array();

    /**
     * Menandai validasi diaktifkan atau tidak
     */
    public $unvalidate = false;

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$instance == null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Memasukkan aturan validasi untuk model
     * @param mixed $field string Nama field
     * @param mixed $rules string Nama aturan validasi
     * @param optional $params array Parameter yang dibutuhkan untuk aturan validasi yang digunakan
     */
    public function setRule($field, $rules, $params)
    {
        $this->rules[] = array_merge($this->rules, array_merge(array(
            $field, $rules
        ), $params));
    }

    /**
     * Membersihkan daftar aturan validasi
     */
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
        if (is_array($rules)) {
            if (!empty($this->rules)) {
                $rules = array_merge($this->rules, $rules);
            }
        }
        $this->rules = $rules;
        if($rules === null) return true;

        // Memecah aturan validasi
        foreach ($rules as $rule) {
            if ($rule === null) continue;
            $fields = $rule[0];
            $action = $rule[1];
            $params = array_slice($rule, 2);
            
            if(preg_match('/,/i', $rule[1])) {
                foreach (explode(',', $rule[1]) as $action) {
                    $this->execute($model, $action, $fields, $params);
                }
            } else {
                $this->execute($model, $action, $fields, $params);
            }
        }
        return !$model->hasError();
    }

    /**
     * Melakukan validasi terhadap suatu model
     * @param mixed $model object Objek model yang akan divalidasi
     * @param mixed $action string Nama aturan validasi yang digunakan
     * @param mixed $fields string Nama field pada model yang akan divalidasi
     * @param mixed $params array Parameter yang dibutuhkan oleh aturan validasi
     * @throws \Exception Jika nama aturan validasi tidak tersedia
     */
    private function execute($model, $action, $fields, $params)
    {
        // Searching single validation with parameters
        if(preg_match('/^([a-zA-Z][a-zA-Z0-9]*)\((.*)\)$/i', $action, $match)) {
            $action = $match[1];
            $params = explode(',', $match[2]);
        }
        $action = trim($action);

        // If no validation
        if(!in_array($action, get_class_methods($this))) {
            if(in_array($action, get_class_methods($model))) {
                // Reading all fields
                foreach (explode(',', $fields) as $field) {
                    $field = trim($field);
                    // Skip when any conditions
                    if(($this->unvalidates && in_array($field, $this->unvalidates)) ||
                        $field == '' || $model->error($field) != null) continue;
                    // Call validation function inside model
                    call_user_func_array(array($model, $action), array(
                        'field'=>$field,
                        'params'=>$params
                    ));
                }
            } else {
                throw new \Exception("No validation with name $action", 500);
            }
        } else {
            // Reading all fields
            foreach (explode(',', $fields) as $field) {
                $field = trim($field);
                // Skip when any conditions
                if(($this->unvalidates && in_array($field, $this->unvalidates)) ||
                    $field == '' || $model->error($field) != null) continue;
                // Call validation function
                call_user_func_array(array($this, $action), array(
                    'model'=>$model,
                    'field'=>$field,
                    'params'=>$params
                ));
            }
        }
    }

    /**
     * Melakukan validasi panjang data field pada model
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return boolean status validasi
     */
    private function length($model, $field, $params = array())
    {
        $stop = false;
        if (isset($params['exact'])) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is more than max value';
            $model->error($field, $message);
            $stop = true;
        }
        if (isset($params['max']) && strlen($model->{$field}) > $params['max']) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is more than max value';
            $model->error($field, $message);
            $stop = true;
        }
        if (isset($params['min'])) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is more than max value';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field kosong atau tidak
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return boolean status validasi
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
     * Melakukan validasi apakah suatu field termasuk angka atau bukan
     * @since 1.0.2
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return boolean status validasi
     */
    private function numeric($model, $field, $params = array())
    {
        if(!is_numeric($model->{$field})) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' only allow numeric value';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field termasuk huruf atau bukan
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return boolean status validasi
     */
    private function alphabet($model, $field, $params = array())
    {
        if(is_numeric($model->{$field})) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' only allow alphabet value';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field tidak melebihi panjang yang ditentukan
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return boolean status validasi
     */
    private function maxLength($model, $field, $params = array())
    {
        if(strlen($model->{$field}) > $params[0]) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is over '.$params[0].' characters';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field cocok dengan pola yang dimasukkan
     * @since 1.0.1
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @return status validasi
     */
    private function regex($model, $field, $params = array())
    {
        preg_match($params[0], $model->{$field}, $match);
        if (empty($match) || $model->{$field} != $match[0]) {
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is not match with pattern';
            $model->error($field, $message);
        }
    }

    /**
     * Melakukan validasi apakah suatu field memiliki nilai yang sama dengan field lain yang ditentukan
     * @since 1.0.0
     * @param $model adalah objek model yang akan divalidasi
     * @param $field adalah field/properti model
     * @param $params adalah parameter yang dimasukkan dalam aturan validasi
     * @access private
     * @return boolean status validasi
     */
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
        if($model->isNew() && $model->count(array($field . ' = ?', $model->{$field})) > 0) {
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
            $message = isset($params['message']) ?
                str_replace('{attr}', $model->label($field), $params['message']) :
                $model->label($field).' is not valid email format';
            $model->error($field, $message);
        }
    }

}