<?php

namespace Database\Seeders;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DisciplinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $categorias = [
            'Análisis del discurso',
            'Antropología',
            'Antropología ecológica',
            'Antropología social',
            'Antropología y sociología',
            'Arte y estética',
            'Ciencia política',
            'Ciencias de la educación',
            'Ciencias sociales',
            'Ciencias sociales (otros)',
            'Comunicación',
            'Comunicación social',
            'Derecho',
            'Derecho constitucional',
            'Derecho penal',
            'Desarrollo económico regional',
            'Desarrollo humano',
            'Docencia de calidad',
            'Economía regional',
            'Educación',
            'Educación superior',
            'Educación, humanidades y artes',
            'Estudios regionales',
            'Filosofía',
            'Filosofía de las religiones',
            'Filosofía, literatura y arte',
            'Geografia fisica',
            'Geografia humana',
            'Historia',
            'Historia de la educación',
            'Historia y antropología',
            'Lengua y literatura',
            'Lenguaje y comunicación',
            'Lingüística',
            'Lingüística aplicada al frances',
            'Literatura',
            'Literatura mexicana',
            'Planeación y desarrollo',
            'Política educativa',
            'Relaciones internacionales',
            'Sociología',
            'Sociología (otros)',
            'Sociología de la educación',
            'Trabajo social',
        ];

        for ($i = 0; $i < count($categorias); $i++) {
            DB::table('catalog_items')->insert([
                'catalog_type' => 'Disciplina', // Puedes cambiar 'categoria_item' por el nombre clave que necesites
                'name' => $categorias[$i],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
