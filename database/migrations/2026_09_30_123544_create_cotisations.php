<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id('id_cotisation');
            $table->foreignId('id_membre')->constrained('membres', 'id_membre')->onDelete('cascade');
            $table->string('mois'); // Exemple : '2026-10'
            $table->decimal('montant', 10, 2);
            $table->timestamp('date_versement')->useCurrent();
            
            // Nouveau champ de validation par l'admin
            $table->enum('statut', ['en_attente', 'validee', 'rejetee'])->default('en_attente');
            
            $table->timestamps();

            // Un membre ne peut avoir qu'une seule cotisation validée ou en attente par mois
            $table->unique(['id_membre', 'mois']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotisations');
    }
};