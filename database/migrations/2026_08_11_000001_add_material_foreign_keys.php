<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('material_id')->references('id')->on('materials')->nullOnDelete();
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->foreign('material_id')->references('id')->on('materials')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
        });
    }
};
