<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Questions;

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

        // 1. Obtenemos todas las preguntas (idealmente solo las de secciones activas)
        // Podrías filtrar aquí: Question::where('is_active', true)->get();
        $questions = Questions::all();

        foreach ($questions as $question) {
            // La clave que envía el HTML es answers[ID], para validación es answers.ID
            $fieldKey = 'answers.' . $question->id;
            
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
                    // Validamos que lo que envían sea una de las llaves definidas en el JSON
                    if (!empty($question->options)) {
                        // array_keys obtiene los IDs o valores que definiste en Filament
                        $validKeys = implode(',', array_keys($question->options));
                        $fieldRules[] = 'in:' . $validKeys;
                    }
                    break;

                // --- ARCHIVOS ---
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240'; // Máximo 10MB
                    // Opcional: restringir tipos
                    // $fieldRules[] = 'mimes:pdf,jpg,png,doc,docx'; 
                    break;

                // --- FECHAS (Con lógica dinámica de min/max) ---
                case 'date':
                    $fieldRules[] = 'date';
                    
                    // Si el admin configuró 'min_date' en Filament
                    if (!empty($question->options['min_date'])) {
                        // after_or_equal:today funciona nativamente en Laravel
                        $fieldRules[] = 'after_or_equal:' . $question->options['min_date'];
                    }

                    // Si el admin configuró 'max_date' en Filament
                    if (!empty($question->options['max_date'])) {
                        $fieldRules[] = 'before_or_equal:' . $question->options['max_date'];
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
            $attributes['answers.' . $question->id] = $question->label;
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