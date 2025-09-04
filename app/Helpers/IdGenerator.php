<?php

namespace App\Helpers;

class IdGenerator
{
    public static function generate($model, $prefix, $pad_length = 2)
    {
       // Hitung total record yang sudah ada di tabel model tersebut
        $count = $model::count() + 1;
        return $prefix . str_pad($count, $pad_length, '0', STR_PAD_LEFT);
        
    }
}
