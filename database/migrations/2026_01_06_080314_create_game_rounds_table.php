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
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('round_number');
            $table->string('team_blue_answer')->nullable();
            $table->string('team_red_answer')->nullable();
            $table->integer('team_blue_time_ms')->nullable(); // waktu menjawab dalam ms
            $table->integer('team_red_time_ms')->nullable();
            $table->boolean('team_blue_correct')->default(false);
            $table->boolean('team_red_correct')->default(false);
            $table->enum('winner_team', ['blue', 'red', 'draw', 'none'])->nullable();
            $table->integer('points_awarded')->default(0);
            $table->integer('rope_position_after')->default(0); // -5 to +5, 0 = center
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
