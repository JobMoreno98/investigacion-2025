<?php

namespace App\Models;

use App\Models\Concerns\HasSistemas;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    use HasSistemas, HasSlug;

    const DATOS_GENERALES = 'Datos Generales';
    const SLUG_DATOS_GENERALES = 'datos-generales';

    protected $guarded = [];

    // Columna heredada: se conserva hasta eliminarla en la fase de limpieza.
    protected $casts = [
        'sistema' => 'array',
    ];

    protected function sistemaPivotTable(): string
    {
        return 'categoria_sistema';
    }

    protected function sistemaPivotKey(): string
    {
        return 'categoria_id';
    }

    protected function slugSource(): string
    {
        return 'titulo';
    }

    public function secciones()
    {
        return $this->hasMany(Sections::class, 'categoria_id');
    }

    public function isDatosGenerales(): bool
    {
        return $this->slug === self::SLUG_DATOS_GENERALES
            || $this->titulo === self::DATOS_GENERALES;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}