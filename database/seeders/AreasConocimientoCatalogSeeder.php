<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AreasConocimientoCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $disciplinas = [
            'Educación',
            'Antropología Física',
            'Arqueología',
            'Estética',
            'Etnohistoria',
            'Filología',
            'Filosofía',
            'Historia',
            'Arquitectura',
            'Urbanismo',
            'Psicología',
            'Literatura',
            'Lingüística y disciplinas afines',
            'Sociología',
            'Antropología Social',
            'Demografía',
            'Comunicación',
            'Derecho',
            'Etnología',
            'Geografía',
            'Administración y políticas públicas y administración privada',
            'Ciencias políticas',
            'Relaciones internacionales y de disciplinas afines',
        ];

        for ($i = 0; $i < count($disciplinas); $i++) {
            DB::table('catalog_items')->insert([
                'catalog_type' => 'AreasConocimiento',
                'name' => $disciplinas[$i],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
