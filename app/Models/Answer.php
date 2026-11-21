<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $guarded = [];

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    public function question()
    {
        return $this->belongsTo(Questions::class);
    }
    public function getFormattedValueAttribute()
    {
        $val = $this->value_text;
        
        if ($val === null || $val === '') {
            return '---';
        }

        $question = $this->question;
        if (!$question) {
            return htmlspecialchars($val);
        }

        $decoded = json_decode($val, true);
        $isArray = (json_last_error() === JSON_ERROR_NONE && is_array($decoded));

        // CASO 1: Repeaters (Sub-arrays, como los premios o historial)
        if ($isArray && isset($decoded[0]) && is_array($decoded[0])) {
            $badges = [];
            foreach ($decoded as $item) {
                $badges[] = htmlspecialchars(implode(': ', $item));
            }
            return implode('<br>', $badges);
        }

        // CASO 2: Textos, Checkboxes, Selects o Catálogos
        $rawValues = $isArray ? $decoded : [$val];
        $resolvedValues = [];

        foreach ($rawValues as $v) {
            $label = $v; // Por defecto mostramos lo que hay en BD

            // A. Si es un catálogo, usamos tu CatalogProvider para sacar el nombre
            if ($question->type === 'catalog') {
                $catalogName = $question->options['catalog_name'] ?? '';
                $opciones = \App\Helpers\CatalogProvider::get($catalogName);
                
                if (!empty($opciones) && isset($opciones[$v])) {
                    $label = $opciones[$v];
                }
            } 
            // B. Si es opción múltiple, buscamos en el JSON de la pregunta
            elseif (in_array($question->type, ['select', 'radio', 'checkbox'])) {
                $choices = $question->options['choices'] ?? [];
                foreach ($choices as $choice) {
                    if (isset($choice['value']) && (string)$choice['value'] === (string)$v) {
                        $label = $choice['label'] ?? $v;
                        break;
                    }
                }
            }

            $resolvedValues[] = htmlspecialchars($label);
        }

        return implode(', ', $resolvedValues);
    }
}
