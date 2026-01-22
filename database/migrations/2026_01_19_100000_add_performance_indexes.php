<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add indexes to frequently queried columns for better performance.
 * 
 * This migration is SAFE and does not modify any data or table structures.
 * It only adds indexes to speed up query performance.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to questions table
        Schema::table('questions', function (Blueprint $table) {
            // Index for filtering by material (very common query)
            $table->index('material_id', 'idx_questions_material');
            
            // Index for filtering by creator (teacher sees own questions)
            $table->index('created_by', 'idx_questions_created_by');
            
            // Index for filtering by difficulty
            $table->index('difficulty', 'idx_questions_difficulty');
            
            // Index for filtering by question type
            $table->index('question_type', 'idx_questions_type');
            
            // Composite index for common query pattern: material + difficulty
            $table->index(['material_id', 'difficulty'], 'idx_questions_material_diff');
        });

        // Add indexes to game_sessions table
        Schema::table('game_sessions', function (Blueprint $table) {
            // Index for filtering by status (active games, finished games)
            $table->index('status', 'idx_sessions_status');
            
            // Composite index for finding active lobbies
            $table->index(['status', 'session_pin'], 'idx_sessions_status_pin');
            
            // Index for winner queries
            $table->index('winner_team', 'idx_sessions_winner');
            
            // Index for created_at (sorting by date)
            $table->index('created_at', 'idx_sessions_created');
        });

        // Add indexes to game_rounds table
        Schema::table('game_rounds', function (Blueprint $table) {
            // Composite index for querying rounds by session and order
            $table->index(['game_session_id', 'round_number'], 'idx_rounds_session_num');
        });

        // Add indexes to topics table
        Schema::table('topics', function (Blueprint $table) {
            // Index for creator (teacher sees own topics)
            $table->index('created_by', 'idx_topics_created_by');
        });

        // Add indexes to materials table
        Schema::table('materials', function (Blueprint $table) {
            // Composite index for common lookup
            $table->index(['topic_id', 'name'], 'idx_materials_topic_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('idx_questions_material');
            $table->dropIndex('idx_questions_created_by');
            $table->dropIndex('idx_questions_difficulty');
            $table->dropIndex('idx_questions_type');
            $table->dropIndex('idx_questions_material_diff');
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_status');
            $table->dropIndex('idx_sessions_status_pin');
            $table->dropIndex('idx_sessions_winner');
            $table->dropIndex('idx_sessions_created');
        });

        Schema::table('game_rounds', function (Blueprint $table) {
            $table->dropIndex('idx_rounds_session_num');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->dropIndex('idx_topics_created_by');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex('idx_materials_topic_name');
        });
    }
};
