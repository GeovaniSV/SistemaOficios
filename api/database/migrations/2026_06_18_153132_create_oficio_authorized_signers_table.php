<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oficio_authorized_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->unique()->constrained('positions')->cascadeOnDelete();
            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE oficio_authorized_signers ADD CONSTRAINT chk_signer_exactly_one '
            . 'CHECK ((user_id IS NOT NULL AND position_id IS NULL) OR (user_id IS NULL AND position_id IS NOT NULL))'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oficio_authorized_signers');
    }
};
