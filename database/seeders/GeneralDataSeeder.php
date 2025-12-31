<?php

namespace Database\Seeders;

use App\Models\Categorias;
use App\Models\Questions;
use App\Models\Sections;
use Illuminate\Database\Seeder;

class GeneralDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categorias::firstOrCreate(
            ['titulo' => 'Datos Generales'],
            [
                'titulo' => 'Datos Generales',
            ]
        );
        
        $section = Sections::firstOrCreate(
            ['title' => 'Datos Generales'], // Busca por título
            [
                'description' => 'Información personal básica e identificación del usuario.',
                'is_repeatable' => false, // <--- IMPORTANTE: Solo se llena una vez
                'is_active' => true,
                'sort_order' => 1, // Que aparezca primero
                'categoria_id' => $categoria->id,
            ]
        );
        $questions = [
            [
                'label' => 'Código',
                'type' => 'number',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique' => true, // <--- IMPORTANTE: Nadie puede repetir este dato
                'options' => null,
            ],
            [
                'label' => 'Nombres',
                'type' => 'text',
                'is_required' => true,
                'sort_order' => 2,
                'is_unique' => false,
                'options' => null,
            ],
            [
                'label' => 'Apellido Paterno',
                'type' => 'text',
                'is_required' => true,
                'sort_order' => 3,
                'is_unique' => false,
                'options' => null,
            ],
            [
                'label' => 'Apellido Materno',
                'type' => 'text',
                'is_required' => true,
                'sort_order' => 4,
                'is_unique' => false,
                'options' => null,
            ],

            [
                'label' => 'Género',
                'type' => 'select',
                'is_required' => true,
                'sort_order' => 5,
                'is_unique' => false,
                'options' => [
                    'choices' => [
                        [
                            'value' => 'masculino',
                            'label' => 'Masculino',
                        ],
                        [
                            'value' => 'femenino',
                            'label' => 'Femenino',
                        ],
                        [
                            'value' => 'otro',
                            'label' => 'Otro',
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Fecha de Nacimiento',
                'type' => 'date',
                'is_required' => true,
                'sort_order' => 4,
                'is_unique' => false,
                'options' => null,
            ],
        ];
        foreach ($questions as $qData) {
            // Buscamos si la pregunta ya existe en esta sección para no duplicar
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
