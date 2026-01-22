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
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // e.g. "Matematika - Kelas 7A"
            $table->enum('game_mode', ['race', 'turn_based'])->default('race');
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->string('team_blue_name')->default('Tim Biru');
            $table->string('team_red_name')->default('Tim Merah');
            $table->integer('team_blue_score')->default(0);
            $table->integer('team_red_score')->default(0);
            $table->enum('winner_team', ['blue', 'red', 'draw'])->nullable();
            $table->integer('total_questions')->default(10);
            $table->integer('time_per_question')->default(30); // seconds
            $table->integer('pull_strength')->default(1); // how much rope moves per correct answer
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
