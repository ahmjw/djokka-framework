<?php

/**
 * Membentuk elemen HTML
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Helpers;

use Djokka\Base;

/**
 * Kelas pembantu yang bertugas untuk mempermudah pembentukan elemen HTML
 */
class Html extends Base
{

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
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
     * Membuat tag pembuka untuk suatu elemen HTML
     * @since 1.0.0
     * @param string $tag adalah nama tag HTML
     * @param array $args adalah parameter tambahan sebagai atribut elemen HTML
     * @param bool $has_closed Menandai suatu tag HTML memiliki penutup atau tidak
     * @return skrip HTML elemen
     */
    public function tag($tag, $args = array(), $has_closed = true)
    {
        if($has_closed)
            return "<$tag".$this->renderAttributes($args).">";
        else
            return "<$tag".$this->renderAttributes($args)." />";
    }

    /**
     * Membuat elemen drop-down list
     * @since 1.0.0
     * @param string $name adalah atribut nama untuk drop-down list
     * @param string $value adalah atribut nilai yang terpilih untuk drop-down list
     * @param array $items adalah daftar pilihan di dalam suatu drop-down list
     * @param array $options adalah parameter tambahan sebagai atribut elemen HTML
     * @param array $params adalah parameter untuk mengatur kinerja elemen
     * @return skrip HTML drop-down list
     */
    public function select($name, $value, $items, $options = array(), $params = array())
    {
        $options['name'] = $name;
        $params['auto_index'] = isset($params['auto_index']) ? $params['auto_index'] : true;
        $rendered = '';
        if($items) {
            foreach ($items as $values => $display) {
                if($params['auto_index'] === true) {
                    $values = !is_numeric($values) ? $values : $display;
                }
                $selected = ($values == $value) ? ' selected="selected"' : '';
                $rendered .= '<option value="'.$values.'"'.$selected.'>'.$display.'</option>';
            }
        }
        return $this->tag('select', $options).$rendered.'</select>';
    }

    /**
     * Membuat elemen radio button
     * @since 1.0.1
     * @param $name adalah atribut nama untuk radio button
     * @param $value adalah atribut nilai yang terpilih untuk radio button
     * @param $items adalah daftar pilihan di dalam suatu radio button
     * @param $options adalah parameter tambahan sebagai atribut elemen HTML
     * @return skrip HTML radio button
     */
    public function radio($name, $value, $items, $options = array())
    {
        $options['name'] = $name;
        $rendered = '';
        if($items) {
            foreach ($items as $values => $display) {
                $values = !is_numeric($values) ? $values : $display;
                $checked = ($values == $value) ? ' checked="checked"' : '';
                $rendered .= '<label class="radio"><input type="radio" name="'.$name.'" value="'.$values.'"'.$checked.'>'.$display.'</label>';
            }
        }
        return $rendered;
    }

    /**
     * Membuat elemen checkbox
     * @since 1.0.0
     * @param $name adalah atribut nama untuk elemen checkbox
     * @param $value adalah atribut nilai untuk elemen checkbox
     * @param $label adalah teks label untuk elemen checkbox
     * @param $options adalah parameter tambahan sebagai atribut elemen checkbox
     * @return skrip HTML elemen
     */
    public function checkbox($name, $value, $label, $options = array())
    {
        $options['name'] = $name;
        $options['type'] = 'checkbox';
        if((bool)$value) {
            $options['checked'] = 'checked';
        }
        return $this->tag('input', $options).' '.$label;
    }

    /**
     * Membentuk atribut elemen HTML yang dimasukkan dalam bentuk array
     * @since 1.0.0
     * @param $args adalah parameter-parameter yang hendak dibentuk
     * @return string hasil bentukan atribut HTML
     */
    private function renderAttributes($args = array())
    {
        if(empty($args)) {
            return;
        }
        $attr = null;
        foreach ($args as $key => $value) {
            $attr .= " $key=\"$value\"";
        }
        return $attr;
    }

}