<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Un sistema es una aplicación/contexto que consume formularios (p. ej. "sia", "investigacion").
 * Agregar uno nuevo es insertar una fila: no requiere migraciones ni columnas nuevas.
 */
class Sistema extends Model
{
    protected $guarded = [];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /** @var array<string, int|null> caché por petición: slug => id */
    private static array $idCache = [];

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public static function idFor(string $slug): ?int
    {
        return self::$idCache[$slug] ??= static::query()->where('slug', $slug)->value('id');
    }

    /** Sistema de esta instalación (config/sia.php => 'sistema'); por defecto "sia". */
    public static function slugActual(): string
    {
        return (string) config('sia.sistema', 'sia');
    }
}