<?php

use App\Enums\MessageStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {

            $table->id();

            $table->enum('status', [
                MessageStatusEnum::PENDING->value,
                MessageStatusEnum::SENT->value,
                MessageStatusEnum::ERROR->value,
            ])->default(
                MessageStatusEnum::PENDING->value
            );

            $table->foreignId('oficio_id')
                ->constrained('oficios')
                ->cascadeOnDelete();

            $table->foreignId('responsible_id')
                ->constrained('responsibles')
                ->cascadeOnDelete();

            $table->timestamp('sent_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
