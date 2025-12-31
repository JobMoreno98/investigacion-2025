<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewDatosGenerales extends Model
{
    // Apuntamos a la vista, no a una tabla física
    protected $table = 'view_datos_generales';

    // Como es una vista, no se puede insertar/editar
    public $incrementing = false;
    public $timestamps = false;
}
