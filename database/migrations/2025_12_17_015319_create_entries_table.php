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
        Schema::create('entries', function (Blueprint $table) {
$table->id();
            
            // Quién hizo el envío. Nullable para encuestas anónimas.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Opcional: Si en el futuro tienes múltiples formularios diferentes, 
            // aquí podrías agregar un 'form_id'. Por ahora no parece necesario.
            
            // Timestamps guarda automáticamente cuándo se creó el envío (created_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
