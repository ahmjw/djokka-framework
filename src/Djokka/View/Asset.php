<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka\View;

use Djokka\Base;
use Djokka\Helpers\String;
use Djokka\Controller;

/**
 * Kelas Djokka\Asset adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * aset yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Asset extends Base
{

    private $header_script_files = array();
    private $header_stylesheet_files = array();
    private $header_stylesheet = array();
    private $header_scripts = array();
    private $footer_script_files = array();
    private $footer_scripts = array();
    private $widgets = array();
    private $dom_append = array();

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

    public function clear() {
        foreach ($this as $key => $value) {
            $this->{$key} = array();
        }
    }

    /**
     * Menambahkan skrip CSS hanya pada halaman web yang sedang dibuka
     * @since 1.0.0
     * @param $code adalah kode CSS yang akan ditambahkan
     */
    public function css($code)
    {
        $this->header_stylesheet = array_merge($this->header_stylesheet, array($code));
    }

    /**
     * Menambahkan skrip Javascript hanya pada halaman yang sedang dibuka
     * @since 1.0.0
     * @param $code adalah kode Javascript yang akan dimasukkan
     * @param $is_header digunakan untuk menentukan apakah skrip diletakkan pada
     * bagian header atau footer. Jika bernilai TRUE, maka skrip akan diletakkan pada
     * bagian header, dan jika bernilai FALSE, maka skrip akan diletakkan pada bagian
     * footer
     */
    public function js($code, $is_header = false)
    {
        if($is_header === true) {
            $this->header_scripts = array_merge($this->header_scripts, array($code));
        } else {
            $this->footer_scripts = array_merge($this->footer_scripts, array($code));
        }
    }

    /**
     * Menambahkan/menempelkan konten ke dalam suatu HTML menggunakan objek DOM
     * @since 1.0.0
     * @param $element adalah ID elemen HTML
     * @param $content adalah konten yang akan ditambahkan
     */
    public function append($element, $content)
    {
        if(isset($this->dom_append[$element])) {
            $this->dom_append[$element][] = $content;
        } else {
            $this->dom_append[$element] = array($content);
        }
    }

    public function setWidget($element, $items) {
        if(is_array($items)) {
            if(isset($this->widgets[$element])) {
                $this->widgets[$element] = array_merge($this->widgets[$element], $items);
            } else {
                $this->widgets[$element] = $items;
            }
        } else {
            if(isset($this->widgets[$element])) {
                $this->widgets[$element][] = $items;
            } else {
                $this->widgets[$element] = array($items);
            }
        }
    }

    /**
     * Menambahkan link file CSS atau Javascript hanya pada halaman yang sedang dibuka
     * @since 1.0.0
     * @param $source adalah lokasi URL file CSS atau Javascript
     * @param $params adalah parameter tambahan untuk link file
     * @param $is_header digunakan untuk menentukan apakah skrip diletakkan pada
     * bagian header atau footer. Jika bernilai TRUE, maka skrip akan diletakkan pada
     * bagian header, dan jika bernilai FALSE, maka skrip akan diletakkan pada bagian
     * footer
     */
    public function add($source, $params = array(), $is_header = false)
    {
        if(preg_match('/\.(css|js)$/i', $source, $match)) {
            switch ($match[1]) {
                // Untuk Javascript
                case 'js':
                    if($is_header === true) {
                        $this->header_script_files = array_merge(
                            $this->header_script_files, array($source => $params)
                        );
                    } else {
                        $this->footer_script_files = array_merge(
                            $this->footer_script_files, array($source => $params)
                        );
                    }
                    break;
                // Untuk CSS
                case 'css':
                    $this->header_stylesheet_files = array_merge(
                        $this->header_stylesheet_files, array($source => $params)
                    );
                    break;
            }
        } else {
            throw new \Exception("Asset file is not supported", 500);
        }
    }

    /**
     * Memuat/membaca semua widget dan objek DOM
     * @since 1.0.0
     * @param $content adalah konten web yang telah disatukan
     */
    public function render($content)
    {
        if(empty($content)) throw new \Exception("Theme has no content or theme is empty", 500);
        
        // Membuat objek DOM
        @Dom::get()->loadHTML($content);
        $header = Dom::get()->getElementsByTagName('head')->item(0);
        $body = Dom::get()->getElementsByTagName('body')->item(0);

        // Render header's assets
        // For JS files
        foreach ($this->header_script_files as $file => $params) {
            $script = $header->appendChild(new \DomElement('script'));
            $script->setAttribute('language', 'javascript');
            $script->setAttribute('src', $file);
        }

        // For CSS files
        foreach ($this->header_stylesheet_files as $file => $params) {
            $link = $header->appendChild(new \DomElement('link'));
            $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('type', 'text/css');
            $link->setAttribute('href', $file);
        }

        // For styles
        if(!empty($this->header_stylesheet)) {
            $style = $header->appendChild(new \DomElement('style'));
            $style->setAttribute('type', 'text/css');
            $value = null;
            foreach ($this->header_stylesheet as $code) {
                $value .= $code;
            }
            $style->nodeValue = $value;
        }

        // For scripts
        if(!empty($this->header_scripts)) {
            $script = $header->appendChild(new \DomElement('script'));
            $script->setAttribute('language', 'javascript');
            $value = null;
            foreach ($assets as $code) {
                $value .= $code;
            }
            $script->nodeValue = $value;
        }


        // Render footer's assets
        // For JS files
        foreach ($this->footer_script_files as $file => $params) {
            $script = $body->appendChild(new \DomElement('script'));
            $script->setAttribute('language', 'javascript');
            $script->setAttribute('src', $file);
        }

        // For scripts
        if(!empty($this->footer_scripts)) {
            $script = $body->appendChild(new \DomElement('script'));
            $script->setAttribute('language', 'javascript');
            $value = null;
            foreach ($this->footer_scripts as $code) {
                $value .= $code;
            }
            $script->nodeValue = $value;
        }

        // For widgets
        foreach ($this->widgets as $elements => $items) {
            foreach ($items as $module => $params) {
                if(is_numeric($module)){
                    Dom::get()->append($elements, 
                        Controller::get()->import('plugin.'.$params)
                    );
                } else {
                    Dom::get()->append($elements, 
                        Controller::get()->import('plugin.'.$module, $params)
                    );
                }
            }
        }

        // For DOM append
        foreach ($this->dom_append as $element => $items) {
            foreach ($items as $item) {
                Dom::get()->append($element, $item);
            }
        }

        return Dom::get()->saveHTML();
    }

}