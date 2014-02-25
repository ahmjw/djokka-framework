<?php

/**
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://www.djokka.com?r=index/license
 * @copyright Copyright &copy; 2013 Djokka Media
 * @package \Djokka\
 * @version 1.0.0
 */

namespace Djokka\Model;

use Djokka\Base;
use Djokka\Db;

/**
 * Kelas Djokka\Config adalah kelas pustaka framework. Dipergunakan untuk mengatur
 * konfigurasi yang digunakan pada web
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @since 1.0.0
 */
class Pager extends Base
{

    /**
     * @var Menampung data konfigurasi web
     * @access private
     * @since 1.0.0
     */
    private $data = array();

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

    public function result() {
        $pager = $this->config('pager');
        $field = isset($pager['select']) ? $pager['select'] : $this->defval($pager['primary'], '*');
        $table = isset($pager['from']) ? $pager['from'] : $pager['table'];
        if(isset($pager['where'])) {
            $where = $pager['where'];
            $sql = "SELECT $field FROM $table";
            if(!is_array($where)) {
                $sql .= ' WHERE '.$where;
            } else {
                $sql .= ' WHERE '.$this('Db')->where($where)->Where;
            }
        } else {
            $sql = "SELECT $field FROM $table";
        }
        $resource = Db::get()->query($sql);
        $total = $resource->num_rows;
        $num_page = ceil($total / $pager['limit']);
        return array($pager['page'], $num_page, $total);
    }

    /**
     * Membentuk informasi paginasi suatu record-record model
     * @since 1.0.0
     * @param $limit adalah nilai batasan maksimum jumlah record per halaman
     * @param $page adalah rute masukan untuk membaca halaman mana yang sedang dibuka
     * @return informasi paginasi record-record
     */
    public function init($args = array())
    {
        list($limit, $page) = $args;
        if($page == null && isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $page = empty($page) || $page == 0 ? 1 : $page;
        if(empty($page)) {
            $offset = 0;
        } else {
            $offset = ($page - 1) * $limit;
        }
        $this->config('pager', array(
            'limit'=>$limit,
            'offset'=>$offset,
            'page'=>$page
        ));
        return $offset.', '.$limit;
    }

}