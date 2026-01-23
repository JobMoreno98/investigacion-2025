<?php

namespace Database\Seeders;

use App\Models\Questions;
use App\Models\Sections;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProyectosInvestigacion extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [

            [
                'label' => 'Folio',
                'type' => 'text',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 1,
                'options' => [
                    'code_tag' => 'generated_code',
                    'default_value' => null,
                ],
            ],

            [
                'label' => 'Título del Proyecto',
                'type' => 'text',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 2,
                'options' => null,
            ],

            [
                'label' => 'Fecha inicio',
                'type' => 'date',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 3,
                'options' => null,
            ],

            [
                'label' => 'Fecha fin',
                'type' => 'date',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 4,
                'options' => null,
            ],

            [
                'label' => 'Tipo de registro',
                'type' => 'select',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 5,
                'options' => [
                    'code_tag' => 'source_type',
                    'default_value' => null,
                    'choices' => [
                        ['label' => 'Nuevo', 'value' => 'N'],
                        ['label' => 'Continuación', 'value' => 'C'],
                    ],
                ],
            ],

            [
                'label' => 'Tipo de proyecto',
                'type' => 'select',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 6,
                'options' => [
                    'choices' => [
                        ['label' => 'Investigación básica', 'value' => 'basica'],
                        ['label' => 'Investigación aplicada', 'value' => 'aplicada'],
                        ['label' => 'Desarrollo tecnológico y experimental', 'value' => 'tecnologico'],
                    ],
                ],
            ],

            [
                'label' => 'Principal sector que impacta',
                'type' => 'select',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 7,
                'options' => [
                    'choices' => [
                        ['label' => 'Social', 'value' => 'social'],
                        ['label' => 'Público', 'value' => 'publico'],
                        ['label' => 'Privado', 'value' => 'privado'],
                    ],
                ],
            ],

            [
                'label' => 'Enfoque del proyecto',
                'type' => 'select',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 8,
                'options' => [
                    'choices' => [
                        ['label' => 'Disciplinario', 'value' => 'disciplinario'],
                        ['label' => 'Interdisciplinario', 'value' => 'interdisciplinario'],
                        ['label' => 'Multidisciplinario', 'value' => 'multidisciplinario'],
                        ['label' => 'Transdisciplinario', 'value' => 'transdisciplinario'],
                    ],
                ],
            ],

            [
                'label' => 'Registro y apoyo económico en otras instituciones',
                'type' => 'select',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 9,
                'options' => [
                    'choices' => [
                        ['label' => 'No', 'value' => 'no'],
                        ['label' => 'Si', 'value' => 'si'],
                    ],
                ],
            ],

            [
                'label' => 'En caso de recibir financiamiento especificar institución y monto',
                'type' => 'textarea',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 10,
                'options' => null,
            ],

            [
                'label' => 'Resumen del proyecto',
                'type' => 'textarea',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 11,
                'options' => null,
            ],

            [
                'label' => 'Justificación',
                'type' => 'textarea',
                'is_required' => true,
                'is_unique' => false,
                'sort_order' => 12,
                'options' => null,
            ],
        ];

        $section = Sections::where('title', 'Proyectos de Investigación')->first();
        foreach ($questions as $qData) {
            Questions::firstOrCreate(
                [
                    'section_id' => $section->id,
                    'label' => $qData['label'],
                ],
                [
                    'type' => $qData['type'],
                    'is_required' => $qData['is_required'],
                    'sort_order' => $qData['sort_order'],
                    'is_unique' => $qData['is_unique'],
                    'options' => $qData['options'],
                ]
            );
        }
    }
}
