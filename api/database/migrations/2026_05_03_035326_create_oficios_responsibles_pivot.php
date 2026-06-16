<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficio_responsible', function (Blueprint $table) {

            $table->id();

            $table->foreignId('oficio_id')
                ->constrained('oficios')
                ->cascadeOnDelete();

            $table->foreignId('responsible_id')
                ->constrained('responsibles')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'oficio_id',
                'responsible_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficio_responsible');
    }
};
