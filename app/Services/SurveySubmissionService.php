<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Entry;
use App\Models\Questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SurveySubmissionService
{
    /**
     * Guarda o actualiza un set completo de respuestas y sub-formularios
     */
    public function saveAnswers(Entry $mainEntry, array $validated, Request $request): void
    {
        // 1. Guardar respuestas del Padre
        if (!empty($validated['answers'])) {
            foreach ($validated['answers'] as $questionId => $value) {
                $this->saveSingleAnswer($mainEntry, $questionId, $value, $request, "answers.{$questionId}");
            }
        }

        // 2. Guardar respuestas de los Sub-formularios
        if (!empty($validated['sub_answers'])) {
            foreach ($validated['sub_answers'] as $parentQId => $incomingChildData) {

                $parentQuestion = Questions::find($parentQId);
                $targetSectionId = $parentQuestion->options['target_section_id'] ?? null;

                if (!$targetSectionId)
                    continue;

                // Le pasamos el targetSectionId a la función
                $childEntry = $this->resolveChildEntry($mainEntry, $parentQId, $targetSectionId);
                if (!$childEntry)
                    continue;

                $childQuestions = Questions::where('section_id', $targetSectionId)->get();

                foreach ($childQuestions as $childQ) {
                    $childQId = $childQ->id;
                    $value = $incomingChildData[$childQId] ?? null;

                    // Parseo de repeaters vacíos (para que guarden un array vacío en JSON)
                    if ($value === null && in_array($childQ->type, ['repeater', 'repeater_awards'])) {
                        $value = [];
                    }

                    $this->saveSingleAnswer($childEntry, $childQId, $value, $request, "sub_answers.{$parentQId}.{$childQId}");
                }
            }
        }
    }

    /**
     * Resuelve el Entry hijo: lo recupera si existe, o lo crea y vincula si es nuevo.
     */
    // Actualiza la firma y el Entry::create()
    private function resolveChildEntry(Entry $parentEntry, int $parentQId, int $targetSectionId): ?Entry
    {
        $linkAnswer = Answer::where('entry_id', $parentEntry->id)
            ->where('question_id', $parentQId)
            ->first();

        if ($linkAnswer && $linkAnswer->value) {
            return Entry::find($linkAnswer->value);
        }

        // AGREGAMOS LOS DOS CAMPOS NUEVOS AQUÍ
        $childEntry = Entry::create([
            'user_id' => Auth::id(),
            'ciclo_id' => $parentEntry->ciclo_id,
            'section_id' => $targetSectionId,      // <-- AGREGADO
            'parent_entry_id' => $parentEntry->id,      // <-- AGREGADO
        ]);

        Answer::updateOrCreate(
            ['entry_id' => $parentEntry->id, 'question_id' => $parentQId],
            ['value' => $childEntry->id]
        );

        return $childEntry;
    }

    /**
     * Guarda un registro individual gestionando subidas de archivos e inyecciones en JSON de manera uniforme.
     */
    private function saveSingleAnswer(Entry $entry, int $questionId, $value, Request $request, string $inputKey): void
    {
        $question = Questions::find($questionId);
        if (!$question)
            return;

        // Gestión de archivos
        if ($request->hasFile($inputKey)) {
            $value = $request->file($inputKey)->store('uploads', 'public');
        } elseif ($value === null && $question->type === 'file') {
            // Si es actualización pero no enviaron archivo nuevo, se omite reemplazar
            if ($entry->answers()->where('question_id', $questionId)->exists()) {
                return;
            }
        }

        // Conversión limpia
        $finalValue = is_array($value) ? json_encode($value) : $value;

        Answer::updateOrCreate(
            ['entry_id' => $entry->id, 'question_id' => $questionId],
            ['value' => $finalValue]
        );
    }
}