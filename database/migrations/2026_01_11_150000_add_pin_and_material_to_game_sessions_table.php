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
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->string('session_pin', 6)->nullable()->after('id')->index();
            $table->enum('status', ['waiting', 'active', 'finished'])->default('waiting')->after('game_mode');
            // Change topic_id to be nullable or generic if we use material_id preferred
            // Add material_id
            $table->unsignedBigInteger('material_id')->nullable()->after('topic_id');
            // We assume material belongs to topic, so topic_id is still relevant for broader categorization
            // No constraint yet to avoid issues, or basic index
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_pin', 'status', 'material_id']);
        });
    }
};
