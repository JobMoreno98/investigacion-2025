<?php

namespace App\Models;

use App\Models\Concerns\HasSistemas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categorias extends Model
{
    use HasSistemas;
    protected $guarded = [];
    const DATOS_GENERALES = 'Datos Generales';


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
        return $this->titulo === self::DATOS_GENERALES;
    }
    protected $casts = [
        'sistema' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->slug = static::generateUniqueSlug($model->titulo);
        });

        static::updating(function ($model) {
            // Solo regenera el slug si el nombre cambió
            if ($model->isDirty('titulo')) {
                $model->slug = static::generateUniqueSlug($model->titulo, $model->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $titulo, $ignoreId = null): string
    {
        $slug = Str::slug($titulo);
        $originalSlug = $slug;
        $count = 1;

        $query = static::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
