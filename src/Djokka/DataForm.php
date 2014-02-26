<?php

namespace Djokka;

class DataForm
{
	/**
     * Mengambil instance suatu model dari peta model
     * @param mixed $class Nama kelas model
     * @param mixed $module Nama modul tempat meletakkan model tersebut
     * @param optional $is_new apakah model akan dimuat sebagai data baru atau data lama
     * @return object
     */
    private static function getObject($class, $module)
    {
        $schema = SchemaCollection::get();
        if(!$schema->existsModel($class)) {
            $object = new $class;
            $object->____dataset['module'] = $module;
            $schema->models($module, $object);
        } else {
            $object = $schema->models($module);
        }
        return $object;
    }
}