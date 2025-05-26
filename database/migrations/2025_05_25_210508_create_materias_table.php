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
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('users'); // Relación con usuarios (profesores)
            $table->string('nombre', 100);
            $table->string('aula', 50);
            $table->time('horario_ingreso');
            $table->time('horario_salida');
            $table->string('curso', 50);
            $table->string('token_qr', 100)->unique(); // Token único para el QR
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
