<?php

namespace App\Http\Requests;

use App\Models\Questions;
use App\Models\Sections;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSurveyRequest extends FormRequest
{

    private ?int $ownerIdCache = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:sections,id',
            'answers' => 'nullable|array',
            'sub_answers' => 'nullable|array',
        ];

        $answers = $this->input('answers', []);
        $targetSections = $this->input('section_ids', []);

        if (empty($targetSections)) {
            return $rules;
        }

        $questions = Questions::whereIn('section_id', $targetSections)->get();
        $questions = app(\App\Services\SistemaFilter::class)->questions($questions);
        // Obtener el ID del entry principal para ignorar en reglas unique (modo edición)
        $entryRouteParam = $this->route('answer') ?? $this->route('id') ?? $this->route('proyecto');

        $mainEntryId = ($entryRouteParam instanceof \Illuminate\Database\Eloquent\Model)
            ? $entryRouteParam->id
            : $entryRouteParam;

        foreach ($questions as $question) {
            // 1. Preguntas Normales (Padre)
            if (!$this->isDependencyMet($question, $answers)) {
                continue;
            }

            $this->buildRulesForQuestion($question, "answers.{$question->id}", $mainEntryId, $rules);

            // 2. Sub-formularios
            if ($question->type === 'sub_form') {
                $rules["sub_answers.{$question->id}"] = 'nullable|array';
                $targetSectionId = $question->options['target_section_id'] ?? null;

                if ($targetSectionId) {
                    $childSection = Sections::with('questions')->find($targetSectionId);

                    if ($childSection) {
                        // Buscar ID del Entry hijo existente para ignorar en unique
                        $childEntryId = null;
                        if ($mainEntryId && ($this->isMethod('put') || $this->isMethod('patch'))) {
                            $childEntryId = DB::table('answers')
                                ->where('entry_id', $mainEntryId)
                                ->where('question_id', $question->id)
                                ->value('value');
                        }

                        $childAnswers = $this->input("sub_answers.{$question->id}", []);

                        foreach ($childSection->questions as $childQ) {
                            if (!$this->isDependencyMet($childQ, $childAnswers)) {
                                continue;
                            }
                            $this->buildRulesForQuestion($childQ, "sub_answers.{$question->id}.{$childQ->id}", $childEntryId, $rules);
                        }
                    }
                }
            }
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        // Esto detendrá el sistema y te mostrará en pantalla gigante qué campo está fallando
        parent::failedValidation($validator);
    }

    /**
     * Comprueba si se cumplen las dependencias condicionales de una pregunta.
     */
    protected function isDependencyMet(Questions $question, array $answersToEvaluate): bool
    {
        if (!filter_var($question->options['is_dependent'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $parentId = $question->options['depends_on_question_id'] ?? null;
        $expected = trim(strtolower($question->options['depends_on_value'] ?? ''));
        $actual = $answersToEvaluate[$parentId] ?? null;

        if (is_array($actual)) {
            return in_array($expected, array_map(fn($v) => trim(strtolower((string) $v)), $actual));
        }

        return trim(strtolower((string) $actual)) === $expected;
    }

    /**
     * Construye dinámicamente las reglas de validación sin importar si es padre o hijo.
     */
    protected function buildRulesForQuestion(Questions $question, string $fieldKey, ?int $ignoreEntryId, array &$rules): void
    {
        $fieldRules = [];

        // Regla base
        if (!in_array($question->type, ['file', 'sub_form'])) {
            $fieldRules[] = $question->is_required ? 'required' : 'nullable';
        } else {
            $fieldRules[] = 'nullable';
        }

        // Regla unique corregida
        if ($question->is_unique) {
            $uniqueRule = Rule::unique('answers_view', 'respuesta')->where('question_id', $question->id);
            if ($ignoreEntryId) {
                $uniqueRule->whereNot('entry_id', $ignoreEntryId);
            }
            $fieldRules[] = $uniqueRule;
        }

        // Reglas por tipo
        switch ($question->type) {
            case 'scored_text':
                $fieldRules[] = 'array';
                $subReq = $question->is_required ? 'required' : 'nullable';
                $min = $question->options['min_score'] ?? 0;
                $max = $question->options['max_score'] ?? 10;
                $rules["{$fieldKey}.score"] = [$subReq, 'numeric', 'integer', "between:{$min},{$max}"];
                $rules["{$fieldKey}.text"] = [$subReq, 'string', 'min:3'];
                break;
            case 'text':
            case 'textarea':
                array_push($fieldRules, 'string', 'max:65535');
                break;
            case 'number':
                array_push($fieldRules, 'integer', 'min:0');
                break;
            case 'select':
                $choices = $question->options['choices'] ?? [];
                if (!empty($choices)) {
                    $fieldRules[] = Rule::in(array_column($choices, 'value'));
                }
                break;
            case 'file':
                $req = ($this->isMethod('put') || $this->isMethod('patch')) ? 'nullable' : ($question->is_required ? 'required' : 'nullable');
                $formats = str_replace(' ', '', $question->options['allowed_formats'] ?? 'pdf');
                array_push($fieldRules, $req, 'file', 'max:10240', "mimes:{$formats}");
                break;
            case 'date':
                $fieldRules[] = 'date';
                if (!empty($question->options['min_date']))
                    $fieldRules[] = 'after_or_equal:' . $question->options['min_date'];
                if (!empty($question->options['max_date']))
                    $fieldRules[] = 'before_or_equal:' . $question->options['max_date'];
                break;
            case 'catalog':
                $catalogName = $question->options['catalog_name'] ?? '';
                $validOptions = \App\Helpers\CatalogProvider::get($catalogName);
                $fieldRules[] = !empty($validOptions) ? Rule::in(array_keys($validOptions)) : 'prohibited';
                break;
            case 'repeater_awards':
                $fieldRules[] = 'array';
                $rules["{$fieldKey}.*.nombre"] = 'required|string|max:255';
                $choices = array_column($question->options['choices'] ?? [], 'value');
                $rules["{$fieldKey}.*.tipo"] = !empty($choices) ? ['required', Rule::in($choices)] : 'required';
                break;
            case 'reference':
                $options = app(\App\Services\EntryReader::class)->referenceOptions($question, $this->ownerId());
                $fieldRules[] = $options ? Rule::in(array_keys($options)) : 'prohibited';
                break;
        }

        if (!empty($fieldRules)) {
            $rules[$fieldKey] = $fieldRules;
        }
    }

    public function attributes(): array
    {
        $attributes = [];
        $targetSections = $this->input('section_ids', []);
        $questions = empty($targetSections) ? Questions::all() : Questions::whereIn('section_id', $targetSections)->get();

        foreach ($questions as $question) {
            $key = "answers.{$question->id}";
            $attributes[$key] = $question->label;

            if ($question->type === 'scored_text') {
                $attributes["{$key}.score"] = "Puntuación de {$question->label}";
                $attributes["{$key}.text"] = "Justificación de {$question->label}";
            }

            if ($question->type === 'sub_form') {
                $targetSectionId = $question->options['target_section_id'] ?? null;
                if ($targetSectionId && $childSection = Sections::with('questions')->find($targetSectionId)) {
                    foreach ($childSection->questions as $childQ) {
                        $childKey = "sub_answers.{$question->id}.{$childQ->id}";
                        $attributes[$childKey] = $childQ->label;

                        if ($childQ->type === 'scored_text') {
                            $attributes["{$childKey}.score"] = "Puntuación de {$childQ->label}";
                            $attributes["{$childKey}.text"] = "Justificación de {$childQ->label}";
                        }
                        if ($childQ->type === 'repeater_awards') {
                            $attributes["{$childKey}.*.nombre"] = 'Nombre';
                            $attributes["{$childKey}.*.tipo"] = 'Tipo';
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo ":attribute" es obligatorio.',
            'date' => 'El campo ":attribute" no es una fecha válida.',
            'after_or_equal' => 'La fecha de ":attribute" debe ser posterior o igual a :date.',
            'before_or_equal' => 'La fecha de ":attribute" debe ser anterior o igual a :date.',
            'in' => 'La opción seleccionada en ":attribute" no es válida.',
            'file' => 'El archivo subido en ":attribute" no es válido.',
            'max' => 'El valor de ":attribute" excede el límite permitido.',
            'unique' => 'El campo ":attribute" ya ha sido registrado por otro usuario. Contacta a soporte si hay un error.',
            'min' => 'El campo ":attribute" debe ser un número entero mayor o igual a cero.',
            'prohibited' => 'El catálogo seleccionado está vacío o no es válido.',
        ];
    }
    protected function ownerId(): int
    {
        if ($this->ownerIdCache !== null) {
            return $this->ownerIdCache;
        }

        $ownerId = null;

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $param = $this->route('answer') ?? $this->route('id') ?? $this->route('proyecto');
            $entryId = $param instanceof \Illuminate\Database\Eloquent\Model ? $param->getKey() : $param;

            if ($entryId) {
                $ownerId = \App\Models\Entry::whereKey($entryId)->value('user_id');
            }
        }

        return $this->ownerIdCache = (int) ($ownerId ?: \Illuminate\Support\Facades\Auth::id());
    }
}
