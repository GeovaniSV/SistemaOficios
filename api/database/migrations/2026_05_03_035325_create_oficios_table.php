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
            $table->string('number')->unique();
            $table->string('subject');

            $table->foreignId('author_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('destination_contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            $table->enum('priority', [
                OficioPriorityEnum::LOW->value,
                OficioPriorityEnum::MEDIUM->value,
                OficioPriorityEnum::HIGH->value,
            ]);

            $table->longText('content');
            $table->string('department')->nullable();

            $table->enum('status', [
                OficioStatusEnum::DRAFT->value,
                OficioStatusEnum::PENDING->value,
                OficioStatusEnum::APPROVED->value,
                OficioStatusEnum::SENT->value,
                OficioStatusEnum::REJECTED->value,
                OficioStatusEnum::RETURNED->value,
            ])->default(OficioStatusEnum::DRAFT->value);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficios');
    }
};
