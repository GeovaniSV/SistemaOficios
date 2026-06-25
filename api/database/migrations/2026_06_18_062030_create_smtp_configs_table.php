<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_configs', function (Blueprint $table) {

            $table->id();

            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('username');
            $table->text('password')->nullable();
            $table->string('from_name');
            $table->string('from_email');
            $table->boolean('use_tls')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_configs');
    }
};
