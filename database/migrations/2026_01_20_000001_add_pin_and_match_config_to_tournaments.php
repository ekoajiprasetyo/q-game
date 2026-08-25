<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tables are isolated by the q_game schema.
     */
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('pin', 6)->unique()->nullable()->after('status');
        });

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->integer('time_per_question')->default(30)->after('material_id');
            $table->integer('total_questions')->default(10)->after('time_per_question');
            $table->boolean('is_ready')->default(false)->after('total_questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('pin');
        });

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
            $table->dropForeign(['material_id']);
            $table->dropColumn(['topic_id', 'material_id', 'time_per_question', 'total_questions', 'is_ready']);
        });
    }
};
