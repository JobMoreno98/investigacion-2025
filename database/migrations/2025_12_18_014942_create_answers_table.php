<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            // VINCULACIÓN CLAVE: Esta respuesta pertenece a un envío específico
            $table->foreignId('entry_id')->constrained()->cascadeOnDelete();

            // A qué pregunta corresponde esta respuesta
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            // El valor de la respuesta (texto, opción seleccionada, ruta archivo)
            $table->text('value')->nullable();

            $table->timestamps();

            // Índice compuesto: Evita duplicados técnicos y acelera búsquedas.
            // Asegura que en un mismo envío no haya dos respuestas a la misma pregunta.
            $table->unique(['entry_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
