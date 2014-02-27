<?php

namespace Djokka;

class DataForm extends Model
{
    /**
     * Memasukkan data model ke dalam pemetaan
     */
    private function preload()
    {
        if($this->labels() != null) {
            $this->schema('labels', $this->labels());
        }
    }
}