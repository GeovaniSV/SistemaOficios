<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_logs', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->change();
            $table->string('worker', 50)->nullable()->after('queue_name');
        });
    }

    public function down(): void
    {
        Schema::table('worker_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->nullable()->change();
            $table->dropColumn('worker');
        });
    }
};
