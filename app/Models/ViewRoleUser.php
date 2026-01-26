<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewRoleUser extends Model
{
        // Apuntamos a la vista, no a una tabla física
    protected $table = 'view_roles_user';

    // Como es una vista, no se puede insertar/editar
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'user_id';
}
