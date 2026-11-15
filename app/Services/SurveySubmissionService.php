<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Entry;
use App\Models\Questions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SurveySubmissionService
{
    /** @var array<int, Collection> preguntas por sección, indexadas por id (caché por petición) */
    private array $questionsBySection = [];

    public function __construct(private EntryReader $reader, private SistemaFilter $sistemaFilter)
    {
    }

    /**
     * Guarda o actualiza las respuestas de un registro y de sus sub-formularios.
     *
     * Las preguntas que se guardan salen SIEMPRE de la base de datos (las de la sección del
     * registro), nunca de las llaves que mande el cliente.
     */
    public function saveAnswers(Entry $mainEntry, array $validated, Request $request): void
    {
        $answers = $validated['answers'] ?? [];
        $subAnswers = $validated['sub_answers'] ?? [];

        $sectionId = $this->resolveSectionId($mainEntry, $answers);

        if (! $sectionId) {
            throw ValidationException::withMessages([
                'section_ids' => 'No se pudo determinar la sección de este registro.',
            ]);
        }

        $questions = $this->questionsOf($sectionId);
        $existing = $this->existingAnswers($mainEntry);

        // 1. Respuestas del registro principal
        foreach ($questions as $question) {
            // El vínculo con el sub-formulario lo escribe resolveChildEntry(); nunca viene del cliente.
            if ($question->type === 'sub_form') {
                continue;
            }

            $inputKey = "answers.{$question->id}";

            if (! self::isVisible($question, $answers)) {
                // Pregunta oculta por dependencia: se limpia lo que hubiera quedado de antes.
                $current = $existing->get($question->id);

                if ($current && $current->value !== null) {
                    $this->saveSingleAnswer($mainEntry, $question, null, $request, $inputKey, $existing);
                }

                continue;
            }

            if (! array_key_exists($question->id, $answers) && ! $request->hasFile($inputKey)) {
                continue;
            }

            $this->saveSingleAnswer($mainEntry, $question, $answers[$question->id] ?? null, $request, $inputKey, $existing);
        }

        // 2. Sub-formularios
        foreach ($subAnswers as $parentQuestionId => $incoming) {
            $parentQuestion = $questions->get((int) $parentQuestionId);

            if (! $parentQuestion || $parentQuestion->type !== 'sub_form') {
                continue;
            }

            if (! self::isVisible($parentQuestion, $answers)) {
                continue;
            }

            $targetSectionId = (int) ($parentQuestion->options['target_section_id'] ?? 0);

            if (! $targetSectionId) {
                continue;
            }

            $incoming = is_array($incoming) ? $incoming : [];
            $childEntry = $this->resolveChildEntry($mainEntry, $parentQuestion, $targetSectionId);
            $childExisting = $this->existingAnswers($childEntry);

            foreach ($this->questionsOf($targetSectionId) as $childQuestion) {
                if ($childQuestion->type === 'sub_form') {
                    continue;
                }

                $value = $incoming[$childQuestion->id] ?? null;

                // Repeaters vacíos se guardan como arreglo vacío en JSON
                if ($value === null && in_array($childQuestion->type, ['repeater', 'repeater_awards'])) {
                    $value = [];
                }

                $this->saveSingleAnswer(
                    $childEntry,
                    $childQuestion,
                    $value,
                    $request,
                    "sub_answers.{$parentQuestionId}.{$childQuestion->id}",
                    $childExisting
                );
            }
        }
    }

    /**
     * ¿Se cumple la dependencia condicional de la pregunta?
     * Misma lógica que StoreSurveyRequest::isDependencyMet(); idealmente el request la llama desde aquí.
     */
    public static function isVisible(Questions $question, array $answers): bool
    {
        $options = $question->options ?? [];

        if (! filter_var($options['is_dependent'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $parentId = $options['depends_on_question_id'] ?? null;
        $expected = self::normalize($options['depends_on_value'] ?? '');
        $actual = $parentId !== null ? ($answers[$parentId] ?? null) : null;

        if (is_array($actual)) {
            return in_array($expected, array_map(fn ($v) => self::normalize($v), $actual), true);
        }

        return self::normalize($actual) === $expected;
    }

    private static function normalize(mixed $value): string
    {
        return is_scalar($value) ? trim(strtolower((string) $value)) : '';
    }

    /**
     * Sección del registro: la columna entries.section_id; si es un registro viejo sin ella,
     * se deduce de las preguntas enviadas y se corrige en el acto.
     */
    private function resolveSectionId(Entry $entry, array $answers): ?int
    {
        if ($entry->section_id) {
            return (int) $entry->section_id;
        }

        $sectionId = Questions::query()->whereIn('id', array_keys($answers))->value('section_id');

        if ($sectionId) {
            $entry->update(['section_id' => $sectionId]);
        }

        return $sectionId ? (int) $sectionId : null;
    }

    /** Preguntas de una sección indexadas por id (filtradas por sistema): una consulta por sección y petición. */
    private function questionsOf(int $sectionId): Collection
    {
        // Los sub-formularios cuya sección destino no pertenece al sistema actual se descartan:
        // no se procesan aunque lleguen en la petición, y lo ya guardado de ellos no se toca.
        return $this->questionsBySection[$sectionId] ??= $this->sistemaFilter->questions(
            Questions::query()
                ->where('section_id', $sectionId)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('id')
        );
    }

    /** Respuestas ya guardadas del registro (question_id => Answer): una sola consulta. */
    private function existingAnswers(Entry $entry): Collection
    {
        return $entry->wasRecentlyCreated
            ? collect()
            : $entry->answers()->get()->keyBy('question_id');
    }

    /**
     * Recupera el registro hijo del sub-formulario o lo crea.
     *
     * El hijo se busca por el vínculo guardado en la respuesta del padre Y se exige que su
     * parent_entry_id sea este padre: así un valor manipulado nunca apunta a registros ajenos.
     */
    private function resolveChildEntry(Entry $parent, Questions $parentQuestion, int $targetSectionId): Entry
    {
        $linkedId = Answer::query()
            ->where('entry_id', $parent->id)
            ->where('question_id', $parentQuestion->id)
            ->value('value');

        $child = ctype_digit((string) $linkedId)
            ? Entry::query()->whereKey((int) $linkedId)->where('parent_entry_id', $parent->id)->first()
            : null;

        if ($child) {
            return $child;
        }

        $child = Entry::create([
            'user_id' => $parent->user_id,          // dueño del registro, no quien edita (p. ej. un admin)
            'ciclo_id' => $parent->ciclo_id,
            'section_id' => $targetSectionId,
            'parent_entry_id' => $parent->id,
        ]);

        // Se mantiene el vínculo en la respuesta del padre por compatibilidad con las vistas actuales.
        Answer::updateOrCreate(
            ['entry_id' => $parent->id, 'question_id' => $parentQuestion->id],
            ['value' => $child->id]
        );

        return $child;
    }

    /**
     * Guarda una respuesta (archivos, JSON y referencias incluidos) sin consultas repetidas.
     */
    private function saveSingleAnswer(
        Entry $entry,
        Questions $question,
        mixed $value,
        Request $request,
        string $inputKey,
        Collection $existing
    ): void {
        $current = $existing->get($question->id);
        $extra = [];

        if ($request->hasFile($inputKey)) {
            $value = $request->file($inputKey)->store('uploads', 'public');
            $this->deleteFileAfterCommit($current?->value);
        } elseif ($value === null && $question->type === 'file' && $current) {
            // Actualización sin archivo nuevo: se conserva el anterior.
            return;
        }

        if ($question->type === 'reference') {
            [$value, $extra['ref_entry_id']] = $this->resolveReference($question, $entry, $value, $inputKey);
        }

        $payload = ['value' => is_array($value) ? json_encode($value) : $value] + $extra;

        if ($current) {
            $current->update($payload);
        } else {
            $existing->put($question->id, $entry->answers()->create($payload + ['question_id' => $question->id]));
        }
    }

    /**
     * Respuesta de tipo "reference": devuelve [texto visible, id del registro referenciado].
     * El texto se guarda en `value`. Con alcance "user" el vínculo va en `ref_entry_id`;
     * con alcance "all" no hay registro concreto, así que ref_entry_id queda en null.
     *
     * @return array{0: ?string, 1: ?int}
     */
    private function resolveReference(Questions $question, Entry $entry, mixed $value, string $inputKey): array
    {
        if ($value === null || $value === '') {
            return [null, null];
        }

        $options = is_scalar($value)
            ? $this->reader->referenceOptions($question, (int) $entry->user_id)
            : [];

        if ($this->reader->referenceScope($question) === EntryReader::SCOPE_ALL) {
            $label = is_scalar($value) && array_key_exists((string) $value, $options) ? (string) $value : null;
            $refId = null;
        } else {
            $label = is_scalar($value) ? ($options[(int) $value] ?? null) : null;
            $refId = $label !== null ? (int) $value : null;
        }

        if ($label === null) {
            throw ValidationException::withMessages([$inputKey => 'La referencia seleccionada no es válida.']);
        }

        return [$label, $refId];
    }

    /** Borra un archivo reemplazado solo cuando la transacción ya se confirmó. */
    private function deleteFileAfterCommit(mixed $path): void
    {
        if (is_string($path) && str_starts_with($path, 'uploads/')) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($path));
        }
    }
}