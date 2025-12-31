<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionesDepartamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $catalogs = [

            /*
            |--------------------------------------------------------------------------
            | DIVISIONES
            |--------------------------------------------------------------------------
            */
            [
                'catalog_type' => 'divisiones',
                'name' => 'División de Estudios de Estado y Sociedad',
                'code' => 'division_estado_sociedad',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'divisiones',
                'name' => 'División de Estudios de la Cultura',
                'code' => 'division_cultura',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'divisiones',
                'name' => 'División de Estudios Históricos y Humanos',
                'code' => 'division_historicos_humanos',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'divisiones',
                'name' => 'División de Estudios Jurídicos',
                'code' => 'division_juridicos',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'divisiones',
                'name' => 'División de Estudios Políticos y Sociales',
                'code' => 'division_politicos_sociales',
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | DEPARTAMENTOS
            |--------------------------------------------------------------------------
            */
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios del Pacífico',
                'code' => 'depto_estudios_pacifico',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios en Educación',
                'code' => 'depto_estudios_educacion',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Ibéricos y Latinoamericanos',
                'code' => 'depto_estudios_ibericos_latinoamericanos',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Sobre Movimientos Sociales',
                'code' => 'depto_movimientos_sociales',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Socio Urbanos',
                'code' => 'depto_estudios_socio_urbanos',
                'is_active' => true,
            ],

            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios de la Comunicación Social',
                'code' => 'depto_comunicacion_social',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios en Lenguas Indígenas',
                'code' => 'depto_lenguas_indigenas',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Literarios',
                'code' => 'depto_estudios_literarios',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Mesoamericanos y Mexicanos',
                'code' => 'depto_estudios_mesoamericanos_mexicanos',
                'is_active' => true,
            ],

            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Filosofía',
                'code' => 'depto_filosofia',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Geografía y Ordenación Territorial',
                'code' => 'depto_geografia_ordenacion_territorial',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Historia',
                'code' => 'depto_historia',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Lenguas Modernas',
                'code' => 'depto_lenguas_modernas',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Letras',
                'code' => 'depto_letras',
                'is_active' => true,
            ],

            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Derecho Privado',
                'code' => 'depto_derecho_privado',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Derecho Público',
                'code' => 'depto_derecho_publico',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Derecho Social',
                'code' => 'depto_derecho_social',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Interdisciplinares en Ciencias Penales',
                'code' => 'depto_ciencias_penales',
                'is_active' => true,
            ],

            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Desarrollo Social',
                'code' => 'depto_desarrollo_social',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Internacionales',
                'code' => 'depto_estudios_internacionales',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Estudios Políticos',
                'code' => 'depto_estudios_politicos',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Sociología',
                'code' => 'depto_sociologia',
                'is_active' => true,
            ],
            [
                'catalog_type' => 'departamentos',
                'name' => 'Departamento de Trabajo Social',
                'code' => 'depto_trabajo_social',
                'is_active' => true,
            ],
        ];

        foreach ($catalogs as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('catalog_items')->insertOrIgnore($catalogs);
    }
}
