<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpf', 11)->unique();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();


            $table->foreignId('role_id')
                ->nullable()
                ->constrained('roles')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_dev')->default(false);
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
