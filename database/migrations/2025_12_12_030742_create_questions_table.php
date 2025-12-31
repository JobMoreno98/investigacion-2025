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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // La pregunta: "¿Cuál es tu color favorito?"
            $table->string('type'); // 'text', 'select', 'file', 'number', 'textarea'
            $table->json('options')->nullable(); // Guardará array: ["Rojo", "Azul"]
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
