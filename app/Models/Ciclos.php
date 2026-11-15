<?php

namespace App\Models;

use App\Models\Concerns\HasSistemas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ciclos extends Model
{
    use HasSistemas;

    protected $guarded = [];

    // 'sistemas' es la columna heredada: se conserva hasta la fase de limpieza.
    protected $casts = [
        'sistemas' => 'array',
        'activo' => 'boolean',
    ];

    protected function sistemaPivotTable(): string
    {
        return 'ciclo_sistema';
    }

    protected function sistemaPivotKey(): string
    {
        return 'ciclo_id';
    }

    /** Ciclos activos cuya ventana de fechas incluye hoy. */
    public function scopeVigente(Builder $query): Builder
    {
        $hoy = today()->toDateString();

        return $query->where('activo', true)
            ->whereDate('inicio', '<=', $hoy)
            ->whereDate('fin', '>=', $hoy);
    }
}