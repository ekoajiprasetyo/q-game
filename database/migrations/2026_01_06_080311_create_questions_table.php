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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('material_id')->nullable(); // No constraint yet to avoid order issues
            $table->text('question_text');
            // Complete enum
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'multiple_answer'])->default('multiple_choice');
            $table->json('options')->nullable(); 
            $table->string('correct_answer')->nullable(); // Nullable for multiple_answer which might use JSON in options or special format
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('points')->default(10);
            $table->string('image_url')->nullable();
            $table->integer('image_scale')->default(100);
            $table->string('audio_url')->nullable();
            $table->integer('time_limit')->default(30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
