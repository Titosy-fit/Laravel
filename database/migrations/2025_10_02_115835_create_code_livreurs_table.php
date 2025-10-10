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
            $table->string('typeCodeLivreur'); // exemple: "email", "sms"
            $table->string('codeLivreur'); // le code envoyé
            $table->unsignedBigInteger('idLivreur'); // clé étrangère vers livreurs
            $table->timestamps();

            // Relation avec la table livreurs
            $table->foreign('idLivreur')->references('id')->on('livreurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_livreurs');
    }
};
