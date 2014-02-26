<?php

/**
 * Membantu melakukan pagination atau pembagian halaman
 * @since 1.0.0
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.0
 */

namespace Djokka\Model;

use Djokka\Base;
use Djokka\Db;

/**
 * Kelas pendamping yang membantu kelas Djokka\Model untuk membagi halaman
 */
class Pager extends Base
{

    /**
     * Menampung data konfigurasi web
     * @since 1.0.0
     */
    private $data = array();

    /**
     * Menampung instance dari kelas
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
     * Mengambil hasil output dari pembagian halaman
     * @since 1.0.0
     * @return array
     */
    public function result() 
    {
        $pager = $this->config('pager');
        $field = isset($pager['select']) ? $pager['select'] : $this->defval($pager['primary_key'], '*');
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
     * @param array $args Parameter yang dibutuhkan untuk pembagian halaman
     * @return string Informasi paginasi record-record
     */
    public function init($args = array())
    {
        if(count($args) == 2) {
            list($limit, $page) = $args;
            $page = !empty($page) ? $page : 1;
        } else {
            $limit = $args[0];
            $page = isset($_GET['page']) && !empty($_GET['page']) ? abs(intval($_GET['page'])) : 1;
        }
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