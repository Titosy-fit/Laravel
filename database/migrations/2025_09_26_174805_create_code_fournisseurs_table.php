<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string("typeCodeFournisseur");
            $table->string("codeFournisseur", 6);
            $table->foreignId("idFournisseur")
                  ->constrained("fournisseurs", "idFournisseur")
                  ->onDelete("cascade");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_fournisseurs');
    }


};
