<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * RENAMED: tournaments -> game_tournaments
     *          tournament_teams -> game_tournament_teams
     *          tournament_matches -> game_tournament_matches
     */
    public function up(): void
    {
        // 1. Game Tournaments Table
        Schema::create('game_tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['setup', 'active', 'completed'])->default('setup');
            $table->json('game_config')->nullable(); // Stores topic_id, duration, etc.
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Game Tournament Teams Table
        Schema::create('game_tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('game_tournaments')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->nullable(); // Hex color code
            $table->integer('seed_number')->nullable(); // Initial seeding position
            $table->timestamps();
        });

        // 3. Game Tournament Matches Table
        Schema::create('game_tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('game_tournaments')->onDelete('cascade');
            $table->integer('round'); // 1 = Quarter, 2 = Semi, 3 = Final (example)
            $table->integer('match_number'); // Position in the bracket (vertical order)
            
            // Teams (Nullable because subsequent rounds wait for winners)
            $table->foreignId('team_1_id')->nullable()->constrained('game_tournament_teams')->onDelete('set null');
            $table->foreignId('team_2_id')->nullable()->constrained('game_tournament_teams')->onDelete('set null');
            
            // Results
            $table->foreignId('winner_team_id')->nullable()->constrained('game_tournament_teams')->onDelete('set null');
            $table->foreignId('game_session_id')->nullable()->constrained('game_sessions')->onDelete('set null');
            
            // Bracket Link (self-reference)
            $table->foreignId('next_match_id')->nullable()->constrained('game_tournament_matches')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_tournament_matches');
        Schema::dropIfExists('game_tournament_teams');
        Schema::dropIfExists('game_tournaments');
    }
};
