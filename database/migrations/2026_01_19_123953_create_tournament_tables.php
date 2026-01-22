<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tournaments Table
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['setup', 'active', 'completed'])->default('setup');
            $table->json('game_config')->nullable(); // Stores topic_id, duration, etc.
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Tournament Teams Table
        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->nullable(); // Hex color code
            $table->integer('seed_number')->nullable(); // Initial seeding position
            $table->timestamps();
        });

        // 3. Tournament Matches Table
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->integer('round'); // 1 = Quarter, 2 = Semi, 3 = Final (example)
            $table->integer('match_number'); // Position in the bracket (vertical order)
            
            // Teams (Nullable because subsequent rounds wait for winners)
            $table->foreignId('team_1_id')->nullable()->constrained('tournament_teams')->onDelete('set null');
            $table->foreignId('team_2_id')->nullable()->constrained('tournament_teams')->onDelete('set null');
            
            // Results
            $table->foreignId('winner_team_id')->nullable()->constrained('tournament_teams')->onDelete('set null');
            $table->foreignId('game_session_id')->nullable()->constrained('game_sessions')->onDelete('set null');
            
            // Bracket Link
            // Note: We use unsignedBigInteger and separate foreign definition to avoid "table doesn't exist" issues if not careful, 
            // but self-referencing on same table creation usually works if column is defined.
            // However, referencing a row that doesn't exist yet is fine.
            $table->foreignId('next_match_id')->nullable()->constrained('tournament_matches')->onDelete('set null');
            
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
