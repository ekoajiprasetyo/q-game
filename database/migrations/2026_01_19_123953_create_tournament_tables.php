<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tournament tables are isolated by the q_game schema.
     */
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['setup', 'active', 'completed'])->default('setup');
            $table->json('game_config')->nullable(); // Stores topic_id, duration, etc.
            $table->foreignId('created_by')->nullable()->references('id')->on('core.users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->nullable(); // Hex color code
            $table->integer('seed_number')->nullable(); // Initial seeding position
            $table->timestamps();
        });

        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->integer('round'); // 1 = Quarter, 2 = Semi, 3 = Final (example)
            $table->integer('match_number'); // Position in the bracket (vertical order)

            // Teams (Nullable because subsequent rounds wait for winners)
            $table->foreignId('team_1_id')->nullable()->constrained('tournament_teams')->nullOnDelete();
            $table->foreignId('team_2_id')->nullable()->constrained('tournament_teams')->nullOnDelete();

            // Results
            $table->foreignId('winner_team_id')->nullable()->constrained('tournament_teams')->nullOnDelete();
            $table->foreignId('game_session_id')->nullable()->constrained('game_sessions')->nullOnDelete();

            // Bracket Link (self-reference)
            $table->foreignId('next_match_id')->nullable()->constrained('tournament_matches')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
        Schema::dropIfExists('tournament_teams');
        Schema::dropIfExists('tournaments');
    }
};
