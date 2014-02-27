<?php

/**
 * Memproses model yang terdapat di dalam modul
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka;

use Djokka\Base;
use Djokka\Model\SchemaCollection;
use Djokka\Model\TableCollection;
use Djokka\Model\ModelCollection;
use Djokka\Model\Validation;
use Djokka\Helpers\String;

/**
 * Kelas pustaka yang bertugas untuk memproses dan mengendalikan model yang terdapat di dalam suatu modul
 */
abstract class Model extends Base
{
    /**
     * Data penting yang dibutuhkan oleh model
     */
    protected $____dataset = array(
        'is_new'=>true,
        'module'=>null,
        'params'=>array(),
        'externals'=>array(),
        'updates'=>array()
    );

    /**
     * Konstruktor kelas
     */
    public function __construct()
    {
        $this->preload();
    }

    /**
     * Fungsi yang digunakan untuk menetapkan nama tabel yang diwakili oleh model
     * @return string
     */
    public function table()
    {
    }

    /**
     * Fungsi yang digunakan untuk menetapkan label-label yang digunakan oleh model
     * @return array
     */
    public function labels()
    {
    }

    /**
     * Fungsi yang digunakan untuk menetapkan aturan-aturan validasi yang digunakan oleh model
     * @return string
     */
    public function rules()
    {
    }

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
                return $this->____dataset;
            case 1:
                if (isset($this->____dataset[func_get_arg(0)])) {
                    return $this->____dataset[func_get_arg(0)];
                }
                break;
            case 2:
                $this->____dataset[func_get_arg(0)] = func_get_arg(1);
                break;
        }
    }

    /**
     * Menentukan aturan validasi terhadap suatu model
     * @param mixed $field string Nama field yang akan diberi aturan
     * @param mixed $rules aturan validasi yang akan diberikan pada field tersebut
     * @param optional $params array Parameter yang dibutuhkan oleh aturan validasi yang digunakan
     */
    public function setRules($field, $rules, $params = array())
    {
        Validation::get()->setRules($field, $rules, $params);
    }

    /**
     * Mengambil teks label suatu properti/atribut/field model
     * @since 1.0.0
     * @param $property adalah properti/atribut/field model yang akan diakses
     * @return nilai properti/atribut/field
     */
    public function label($property = null)
    {
        if($this->schema() != null) {
            return isset($this->schema()->Labels[$property]) ? $this->schema()->Labels[$property] : ucfirst($property);
        } else {
            return ucfirst($property);
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
                if(!$this->isNew() && !in_array($key, $this->____dataset['updates'])) {
                    $this->____dataset['updates'][] = $key;
                }
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

    /**
     * Mengambil objek asli model
     * @param optional $is_new boolean Objek diambil sebagai data baru atau data lama
     */
    public function origin($is_new = false)
    {
        return self::getObject(get_class($this), $this->____dataset['module'], (bool)$is_new);
    }

    /**
     * Mengecek suatu properti model memiliki error atau tidak
     * @since 1.0.0
     * @return boolean status memiliki error atau tidak. Bernilai TRUE jika terdapat
     * error pada properti tersebut. Bernilai FALSE jika tidak terdapat error pada
     * properti tersebut
     */
    public function hasError()
    {
        return count($this->error()) > 0;
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
                return Validation::get()->errors;
            case 1:
                if (isset(Validation::get()->errors[func_get_arg(0)])) {
                    return Validation::get()->errors[func_get_arg(0)];
                }
                break;
            case 2:
                Validation::get()->errors[func_get_arg(0)] = func_get_arg(1);
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
        $validated = Validation::get()->validate($this);
        Validation::get()->clearRules();
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
                Validation::get()->unvalidates = array_merge(
                    Validation::get()->unvalidates,
                    array($property)
                );
            } else {
                Validation::get()->unvalidates = array_merge(
                    Validation::get()->unvalidates, $property
                );
            }
        } else {
            Validation::get()->unvalidate = true;
        }
    }
}