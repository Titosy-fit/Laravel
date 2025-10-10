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
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id('idFournisseur'); // clé primaire auto-incrémentée
            $table->string('nomFournisseur', 255);
            $table->string('prenomFournisseur', 255);
            $table->string('nomEntreprise', 255);
            $table->string('nif', 50)->unique();
            $table->string('stat', 50)->nullable();
            $table->string('rcs', 50)->nullable();
            $table->string('emailFRN', 100)->unique();
            $table->string('passwordFRN'); // hashé plus tard
            $table->enum('etatInscription', ['en attente', 'validé', 'rejeté'])->default('en attente');
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
