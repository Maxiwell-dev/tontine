<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membres', function (Blueprint $table) {
            $table->id('id_membre');
            $table->string('nom');
            $table->string('telephone');
            $table->integer('ordre_tour');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membres');
    }
};