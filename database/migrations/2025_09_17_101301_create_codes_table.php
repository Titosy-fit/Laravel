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
        Schema::create('codes', function (Blueprint $table) {
            $table->id();
            $table->string("typeCode");
            $table->integer("code");
            $table->unsignedBigInteger("client_id");
            $table->timestamps();
        
            // Clé étrangère
            $table->foreign("client_id")
                  ->references("id")
                  ->on("clients")
                  ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codes');
    }
};
