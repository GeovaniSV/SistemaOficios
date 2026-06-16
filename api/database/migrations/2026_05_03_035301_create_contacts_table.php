<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TipoContatoEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {

            $table->id();

            $table->enum('type', [
                TipoContatoEnum::PESSOA_FISICA->value,
                TipoContatoEnum::PESSOA_JURIDICA->value,
            ]);

            $table->string('doc', 14)->unique();

            $table->string('name');

            $table->foreignId('address_id')
                ->constrained('addresses')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
