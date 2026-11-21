<?php

namespace App\Models;

use App\Models\Concerns\HasSistemas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    /**
     * Alias de sistemas() que NO choca con la columna heredada `ciclos.sistemas`.
     * En un registro cargado, $ciclo->sistemas devuelve la columna (el JSON), no la relación; Filament y
     * cualquier código que lea la relación como propiedad deben usar este nombre.
     * Se elimina cuando se borre la columna vieja.
     */
    public function sistemasPivot(): BelongsToMany
    {
        return $this->sistemas();
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