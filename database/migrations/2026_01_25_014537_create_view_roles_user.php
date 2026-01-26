<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
        CREATE OR REPLACE VIEW view_roles_user AS
SELECT 
    users.id as user_id,
    users.name as nombre,
    roles.name as name_role
FROM 
    users
LEFT JOIN model_has_roles 
    ON model_has_roles.model_id = users.id
LEFT JOIN roles 
    ON model_has_roles.role_id = roles.id;
    ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_roles_user");
    }
};
