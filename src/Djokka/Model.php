<?php

/**
 * Djokka Framework model class file
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 */

namespace Djokka;

use Djokka\Model\SchemaCollection;
use Djokka\Model\TableCollection;
use Djokka\Model\ModelCollection;
use Djokka\Model\Validation;
use Djokka\Helpers\String;

/**
 * This class is parent of all model classes including active record classes. This class
 * will provides the validation library for model.
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
abstract class Model extends Shortcut
{
    /**
     * Fungsi yang digunakan untuk menetapkan label-label yang digunakan oleh model
     * @return array
     */
    abstract function labels();

    /**
     * Fungsi yang digunakan untuk menetapkan aturan-aturan validasi yang digunakan oleh model
     * @return string
     */
    abstract function rules();

    /**
     * Mengambil dan mengubah informasi yang terkandung di dalam suatu model
     * @since 1.0.0
     * @param - Jika tanpa parameter, maka nilai baliknya adalah semua informasi yang
     *   terkandung di dalam suatu model
     * - Jika memasukkan satu parameter, maka nilai baliknya adalah nilai informasi
     *   yang diambil berdasarkan nama atribut
     * - Jika memasukkan dua parameter, maka dia akan mengubah nilai suatu informasi
     *   berdasarkan atribut yang dimasukkan
     * @return nilai informasi model
     */
    public function dataset()
    {
        switch (func_num_args()) {
            case 0:
                return $this->_dataset;
            case 1:
                if (isset($this->_dataset[func_get_arg(0)])) {
                    return $this->_dataset[func_get_arg(0)];
                }
                break;
            case 2:
                $this->_dataset[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    /**
     * Menentukan aturan validasi terhadap suatu model
     * @param mixed $field string Nama field yang akan diberi aturan
     * @param mixed $rules aturan validasi yang akan diberikan pada field tersebut
     * @param optional $params array Parameter yang dibutuhkan oleh aturan validasi yang digunakan
     */
    public function setRule($field, $rules, $params = array())
    {
        Validation::getInstance()->setRule($field, $rules, $params);
    }

    /**
     * Mengambil teks label suatu properti/atribut/field model
     * @since 1.0.0
     * @param $property adalah properti/atribut/field model yang akan diakses
     * @return nilai properti/atribut/field
     */
    public function label($property = null)
    {
        $labels = $this->labels();
        if (!preg_match_all('/\[(.*?)\]/i', $property, $matches)) {
            return !empty($labels) && isset($labels[$property]) ? $labels[$property] : ucfirst($property);
        } else {
            preg_match('/^(.*?)\[/i', $property, $match);
            $data = null;
            $key_name = null;
            foreach ($matches as $key => $m) {
                $key_name = $matches[1][$key];
                if ($key == 0) {
                    $data = $labels[$match[1]][$key_name];
                } else {
                    $data = $data[$key_name];
                }
            }
            return $data === null ? ucfirst($key_name) : $data;
        }
    }

    /**
     * Memasukkan suatu nilai ke dalam properti model
     * @since 1.0.0
     * @param $data daftar pengisian nilai dalam bentuk array. Nama properti model
     * berlaku sebagai key/indeks dan nilai properti berlaku sebagai value
     * @param $clean untuk menentukan apakah pengisian nilai disertai penyaringan atau
     * tidak. Jika nilainya TRUE, maka pengisian nilai disertai penyaringan. Jika nilainya
     * FALSE, maka pengisian nilai tidak disertai penyaringan
     */
    public function input($data, $clean = true)
    {
        if(!empty($data)) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    /**
     * Mengosongkan nilai field/properti pada model
     */
    public function clear() 
    {
        foreach ($this as $key => $value) {
            $this->{$key} = null;
        }
    }

    public function showError(array $params = array())
    {
        if ($this->hasError()) {
            echo isset($params['open']) ? $params['open'] : '<ul>';
            foreach ($this->error() as $message) {
                echo '<li>' . $message . '</li>';
            }
            echo isset($params['close']) ? $params['close'] : '</ul>';
        }
    }

    public function showSuccess(array $params = array())
    {
        if ($this->isSuccess()) {
            echo isset($params['open']) ? $params['open'] : '<ul>';
            foreach ($this->success() as $message) {
                echo '<li>' . $message . '</li>';
            }
            echo isset($params['close']) ? $params['close'] : '</ul>';
        }
    }

    /**
     * Mengecek suatu properti model memiliki error atau tidak
     * @since 1.0.0
     * @return boolean status memiliki error atau tidak. Bernilai TRUE jika terdapat
     * error pada properti tersebut. Bernilai FALSE jika tidak terdapat error pada
     * properti tersebut
     */
    public function hasError($field = null)
    {
        if ($field === null) {
            return count($this->error()) > 0;
        } else {
            return isset(Validation::getInstance()->errors[$field]);
        }
    }

    public function isSuccess()
    {
        return count(Validation::getInstance()->success) > 0;
    }

    /**
     * Mengambil semua error atau error berdasarkan nama properti model
     * @since 1.0.0
     * @param $key adalah nama properti untuk menyaring error
     * @param $message adalah pesan error yang akan ditampilkan
     * @return informasi error atau void
     */
    public function error()
    {
        switch (func_num_args()) {
            case 0:
                return Validation::getInstance()->errors;
            case 1:
                if (isset(Validation::getInstance()->errors[func_get_arg(0)])) {
                    return Validation::getInstance()->errors[func_get_arg(0)];
                }
                break;
            case 2:
                Validation::getInstance()->errors[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    public function success()
    {
        switch (func_num_args()) {
            case 0:
                return Validation::getInstance()->success;
            case 1:
                Validation::getInstance()->success[] = func_get_arg(0);
                break;
        }
    }

    /**
     * Melakukan validasi terhadap model. Hasil validasi akan memberikan informasi error
     * ke dalam properti model dan memberikan status model valid atau tidak
     * @since 1.0.0
     * @return status validasi, bernilai TRUE jika model valid, bernilai FALSE jika
     * model tidak valid
     */
    public function validate()
    {
        $validated = Validation::getInstance()->validate($this);
        Validation::getInstance()->clearRules();
        return $validated;
    }

    /**
     * Melakukan validasi terhadap model. Hasil validasi akan memberikan informasi error
     * ke dalam properti model dan memberikan status model valid atau tidak
     * @param mixed $property Properti/field yang akan diabaikan dalam validasi
     * @since 1.0.1
     * @return status validasi, bernilai TRUE jika model valid, bernilai FALSE jika
     * model tidak valid
     */
    public function unvalidate($property = null)
    {
        if($property !== null) {
            if(!is_array($property)) {
                Validation::getInstance()->unvalidates = array_merge(
                    Validation::getInstance()->unvalidates,
                    array($property)
                );
            } else {
                Validation::getInstance()->unvalidates = array_merge(
                    Validation::getInstance()->unvalidates, $property
                );
            }
        } else {
            Validation::getInstance()->unvalidate = true;
        }
    }

    public function scriptValidate()
    {
        $script = '<script language="javascript">';
        $script .= Validation::getInstance()->getScript($this);
        $script .= '</script>';
        return $script;
    }
}