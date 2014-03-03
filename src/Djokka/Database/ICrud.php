<?php

/**
 * Antarmuka yang harus dimiliki oleh pengeksekusi CRUD
 * @since 1.0.3
 * @author Ahmad Jawahir <rawndummy@gmail.com>
 * @link http://www.djokka.com
 * @license http://creativecommons.org/licenses/by-nc-sa/4.0/deed.en_US
 * @copyright Copyright &copy; 2013 Djokka Media
 */

namespace Djokka\Database;

/**
 * Antarmuka penghubung antara model dengan driver database pengakses CRUD
 */
interface ICrud
{
	/**
     * Mengambil instance secara Singleton Pattern
     * @since 1.0.3
     * @return objek instance kelas
     */
	public static function getInstance();

	/**
     * Mengambil satu record/baris dari suatu tabel menggunakan model
     * @param string $tableName Nama tabel
     * @param string $primary_key Kunci utama tabel
     * @param array $params Parameter tambahan untuk penyaringan data
     * @since 1.0.3
     * @return object
     */
	public function findImpl($model, $params);

	/**
     * Mengambil nilai field dari perintah SQL yang menyaring satu field
     * @param string $tableName Nama tabel
     * @param string $primary_key Kunci utama tabel
     * @param array $params Parameter tambahan untuk penyaringan data
     * @since 1.0.3
     * @return mixed
     */
	public function findDataImpl($tableName, $primary_key, $params);

	/**
     * Mengambil lebih dari satu record/baris dari suatu tabel menggunakan model
     * @param object $model Object model
     * @param array $params Parameter tambahan untuk mengatur data yang dihasilkan
     * @since 1.0.3
     * @return array
     */
	public function findAllImpl($model, $params);

     public function deleteImpl($tableName, $condition);

     public function updateImpl($model, array $availables = array());

     public function insertImpl($model, array $availables = array());

	/**
     * Mengambil data pembagian halaman
     * @param object $model Object model
     * @return array
     */
	public function getPagerImpl($model);
}