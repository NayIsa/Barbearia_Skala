<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servico_id')->constrained('servicos')->restrictOnDelete();
            $table->string('cliente', 80);
            $table->string('telefone', 11);
            $table->date('data');
            $table->unsignedSmallInteger('inicio'); // minutos desde 00:00
            $table->unsignedSmallInteger('fim');
            $table->timestamps();

            $table->index(['data', 'inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
