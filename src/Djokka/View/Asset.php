<?php

/**
 * Mengelola aset web
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\View;

use Djokka\Helpers\String;
use Djokka\Controller;

/**
 * Kelas pendamping yang membantu kelas Djokka\View untuk mengelola aset web
 */
class Asset
{
    /**
     * Daftar berkas Javascript yang akan diletakkan pada kepala HTML
     */
    private $header_script_files = array();

    /**
     * Daftar berkas CSS yang akan diletakkan pada kepala HTML
     */
    private $header_stylesheet_files = array();
    
    /**
     * Daftar kode Javascript yang akan diletakkan pada kepala HTML
     */
    private $header_stylesheet = array();
    
    /**
     * Daftar kode Javascript yang akan diletakkan pada kepala HTML
     */
    private $header_scripts = array();
    
    /**
     * Daftar berkas Javascript yang akan diletakkan pada kaki HTML
     */
    private $footer_script_files = array();
    
    /**
     * Daftar kode Javascript yang akan diletakkan pada kepala HTML
     */
    private $footer_scripts = array();
    
    /**
     * Daftar widget yang akan diletakkan pada badan HTML
     */
    private $widgets = array();
    
    /**
     * Daftar teks yang akan diletakkan pada badan HTML
     */
    private $dom_append = array();

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
     * Mengosongkan semua data aset
     */
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
     * @param mixed $element adalah ID elemen HTML
     * @param mixed $content adalah konten yang akan ditambahkan
     */
    public function append($element, $content)
    {
        if(isset($this->dom_append[$element])) {
            $this->dom_append[$element][] = $content;
        } else {
            $this->dom_append[$element] = array($content);
        }
    }

    /**
     * Menambahkan/menempelkan konten dari suatu plugin/widget ke dalam suatu HTML menggunakan objek DOM
     * @since 1.0.0
     * @param string $element ID elemen HTML
     * @param array $items Daftar nama widget/plugin
     */
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
        libxml_use_internal_errors(true);
        Dom::getInstance()->loadHTML($content);
        libxml_clear_errors();
        $header = Dom::getInstance()->getElementsByTagName('head')->item(0);
        $body = Dom::getInstance()->getElementsByTagName('body')->item(0);

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
                    Dom::getInstance()->append($elements, 
                        Controller::getInstance()->import($params, null, true)
                    );
                } else {
                    Dom::getInstance()->append($elements, 
                        Controller::getInstance()->import($module, $params, true)
                    );
                }
            }
        }

        // For DOM append
        foreach ($this->dom_append as $element => $items) {
            foreach ($items as $item) {
                Dom::getInstance()->append($element, $item);
            }
        }

        return Dom::getInstance()->saveHTML();
    }

}