<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id('id_distribution');
            $table->string('mois');
            $table->decimal('montant_total', 10, 2);
            $table->timestamp('date_distribution')->useCurrent();
            
            // Clé étrangère vers le membre bénéficiaire
            $table->foreignId('id_membre')->constrained('membres', 'id_membre')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};