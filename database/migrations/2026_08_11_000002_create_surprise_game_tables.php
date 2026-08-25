<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables for Kotak Kejutan. Names are feature-specific, while the
     * configured PostgreSQL search path keeps them inside q_game.
     */
    public function up(): void
    {
        Schema::create('surprise_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('session_pin', 6)->unique();
            $table->string('status')->default('waiting');
            $table->string('mode')->default('quiz');
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->unsignedSmallInteger('board_size');
            $table->unsignedInteger('question_time_limit')->nullable();
            // Foreign keys are added after teams and cards are created.
            $table->unsignedBigInteger('current_team_id')->nullable();
            $table->unsignedBigInteger('current_card_id')->nullable();
            $table->json('config')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->nullable()->references('id')->on('core.users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'session_pin']);
        });

        Schema::create('surprise_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surprise_session_id')->constrained('surprise_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20);
            $table->unsignedSmallInteger('turn_order');
            $table->integer('score')->default(0);
            $table->unsignedSmallInteger('shield_count')->default(0);
            $table->unsignedSmallInteger('skip_turns')->default(0);
            $table->timestamps();

            $table->unique(['surprise_session_id', 'turn_order']);
        });

        Schema::create('surprise_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surprise_session_id')->constrained('surprise_sessions')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('card_type')->default('question');
            $table->foreignId('question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->string('power_up_code')->nullable();
            $table->string('state')->default('hidden');
            $table->foreignId('selected_by_team_id')->nullable()->constrained('surprise_teams')->nullOnDelete();
            $table->timestamp('revealed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('result_payload')->nullable();
            $table->timestamps();

            $table->unique(['surprise_session_id', 'position']);
        });

        Schema::create('surprise_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surprise_session_id')->constrained('surprise_sessions')->cascadeOnDelete();
            $table->foreignId('surprise_card_id')->nullable()->constrained('surprise_cards')->nullOnDelete();
            $table->foreignId('actor_team_id')->nullable()->constrained('surprise_teams')->nullOnDelete();
            $table->string('event_type');
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->references('id')->on('core.users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['surprise_session_id', 'created_at']);
        });

        Schema::table('surprise_sessions', function (Blueprint $table) {
            $table->foreign('current_team_id')->references('id')->on('surprise_teams')->nullOnDelete();
            $table->foreign('current_card_id')->references('id')->on('surprise_cards')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surprise_sessions', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
            $table->dropForeign(['current_card_id']);
        });

        Schema::dropIfExists('surprise_events');
        Schema::dropIfExists('surprise_cards');
        Schema::dropIfExists('surprise_teams');
        Schema::dropIfExists('surprise_sessions');
    }
};
