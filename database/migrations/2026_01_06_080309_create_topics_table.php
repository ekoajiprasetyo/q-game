<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * RENAMED: topics -> game_topics (menghindari konflik dengan Q-Exam)
     */
    public function up(): void
    {
        Schema::create('game_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject')->nullable(); // mata pelajaran
            $table->string('icon')->nullable(); // untuk UI (emoji atau icon class)
            $table->string('color')->nullable(); // hex color untuk UI
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_topics');
    }
};
