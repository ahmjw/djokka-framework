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
 * Kelas Djokka\String adalah kelas pustaka framework. Dipergunakan untuk mengakses,
 * mengelola, dan memanipulasi data sesi pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class String
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
     * Memecah karakter menjadi dua bagian dan memberi nilai balik kedua bagian tersebut
     * @since 1.0.0
     * @param $symbol adalah karakter pembatas string
     * @param $text adalah teks yang akan dipecah
     * @return string
     */
    public function splitLast($symbol, $text)
    {
        $pos = strrpos($text, $symbol);
        return array(
            substr($text, 0, $pos),
            substr($text, $pos + 1, strlen($text))
        );
    }

    /**
     * Mengambil potongan belakang berdasarkan simbol pemisah
     * @since 1.0.0
     * @param $symbol adalah karakter pembatas string
     * @param $text adalah teks yang akan dipecah
     * @return string potongan
     */
    public function lastPart($symbol, $text)
    {
        return substr($text, strrpos($text, $symbol) + 1, strlen($text));
    }

    /**
     * Mengambil potongan depan berdasarkan simbol pemisah
     * @since 1.0.0
     * @param $symbol adalah karakter pembatas string
     * @param $text adalah teks yang akan dipecah
     * @return string potongan
     */
    public function unlastPart($symbol, $text)
    {
        return substr($text, 0, strrpos($text, $symbol));
    }

    /**
     * Mengubah suatu teks menjadi format yang sesuai SEO (Search Engine Optimization)
     * @since 1.0.0
     * @param $text adalah teks masukan
     * @return string hasil format
     */
    public function slugify($text)
    {
      return strtolower(trim(preg_replace('/\W+/', '-', $text), '-'));
    }

    /**
     * Mengubah suatu string menjadi format lokasi folder yang benar
     * @since 1.0.0
     * @param $path adalah string lokasi folder yang akan diformat
     * @return string hasil format
     */
    public function realPath($path)
    {
        return str_replace('\\', DS, str_replace('/', DS, $path));
    }

    public function replaceWith($text, $symbol, $params = array())
    {
        $i = 0;
        return preg_replace_callback('/\''.$symbol.'/i', function($matches) use($params, &$i) {
            $i++;
            return $params[$i-1];
        }, $text);
    }

}