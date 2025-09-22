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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string("reference")->unique();
            $table->string("design");
            $table->decimal("prix", 10, 2);
            $table->integer("qte");
            $table->text("description")->nullable();
            $table->string("image_file")->nullable();
            
            // clé étrangère vers categories
            $table->foreignId("categorie_id")
                  ->constrained("categories")
                  ->onDelete("cascade"); 
                  // supprime les produits si la catégorie est supprimée

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
