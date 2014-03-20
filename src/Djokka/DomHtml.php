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

class DomHtml extends \DomDocument
{
    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Menampung objek DOM pembantu
     * @since 1.0.0
     */
    private $helper;

    /**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.0
     * @param $class adalah nama kelas (opsional)
     * @return objek instance kelas
     */
    public static function getInstance()
    {
        if(self::$_instance == null) {
            self::$_instance = new static();
            self::$_instance->helper = new \DomDocument(1.0, 'UTF-8');
        }
        return self::$_instance;
    }

    /**
     * Menempelkan suatu konten pada suatu elemen HTML
     * @since 1.0.0
     * @param $element adalah ID elemen HTML tempat penempelan konten
     * @param $content adalah konten yang akan ditempelkan pada elemen
     */
    public function append($element, $content)
    {
        if($content == null) return;

        libxml_use_internal_errors(true);
        $this->helper->loadHTML($content);
        libxml_clear_errors();
        $pAttach = $this->helper->getElementsByTagName('body');
        $document = $this->getElementById($element);

        if($document == null) return;
        if ($pAttach->length) {
            for ($i = 0; $i < $pAttach->item(0)->childNodes->length; $i++) {
                $document->appendChild($this->importNode($pAttach->item(0)->childNodes->item($i), true));
            }
        }
    }
}