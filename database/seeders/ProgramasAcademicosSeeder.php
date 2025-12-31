<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramasAcademicosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $programas = [
            'Carrera de Abogado',
            'Carrera de Abogado Semiescolarizado',
            'Licenciatura en Antropología',
            'Licenciatura en Comunicación Pública',
            'Licenciatura en Criminología',
            'Licenciatura en Didáctica del Francés como Lengua Extranjera',
            'Licenciatura en Docencia del Inglés',
            'Licenciatura en Docencia del Inglés (Modalidad Semiescolarizada Abierta y a Distancia)',
            'Licenciatura en Escritura Creativa',
            'Licenciatura en Estudios Políticos y Gobierno',
            'Licenciatura en Filosofía',
            'Licenciatura en Geografía',
            'Licenciatura en Historia',
            'Licenciatura en Letras Hispánicas',
            'Licenciatura en Relaciones Internacionales',
            'Licenciatura en Sociología',
            'Licenciatura en Trabajo Social',
            'Nivelación a la Licenciatura en Trabajo Social (NiLiTS)',
            'Maestría en Bioética',
            'Maestría en Ciencia Política',
            'Maestría en Ciencias Sociales',
            'Maestría en Comunicación',
            'Maestría en Derecho',
            'Maestría en Desarrollo Local y Territorio',
            'Maestría en Enseñanza del Inglés como Lengua Extranjera',
            'Maestría en Estudios Críticos del Lenguaje',
            'Maestría en Estudios de Género',
            'Maestría en Estudios de las Lenguas y Culturas Inglesas',
            'Maestría en Estudios de Literatura Mexicana',
            'Maestría en Estudios Filosóficos',
            'Maestría en Estudios Francófonos: Pedagogía, Lingüística y Estudios Interculturales',
            'Maestría en Estudios Mesoamericanos',
            'Maestría en Gestión y Desarrollo Social',
            'Maestría en Global Politics and Transpacific Studies',
            'Maestría en Historia de México',
            'Maestría en Investigación Educativa',
            'Maestría en Lingüística Aplicada',
            'Maestría en Literaturas Interamericanas',
            'Maestría en Relaciones Internacionales de Gobiernos y Actores Locales',
            'Maestría Interinstitucional en Deutsch als Fremdsprache: Estudios Interculturales de Lengua, Literatura y Cultura Alemanas',
            'Doctorado en Ciencia Política',
            'Doctorado en Ciencias Sociales',
            'Doctorado en Cognición y Aprendizaje',
            'Doctorado en Derecho',
            'Doctorado en Educación',
            'Doctorado en Geografía y Ordenación Territorial',
            'Doctorado en Historia',
            'Doctorado en Humanidades',
        ];
        for ($i = 0; $i < count($programas); $i++) {
            DB::table('catalog_items')->insert([
                'catalog_type' => 'progrmas_academicos',
                'name' => $programas[$i],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
