<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_logs', function (Blueprint $table) {

            $table->id();

            $table->string('correlation_id')->nullable();
            $table->string('code')->nullable();
            $table->string('message')->nullable();
            $table->unsignedTinyInteger('status')->nullable();
            $table->string('queue_name')->nullable();
            $table->string('event_type')->nullable();
            $table->json('metadata')->nullable();
            $table->string('user_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_logs');
    }
};
