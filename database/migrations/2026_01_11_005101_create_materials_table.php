<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * RENAMED: materials -> game_materials (menghindari konflik)
     */
    public function up(): void
    {
        // Create game_materials table
        Schema::create('game_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('game_topics')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // emoji icon
            $table->string('color')->nullable(); // hex color
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_materials');
    }
};
