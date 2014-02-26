<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\View;

/**
 * Kelas Djokka\Dom adalah kelas pustaka framework. Dipergunakan untuk keperluan akses
 * objek DOM pada web
 * @since 1.0.0
 */
class Dom extends \DomDocument
{

    /**
     * Menampung instance dari kelas
     * @since 1.0.0
     */
    private static $instance;

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
    public static function get($class = __CLASS__)
    {
        if(self::$instance == null) {
            self::$instance = new $class;
            self::$instance->helper = new \DomDocument(1.0, 'UTF-8');
        }
        return self::$instance;
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