<?php

namespace Database\Seeders;

use App\Models\Categorias;
use App\Models\Questions;
use App\Models\Sections;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categorias = [
            'Formación Académica' => [
                'Licenciaturas',
                'Maestrías',
                'Doctorados',
                'Postdoctorados',
                'Especializaciónes',
                'Actualización Disciplinar y/o Pedagógica',
                'Estudios realizados o en curso',
            ],
            'Datos Laborales' => [],
            'Docencia' => [
                'Cursos Impartidos',
                'Titulación',
                'Asesorias',
                'Tutorías y asesorías en procesos de titulación',
                'Elaboración de programas y planes de estudios',
                'Material Didáctico',
            ],
            'Generación y Aplicación del Conocimiento' => [
                'Proyectos de Investigación',
                'Participación en foros Académicos',
                'Publicaciones',
            ],
            'Reconocimientos' => [
                'Reconocimientos o distinciones',
            ],
            'Vinculación, difusión y extensión de la ciencia y la cultura' => [
                'Estancias Académicas o Sabáticas',
                'Membresía a redes',
                'Sociedades Científicas',
                'Acciones de Internacionalización',
            ],
            'Participación Universitaria' => [
                'Gestión Académica',
                'Coordinación de equipos de trabajo',
                'Participación en comisiones y comites',
            ],
        ];
        $contador = 2;
        foreach ($categorias as $key => $value) {
            $categoria = Categorias::firstOrCreate(

                ['titulo' => $key],
                [
                    'titulo' => $key,
                    'sistema' => ['sia', 'investigacion']
                ]
            );
            if ($key == 'Datos Laborales') {
                $datosLaboralesID = $categoria->id;
            }
            for ($i = 0; $i < count($value); $i++) {

                Sections::firstOrCreate(
                    ['title' => $value[$i]], // Busca por título
                    [
                        'description' => 'Información de '.$value[$i].'.',
                        'is_repeatable' => true, // <--- IMPORTANTE: Solo se llena una vez
                        'is_active' => true,
                        'sort_order' => $i, // Que aparezca primero
                        'categoria_id' => $categoria->id,
                    ]
                );
            }

        }

        $datos = Sections::firstOrCreate(
            ['title' => 'Datos Laborales'], // Busca por título
            [
                'description' => 'Información de Datos Laborales.',
                'is_repeatable' => true, // <--- IMPORTANTE: Solo se llena una vez
                'is_active' => true,
                'sort_order' => 1, // Que aparezca primero
                'categoria_id' => $datosLaboralesID,
            ]
        );
        $questions = [
            [
                'label' => 'División',
                'type' => 'catalog',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique' => false, // <--- IMPORTANTE: Nadie puede repetir este dato
                'options' => [
                    'catalog_name' => 'divisiones',
                ],
            ],
            [
                'label' => 'Departamento',
                'type' => 'catalog',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique' => false, // <--- IMPORTANTE: Nadie puede repetir este dato
                'options' => [
                    'catalog_name' => 'departamentos',
                ],
            ],
            [
                'label' => 'Carga Horaria',
                'type' => 'select',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique' => false, // <--- IMPORTANTE: Nadie puede repetir este dato
                'options' => [
                    'choices' => [
                        [
                            'value' => 'PTC',
                            'label' => 'PTC',
                        ],
                        [
                            'value' => 'PTM',
                            'label' => 'PTM',
                        ],
                        [
                            'value' => 'PA',
                            'label' => 'PA',
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Horas',
                'type' => 'number',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique' => false, // <--- IMPORTANTE: Nadie puede repetir este dato
                'options' => null,
            ],
            [
                'label' => 'Tipo de contrato',
                'type' => 'select',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique' => false, // <--- IMPORTANTE: Nadie puede repetir este dato
                'options' => [
                    'choices' => [
                        [
                            'value' => 'definitivo',
                            'label' => 'Definitivo',
                        ],
                        [
                            'value' => 'emerito',
                            'label' => 'Emérito',
                        ],
                        [
                            'value' => 'honorario',
                            'label' => 'Honorario',
                        ],
                        [
                            'value' => 'interino',
                            'label' => 'Interino',
                        ],
                        [
                            'value' => 'temporal',
                            'label' => 'Temporal',
                        ],
                    ],
                ],
            ],
        ];
        foreach ($questions as $qData) {
            Questions::firstOrCreate(
                [
                    'section_id' => $datos->id,
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
