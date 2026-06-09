<?php

use App\Enums\OficioPriorityEnum;
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

            $table->longText('reason');

            $table->foreignId('author_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->enum('type', [
                OficioStatusEnum::REJECTED->value,
                OficioStatusEnum::RETURNED->value,
            ])->default(
                OficioStatusEnum::REJECTED->value
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejection_infos');
    }
};
