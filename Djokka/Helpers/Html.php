<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka\Helpers;

/**
 * Kelas Djokka\Html adalah kelas pustaka framework. Dipergunakan untuk membantu
 * pembuatan elemen HTML
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Html
{

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

    /**
     * Membuat tag pembuka untuk suatu elemen HTML
     * @since 1.0.0
     * @param $tag adalah nama tag HTML
     * @param $args adalah parameter tambahan sebagai atribut elemen HTML
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
     * @param $name adalah atribut nama untuk drop-down list
     * @param $value adalah atribut nilai yang terpilih untuk drop-down list
     * @param $items adalah daftar pilihan di dalam suatu drop-down list
     * @param $options adalah parameter tambahan sebagai atribut elemen HTML
     * @return skrip HTML drop-down list
     */
    public function select($name, $value, $items, $options = array(), $params = array())
    {
        $options['name'] = $name;
        $params['auto_index'] = isset($params['auto_index']) ? $params['auto_index'] : true;
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