<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surprise_cards', function (Blueprint $table) {
            $table->timestamp('timer_paused_at')->nullable()->after('revealed_at');
            $table->unsignedInteger('timer_remaining_seconds')->nullable()->after('timer_paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('surprise_cards', function (Blueprint $table) {
            $table->dropColumn(['timer_paused_at', 'timer_remaining_seconds']);
        });
    }
};
