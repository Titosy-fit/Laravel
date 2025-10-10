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
        Schema::create('livreurs', function (Blueprint $table) {
            $table->id();
            $table->string('nomLivreur');
            $table->string('prenomLivreur');
            $table->string('adresseLivreur')->nullable();
            $table->string('emailLivreur')->unique();
            $table->string('CIN')->unique();
            $table->date('dateCINLivreur')->nullable();
            $table->string('lieuCINLivreur')->nullable();
            $table->date('dateNaissanceLivreur')->nullable();
            $table->string('passwordLivreur');
            $table->enum('etatInscriptionLivreur', ['en attente', 'validé', 'rejeté'])
                  ->default('en attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livreurs');
    }
};
