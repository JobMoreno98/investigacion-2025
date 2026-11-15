<?php

namespace App\Models;

use App\Models\Concerns\HasSistemas;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sections extends Model
{
    use HasSistemas, HasSlug, SoftDeletes;

    const EVALUACION_NUEVO = 'Evaluaciones Nuevo';
    const EVALUACION_CONTINUACION = 'Evaluaciones Continuación';
    const PROYECTOS = 'Proyectos de Investigación';
    const ASIGNACIONES = 'ASIGNACIONES';

    // Identificadores estables (se generaron del título original al migrar).
    // Editar el título de la sección ya no rompe estas búsquedas.
    const SLUG_EVALUACION_NUEVO = 'evaluaciones-nuevo';
    const SLUG_EVALUACION_CONTINUACION = 'evaluaciones-continuacion';
    const SLUG_PROYECTOS = 'proyectos-de-investigacion';
    const SLUG_ASIGNACIONES = 'asignaciones';

    protected $guarded = [];

    protected $casts = [
        'investigacion' => 'boolean',
    ];

    /** @var array<string, int> caché por petición: slug => id */
    private static array $idCache = [];

    /* ---------------- Pertenencia a sistemas / slug ---------------- */

    protected function sistemaPivotTable(): string
    {
        return 'section_sistema';
    }

    protected function sistemaPivotKey(): string
    {
        return 'section_id';
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    /* ---------------- Relaciones ---------------- */

    public function categorias()
    {
        return $this->belongsTo(Categorias::class, 'categoria_id');
    }

    public function questions()
    {
        return $this->hasMany(Questions::class, 'section_id')->orderBy('sort_order');
    }

    public function entries()
    {
        return $this->hasMany(Entry::class, 'section_id');
    }

    /* ---------------- Registros del usuario logueado en esta sección ---------------- */

    public function getUserEntriesAttribute()
    {
        return $this->entriesOfCurrentUser();
    }

    // Misma consulta que getUserEntriesAttribute (el nombre prometía "todos", pero siempre filtró por usuario).
    public function getAllEntriesAttribute()
    {
        return $this->entriesOfCurrentUser();
    }

    private function entriesOfCurrentUser()
    {
        // Filtra por section_id (no por título). Con ONLY_FULL_GROUP_BY activo, esta consulta
        // agrupada sobre la vista puede fallar; el reemplazo natural es $this->entries()->forUser()->roots().
        return AnswerFullView::where('user_id', Auth::id())
            ->where('section_id', $this->id)
            ->groupBy('entry_id')
            ->orderBy('fecha_creado')
            ->get();
    }

    /* ---------------- Secciones especiales ---------------- */

    public static function idEvaluacionNuevo(): int
    {
        return self::idDe(self::SLUG_EVALUACION_NUEVO, self::EVALUACION_NUEVO);
    }

    public static function idEvaluacionContinuacion(): int
    {
        return self::idDe(self::SLUG_EVALUACION_CONTINUACION, self::EVALUACION_CONTINUACION);
    }

    public static function idAsignaciones(): int
    {
        return self::idDe(self::SLUG_ASIGNACIONES, self::ASIGNACIONES);
    }

    public static function idProyectos(): int
    {
        return self::idDe(self::SLUG_PROYECTOS, self::PROYECTOS);
    }

    /** Busca por slug (con respaldo por título si la sección aún no tiene slug) y falla con un mensaje claro. */
    private static function idDe(string $slug, string $titulo): int
    {
        return self::$idCache[$slug] ??= (int) (
            static::idBySlug($slug)
            ?? static::query()->where('title', $titulo)->value('id')
            ?? throw new \RuntimeException("No existe la sección «{$titulo}».")
        );
    }
}