<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Un sistema es una aplicación/contexto que consume formularios (p. ej. "sia", "investigacion").
 * Agregar uno nuevo es insertar una fila: no requiere migraciones ni columnas nuevas.
 */
class Sistemas extends Model
{
    protected $guarded = [];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /** @var array<string, int|null> caché por petición: slug => id */
    private static array $idCache = [];

    /** Sistema fijado en esta petición (por middleware, prueba o job); null = usar la configuración. */
    private static ?string $fijado = null;

    public function categorias()
    {
        return $this->belongsToMany(Categorias::class, 'categoria_sistema', 'sistema_id', 'categoria_id');
    }

    public function secciones()
    {
        return $this->belongsToMany(Sections::class, 'section_sistema', 'sistema_id', 'section_id');
    }

    public function ciclos()
    {
        return $this->belongsToMany(Ciclos::class, 'ciclo_sistema', 'sistema_id', 'ciclo_id');
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public static function idFor(string $slug): ?int
    {
        return self::$idCache[$slug] ??= static::query()->where('slug', $slug)->value('id');
    }

    /**
     * Slug del sistema con el que trabaja este código. Orden de resolución:
     *   1) el fijado con fijar()/usando()  2) config('sia.sistema')  3) "sia".
     */
    public static function slugActual(): string
    {
        return self::$fijado ?? (string) config('sia.sistema', 'sia');
    }

    /** El modelo del sistema actual (o null si el slug no existe en la tabla). */
    public static function actual(): ?self
    {
        return static::query()->where('slug', self::slugActual())->first();
    }

    /**
     * Fija el sistema para lo que resta de la petición (middleware por dominio, prefijo o panel).
     * Con Octane/colas, llámalo en CADA petición (incluso con null) para no arrastrar el anterior.
     */
    public static function fijar(?string $slug): void
    {
        self::$fijado = $slug;
    }

    /** Ejecuta un bloque como si fuera otro sistema (pruebas, comandos, jobs) y restaura el anterior. */
    public static function usando(string $slug, callable $callback): mixed
    {
        $anterior = self::$fijado;
        self::$fijado = $slug;

        try {
            return $callback();
        } finally {
            self::$fijado = $anterior;
        }
    }
}