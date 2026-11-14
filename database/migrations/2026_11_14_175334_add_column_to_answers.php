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
        Schema::table('answers', function (Blueprint $table) {
            $table->unsignedBigInteger('ref_entry_id')->nullable()->after('question_id');
            $table->index('ref_entry_id', 'idx_answers_ref_entry');
            $table->foreign('ref_entry_id', 'answers_ref_entry_fk')
                ->references('id')->on('entries')
                ->nullOnDelete();
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->index(['user_id', 'section_id', 'ciclo_id', 'deleted_at'], 'idx_entries_user_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex('idx_entries_user_section');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign('answers_ref_entry_fk');
            $table->dropIndex('idx_answers_ref_entry');
            $table->dropColumn('ref_entry_id');
        });
    }
};
