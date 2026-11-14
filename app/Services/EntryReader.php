<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Entry;
use App\Models\Questions;
use Illuminate\Support\Collection;

/**
 * Lectura de respuestas entre secciones/formularios.
 *
 * Es la única puerta de entrada para preguntar "¿qué respondió este usuario en
 * aquella sección?". Siempre trabaja por lotes (nunca una consulta por pregunta).
 *
 * Úsalo resolviéndolo del contenedor en cada petición (app(EntryReader::class));
 * guarda una caché en memoria que vive solo lo que viva la instancia, así que NO
 * lo registres como singleton si vas a leer después de guardar en la misma petición.
 */
class EntryReader
{
    /** Alcance de una pregunta "reference": solo los registros del usuario, o las respuestas de todos. */
    public const SCOPE_USER = 'user';
    public const SCOPE_ALL = 'all';

    /** Tope de opciones en el alcance "all" (un select con más de esto ya no es usable). */
    private const MAX_ALL_OPTIONS = 1000;

    private array $entriesMemo = [];
    private array $questionsMemo = [];
    private array $allValuesMemo = [];

    /**
     * Registros de un usuario en una sección, del más antiguo al más reciente.
     *
     * @param  bool  $soloRaiz  true = ignora los sub-formularios (parent_entry_id no nulo)
     */
    public function entries(int $userId, int $sectionId, ?int $cicloId = null, bool $soloRaiz = true): Collection
    {
        $key = implode(':', [$userId, $sectionId, $cicloId ?? '*', (int) $soloRaiz]);

        return $this->entriesMemo[$key] ??= Entry::query()
            ->forUser($userId)
            ->inSection($sectionId)
            ->when($soloRaiz, fn ($q) => $q->roots())
            ->when($cicloId, fn ($q) => $q->where('ciclo_id', $cicloId))
            ->orderBy('id')
            ->get();
    }

    /** Para secciones no repetibles: el registro más reciente (o null). */
    public function entry(int $userId, int $sectionId, ?int $cicloId = null): ?Entry
    {
        return $this->entries($userId, $sectionId, $cicloId)->last();
    }

    /** Sub-formulario de una sección dentro de un registro padre. */
    public function child(Entry $parent, int $sectionId): ?Entry
    {
        return $parent->children->firstWhere('section_id', $sectionId);
    }

    /**
     * Respuestas de varios registros en UNA consulta.
     *
     * @param  iterable<int>  $entryIds
     * @param  array<int>|null  $questionIds  null o [] = todas las preguntas
     * @return Collection<int, Collection<int, Answer>>  entry_id => (question_id => Answer)
     */
    public function answersFor(iterable $entryIds, ?array $questionIds = null): Collection
    {
        $ids = collect($entryIds)->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Answer::query()
            ->whereIn('entry_id', $ids)
            ->when($questionIds, fn ($q) => $q->whereIn('question_id', $questionIds))
            ->get()
            ->groupBy('entry_id')
            ->map(fn (Collection $rows) => $rows->keyBy('question_id'));
    }

    /** Valor de UNA pregunta para un usuario (en cualquier sección). */
    public function value(int $userId, int $questionId, ?int $cicloId = null): mixed
    {
        return $this->values($userId, [$questionId], $cicloId)[$questionId] ?? null;
    }

    /**
     * Valores de varias preguntas (de una o varias secciones) para un usuario.
     * Consultas: 1 (preguntas) + 2 por cada sección distinta involucrada.
     *
     * @param  array<int>  $questionIds
     * @return array<int, mixed>  question_id => valor (los que no tengan respuesta no aparecen)
     */
    public function values(int $userId, array $questionIds, ?int $cicloId = null): array
    {
        $result = [];

        $questions = Questions::query()->whereIn('id', $questionIds)->get();

        foreach ($questions->groupBy('section_id') as $sectionId => $sectionQuestions) {
            $entry = $this->entry($userId, (int) $sectionId, $cicloId);

            if (! $entry) {
                continue;
            }

            $answers = $this->answersFor([$entry->id], $sectionQuestions->pluck('id')->all())->get($entry->id);

            foreach ($sectionQuestions as $question) {
                $answer = $answers?->get($question->id);

                if ($answer) {
                    $result[$question->id] = $this->decode($answer);
                }
            }
        }

        return $result;
    }

    /** Alcance configurado en la pregunta (options.scope). Por defecto "user", que es el seguro. */
    public function referenceScope(Questions $question): string
    {
        return ($question->options['scope'] ?? self::SCOPE_USER) === self::SCOPE_ALL
            ? self::SCOPE_ALL
            : self::SCOPE_USER;
    }

    /**
     * Opciones de una pregunta de tipo "reference", según options.scope:
     *
     *  - "user" (por defecto): registros del usuario indicado en options.source_section_id.
     *    Devuelve [entry_id => texto visible]; el texto sale de options.source_question_id.
     *  - "all": valores distintos que TODOS los usuarios respondieron en options.source_question_id.
     *    Devuelve [texto => texto]; no hay vínculo a un registro concreto.
     *
     * Sirve igual para llenar el select, validar (Rule::in sobre las llaves) y guardar.
     *
     * @return array<int|string, string>
     */
    public function referenceOptions(Questions $question, int $userId, ?int $cicloId = null): array
    {
        $displayQuestionId = (int) ($question->options['source_question_id'] ?? 0);

        if (! $displayQuestionId) {
            return [];
        }

        if ($this->referenceScope($question) === self::SCOPE_ALL) {
            return $this->allAnswerValues($displayQuestionId);
        }

        $sectionId = (int) ($question->options['source_section_id'] ?? 0)
            ?: (int) $this->question($displayQuestionId)?->section_id;

        if (! $sectionId) {
            return [];
        }

        $entries = $this->entries($userId, $sectionId, $cicloId);
        $answers = $this->answersFor($entries->pluck('id'), [$displayQuestionId]);

        return $entries
            ->mapWithKeys(fn (Entry $entry) => [
                $entry->id => $this->display($answers->get($entry->id)?->get($displayQuestionId)),
            ])
            ->all();
    }

    /**
     * Valores distintos de una pregunta entre todos los usuarios (sin registros borrados).
     *
     * @return array<string, string>
     */
    private function allAnswerValues(int $questionId): array
    {
        return $this->allValuesMemo[$questionId] ??= Answer::query()
            ->join('entries', 'entries.id', '=', 'answers.entry_id')
            ->whereNull('entries.deleted_at')
            ->where('answers.question_id', $questionId)
            ->whereNotNull('answers.value_text')
            ->where('answers.value_text', '!=', '')
            ->distinct()
            ->orderBy('answers.value_text')
            ->limit(self::MAX_ALL_OPTIONS)
            ->pluck('answers.value_text')
            ->mapWithKeys(fn ($text) => [(string) $text => (string) $text])
            ->all();
    }

    /**
     * Valor listo para usar: arreglo si la respuesta es compuesta (repeaters, scored_text...),
     * texto plano en cualquier otro caso. Mismo criterio que Answer::getFormattedValueAttribute.
     */
    public function decode(Answer $answer): mixed
    {
        $raw = $answer->value;

        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : $answer->value_text;
    }

    private function display(?Answer $answer): string
    {
        if (! $answer) {
            return '—';
        }

        $value = $this->decode($answer);

        return is_array($value)
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
    }
}