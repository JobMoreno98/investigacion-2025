<?php

namespace Database\Seeders;

use App\Models\Categorias;
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
                'Mestrías',
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
                ]
            );
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

    }
}
