<?php

/**
 * Membentuk koleksi model guna optimasi sistem
 * @since 1.0.2
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 * @version 1.0.2
 */

namespace Djokka\Model;

use Djokka\Database\Connection;
use Djokka\Route;

/**
 * Kelas pendampingyang membantu kelas Djokka\Model untuk optimasi
 */
class ModelCollection
{
    /**
     * Jumlah baris yang dihasilkan dari hasil eksekusi perintah SQL
     */
    public $rowCount;

    /**
     * Jumlah kolom yang dihasilkan dari hasil eksekusi perintah SQL
     */
    public $fieldCount;

    /**
     * Perintah SQL
     */
    private $_sql;

    /**
     * Hasil eksekusi perintah SQL
     */
    private $_result;

    /**
     * Model yang akan dikoleksi
     */
    private $_model;

    /**
     * Konstruktor kelas
     */
    public function __construct($sql, $model)
    {
        $this->_sql = $sql;
        $this->_result = $model->getDriver('Connection')->query($sql);
        $this->rowCount = $this->_result->num_rows;
        $this->fieldCount = $this->_result->field_count;
        $this->_model = $model;
    }

    /**
     * Memasukkan koleksi model ke dalam suatu properti
     * @param string $property Nama properti/field
     * @return mixed
     */
    public function __get($property)
    {
        if ($property == 'rows') {
            $this->rows = array();
            while ($row = $this->_result->fetch_assoc()) {
                $record = clone $this->_model;
                foreach ($row as $key => $value) {
                    $record->{$key} = stripslashes($value);
                }
                $this->rows[] = $record;
            }
            $this->_result->free_result();
        }
        if (isset($this->{$property})) {
          return $this->{$property};
        }
    }

    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Mengambil hasil pembagi halaman
     */
    public function getPager()
    {
        return $this->_model->getPager();
    }

    /**
     * Menampilkan pembagi halaman
     */
    public function showPager()
    {
        if($this->rowCount == 0) return;
        $pager = $this->_model->getPager();
        list($page, $num_page, $total) = $pager;

        if ($num_page > 0) {
            echo '<p><b>Page:</b> ';
            for ($i = 1; $i <= $num_page; $i++) {
                if ($i != $page) {
                    echo '<a href="'.Route::getInstance()->getUrl().'?page='.$i.'">'.$i.'</a>';
                } else {
                    echo '<b>'.$i.'</b>';
                }
                if ($i < $num_page) {
                    echo ' | ';
                }
            }
            echo '</p>';
        }
    }

    /**
     * Menghapus sekelompok data
     */
    public function delete()
    {
        $this->_model->getDriver('Crud')->deleteImpl($this->_model->tableName(), $this->_model->dataset('condition'));
    }
}