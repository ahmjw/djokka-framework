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
 * Kelas Djokka\Mail adalah kelas pustaka framework. Dipergunakan untuk membantu
 * dalam pengiriman pesan e-mail menggunakan protokol IMAP
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Email
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
     * Mengirim pesan e-mail menggunakan protokol IMAP
     * @since 1.0.0
     * @param $from adalah alamat e-mail pengirim
     * @param $to adalah alamat e-mail penerima
     * @param $subject adalah subjek atau perihal pesan e-mail
     * @param $message adalah isi pesan e-mail
     * @param $headers adalah informasi tambahan yang hendak dimasukkan pada bagian
     * kepala pesan e-mail
     * @return status pengiriman pesan e-mail (sukses atau gagal)
     */
    public function send($from, $to, $subject, $message, $headers = array())
    {
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=iso-8859-1";
        $headers[] = "From: {$from}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "X-Mailer: PHP/".phpversion();
        if(is_array($to)) {
            $to = implode(',', $to);
        }
        if($success = mail($to, $subject, $message, implode('\r\n', $headers))) {
            return $success;
        } else {
            throw new \Exception("Failed to send an email", 500);
        }
    }

}