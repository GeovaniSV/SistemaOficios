<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oficio_settings', function (Blueprint $table) {

            $table->dropColumn('statement_text');

            $table->longText('header')->nullable();
            $table->longText('footer')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('oficio_settings', function (Blueprint $table) {

            $table->dropColumn(['header', 'footer']);

            $table->longText('statement_text')->nullable();
        });
    }
};
