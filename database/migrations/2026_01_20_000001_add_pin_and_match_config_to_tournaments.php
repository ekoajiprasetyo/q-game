<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updated: References game_* prefixed tables
     */
    public function up(): void
    {
        // Add PIN to game_tournaments table
        Schema::table('game_tournaments', function (Blueprint $table) {
            $table->string('pin', 6)->unique()->nullable()->after('status');
        });

        // Add per-match configuration to game_tournament_matches table
        Schema::table('game_tournament_matches', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('game_session_id')->constrained('game_topics')->onDelete('set null');
            $table->foreignId('material_id')->nullable()->after('topic_id')->constrained('game_materials')->onDelete('set null');
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
        Schema::table('game_tournaments', function (Blueprint $table) {
            $table->dropColumn('pin');
        });

        Schema::table('game_tournament_matches', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
            $table->dropForeign(['material_id']);
            $table->dropColumn(['topic_id', 'material_id', 'time_per_question', 'total_questions', 'is_ready']);
        });
    }
};
