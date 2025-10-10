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
        Schema::create('products', function (Blueprint $table) {
            $table->id("idProduct");
            $table->string("refProduct",20)->unique();
            $table->string("designProduct");
            $table->decimal("marqueProduct", 10, 2);
            $table->text("description")->nullable();
            $table->string("imageProduct")->nullable();
        
            $table->foreignId("idSousCategorie")
                  ->constrained("sous_categorie_produits", "idSousCategorie")
                  ->onDelete("cascade"); 
        
            $table->foreignId("idFournisseur")
                  ->constrained("fournisseurs", "idFournisseur")
                  ->onDelete("cascade");      
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
