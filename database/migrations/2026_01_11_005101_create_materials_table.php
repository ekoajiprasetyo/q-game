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
        // Create materials table
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // emoji icon
            $table->string('color')->nullable(); // hex color
            $table->timestamps();
        });

        // Add material_id to questions and remove topic_id
        // Questions modifications removed to avoid conflict specific migration already included in create_questions table.

        // Migrate existing data: create materials from topics and link questions
        // This will be done in a seeder or manually

        // Remove topic_id from questions (we'll do this after data migration)
        // Schema::table('questions', function (Blueprint $table) {
        //     $table->dropForeign(['topic_id']);
        //     $table->dropColumn('topic_id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down migration for questions removed

        Schema::dropIfExists('materials');
    }
};
