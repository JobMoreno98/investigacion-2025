<?php

namespace App\Services;

use App\Models\Sections;
use App\Models\Sistema;
use Illuminate\Support\Collection;

/**
 * Qué preguntas están disponibles en un sistema.
 *
 * Regla: una pregunta `sub_form` solo existe en los sistemas donde TAMBIÉN está asignada su
 * sección destino (options.target_section_id). En los demás no se muestra, no se valida, no se
 * guarda y lo que ya estuviera guardado de ese sub-formulario no se toca.
 *
 * Es la única implementación de la regla: la usan el request, el servicio y los controladores.
 */
class SistemaFilter
{
    /** @var array<string, array<int>> caché por instancia: "sistema:ids" => ids permitidos */
    private array $memo = [];

    /**
     * Devuelve las preguntas disponibles en el sistema (por defecto, el actual).
     * Conserva las llaves de la colección recibida (sirve tanto indexada por id como en lista).
     */
    public function questions(Collection $questions, ?string $sistema = null): Collection
    {
        $sistema ??= Sistema::slugActual();

        $targets = $questions
            ->where('type', 'sub_form')
            ->map(fn ($question) => (int) ($question->options['target_section_id'] ?? 0))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (! $targets) {
            return $questions;
        }

        $allowed = $this->sectionIdsInSistema($targets, $sistema);

        return $questions->reject(
            fn ($question) => $question->type === 'sub_form'
                && ! in_array((int) ($question->options['target_section_id'] ?? 0), $allowed, true)
        );
    }

    /**
     * De las secciones indicadas, las que pertenecen al sistema (y no están borradas).
     *
     * @param  array<int>  $sectionIds
     * @return array<int>
     */
    public function sectionIdsInSistema(array $sectionIds, string $sistema): array
    {
        sort($sectionIds);
        $key = $sistema . ':' . implode(',', $sectionIds);

        return $this->memo[$key] ??= Sections::query()
            ->forSistema($sistema)
            ->whereIn('id', $sectionIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}