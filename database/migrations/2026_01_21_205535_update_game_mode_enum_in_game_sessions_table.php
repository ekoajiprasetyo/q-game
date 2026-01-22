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
            // Mengubah enum menjadi string agar bisa menerima 'tournament'
            // SQLite tidak mendukung perubahan enum secara langsung dengan mudah,
            // jadi mengubahnya ke string adalah solusi paling aman untuk menghilangkan constraint CHECK.
            $table->string('game_mode')->default('race')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            //
        });
    }
};
