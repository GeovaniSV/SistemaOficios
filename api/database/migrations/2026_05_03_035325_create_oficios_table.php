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
        Schema::create('oficios', function (Blueprint $table) {

            $table->id();

            $table->string('subject');

            $table->foreignId('destination_contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            $table->enum('priority', [
                OficioPriorityEnum::LOW->value,
                OficioPriorityEnum::MEDIUM->value,
                OficioPriorityEnum::HIGH->value,
            ]);

            $table->longText('content');

            $table->enum('status', [
                OficioStatusEnum::PENDING->value,
                OficioStatusEnum::COMPLETED->value,
            ])->default(
                OficioStatusEnum::PENDING->value
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficios');
    }
};
