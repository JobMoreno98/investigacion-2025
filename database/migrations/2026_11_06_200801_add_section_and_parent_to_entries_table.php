<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // Agregar las columnas en el orden específico
            $table->unsignedBigInteger('section_id')->after('user_id');
            $table->unsignedBigInteger('parent_entry_id')->nullable()->after('section_id');

            $table->foreign('section_id', 'entries_section_id_foreign')
                  ->references('id')
                  ->on('sections');
                  
            $table->foreign('parent_entry_id', 'entries_parent_id_foreign')
                  ->references('id')
                  ->on('entries')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropForeign('entries_section_id_foreign');
            $table->dropForeign('entries_parent_id_foreign');

            $table->dropColumn(['section_id', 'parent_entry_id']);
        });
    }
};