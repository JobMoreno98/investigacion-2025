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
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();

            // Esta es la clave: Define a qué lista pertenece el registro
            // Ej: 'ciudades', 'hospitales', 'sectores_industriales'
            $table->string('catalog_type')->index();

            $table->string('name'); // El texto a mostrar
            $table->string('code')->nullable(); // Un código opcional (ej: C01)

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Opcional: Para evitar duplicados dentro de la misma lista
            $table->unique(['catalog_type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
