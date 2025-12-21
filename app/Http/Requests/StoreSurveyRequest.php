<?php

namespace App\Http\Requests;

use App\Models\Questions;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cambia esto si necesitas lógica de permisos específica
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [];

        // 1. Validamos que los section_ids sean válidos primero (seguridad)
        // Esto asegura que 'section_ids' sea un array y exista en la tabla sections
        $rules['section_ids'] = 'required|array';
        $rules['section_ids.*'] = 'exists:sections,id';

        // 2. Obtenemos SOLO las preguntas de las secciones enviadas
        $targetSections = $this->input('section_ids', []);

        // AQUÍ ESTÁ LA MAGIA: Usamos whereIn
        $questions = Questions::whereIn('section_id', $targetSections)
            // Buena práctica: filtrar activas
            ->get();

        foreach ($questions as $question) {
            // La clave que envía el HTML es answers[ID], para validación es answers.ID
            $fieldKey = 'answers.'.$question->id;

            $fieldRules = [];

            // ---------------------------------------------------------
            // A. Regla Base: Requerido o Nullable
            // ---------------------------------------------------------
            if ($question->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // ---------------------------------------------------------
            // B. Reglas según el Tipo de Pregunta
            // ---------------------------------------------------------
            switch ($question->type) {

                // --- TEXTO Y TEXTAREA ---
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:65535';
                    break;

                    // --- NÚMERO ---
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;

                    // --- SELECT / LISTA ---
                case 'select':
                    // Antes: if (!empty($question->options)) ...
                    // AHORA: Buscamos dentro de 'choices'
                    if (! empty($question->options['choices'])) {
                        $validKeys = implode(',', array_keys($question->options['choices']));
                        $fieldRules[] = 'in:'.$validKeys;
                    }
                    break;

                    // --- ARCHIVOS ---
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240'; // 10MB default

                    // LÓGICA NUEVA: Formatos personalizados
                    if (! empty($question->options['allowed_formats'])) {
                        // Limpiamos espacios en blanco por si el admin escribió "pdf, jpg"
                        $formats = str_replace(' ', '', $question->options['allowed_formats']);

                        // Agregamos la regla mimes:pdf,jpg,png
                        $fieldRules[] = 'mimes:'.$formats;
                    } else {
                        // Opcional: Si el admin lo deja vacío, ¿quieres permitir todo o poner un default?
                        // $fieldRules[] = 'mimes:pdf,jpg,png,doc,docx,xls,xlsx';
                    }
                    break;

                    // --- FECHAS (Con lógica dinámica de min/max) ---
                case 'date':
                    $fieldRules[] = 'date';

                    // Si el admin configuró 'min_date' en Filament
                    if (! empty($question->options['min_date'])) {
                        // after_or_equal:today funciona nativamente en Laravel
                        $fieldRules[] = 'after_or_equal:'.$question->options['min_date'];
                    }

                    // Si el admin configuró 'max_date' en Filament
                    if (! empty($question->options['max_date'])) {
                        $fieldRules[] = 'before_or_equal:'.$question->options['max_date'];
                    }
                    break;
            }

            // Asignamos las reglas acumuladas a este campo
            $rules[$fieldKey] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Personalizar los nombres de los atributos para los errores.
     * Esto hace que el error diga "El campo Fecha de Nacimiento es obligatorio"
     * en lugar de "El campo answers.5 es obligatorio".
     */
    public function attributes(): array
    {
        $attributes = [];
        $questions = Questions::all();

        foreach ($questions as $question) {
            $attributes['answers.'.$question->id] = $question->label;
        }

        return $attributes;
    }

    /**
     * Mensajes personalizados opcionales.
     */
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
        ];
    }
}
