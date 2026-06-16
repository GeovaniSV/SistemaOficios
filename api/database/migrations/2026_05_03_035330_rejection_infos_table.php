<?php

use App\Enums\OficioStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rejection_infos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('oficio_id')
                ->constrained('oficios')
                ->onDelete('cascade');

            $table->foreignId('author_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->text('reason');

            $table->enum('type', [
                OficioStatusEnum::REJECTED->value,
                OficioStatusEnum::RETURNED->value,
            ]);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejection_infos');
    }
};
