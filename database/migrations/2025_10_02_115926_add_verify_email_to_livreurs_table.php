<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            $table->boolean('verify_email')->default(false)->after('etatInscriptionLivreur');
        });
    }

    public function down(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            $table->dropColumn('verify_email');
        });
    }
};
