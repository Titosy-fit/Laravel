<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_livreurs', function (Blueprint $table) {
            $table->id();
            $table->string('typeCodeLivreur'); // Exemple : OTP, REF, etc.
            $table->string('codeLivreur')->unique(); // Le code du livreur
            $table->unsignedBigInteger('idLivreur'); // FK vers table livreurs
            $table->timestamps();

            // Définir la relation clé étrangère
            $table->foreign('idLivreur')
                  ->references('id')
                  ->on('livreurs')
                  ->onDelete('cascade'); // si le livreur est supprimé, ses codes aussi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_livreurs');
    }
};
