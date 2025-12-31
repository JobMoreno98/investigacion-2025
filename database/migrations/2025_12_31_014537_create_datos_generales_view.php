<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
        CREATE OR REPLACE VIEW view_datos_generales AS
        SELECT 
            u.id as user_id,
            u.name,
            
            -- Crea un objeto JSON { 'Etiqueta': 'Valor', 'Otra': 'Valor' }
            JSON_OBJECTAGG(q.label, a.value) as datos_json,
            
            MAX(e.updated_at) as fecha_registro

        FROM users u
        JOIN entries e ON u.id = e.user_id
        JOIN answers a ON e.id = a.entry_id
        JOIN questions q ON a.question_id = q.id
        JOIN sections s ON q.section_id = s.id

        WHERE s.title = 'Datos Generales'
        GROUP BY u.id, u.name
    ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_datos_generales");
    }
};
