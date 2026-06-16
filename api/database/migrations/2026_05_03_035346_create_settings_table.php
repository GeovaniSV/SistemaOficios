<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficio_settings', function (Blueprint $table) {

            $table->id();

            $table->longText('statement_text');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficio_settings');
    }
};
