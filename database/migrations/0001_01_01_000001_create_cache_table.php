<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * CATATAN: Tabel cache sudah ada di Q-Link.
     * Migrasi ini di-skip karena Q-Game menggunakan database bersama dengan Q-Link.
     */
    public function up(): void
    {
        // SKIP - Tabel ini sudah ada di database Q-Link
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SKIP - Jangan hapus tabel karena dimiliki Q-Link
    }
};
