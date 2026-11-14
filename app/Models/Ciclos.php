<?php

namespace App\Models;

use App\Models\Concerns\HasSistemas;
use Illuminate\Database\Eloquent\Model;

class Ciclos extends Model
{
    use HasSistemas;
    protected $guarded = [];
    protected $casts = [
        'sistemas' => 'array',
        'activo' => 'boolean'
    ];


    protected function sistemaPivotTable(): string
    {
        return 'ciclo_sistema';
    }
    protected function sistemaPivotKey(): string
    {
        return 'ciclo_id';
    }
}
