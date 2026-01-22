<?php
/**
 * Script Diagnosa Error 500 Laravel
 * Upload ke public html, jalankan, lalu HAPUS.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Laravel 500 Error Diagnoser</h1>";

$corePath = __DIR__ . '/../../q-game-core';

// 1. Cek Folder Core
if (!is_dir($corePath)) {
    die("<h3 style='color:red'>Folder Core tidak ditemukan di: $corePath</h3>");
}
echo "<div style='color:green'>&#10004; Folder Core ditemukan.</div>";

// 2. Cek Permission Storage
$storagePath = $corePath . '/storage';
$logPath = $storagePath . '/logs/laravel.log';
$isWritable = is_writable($storagePath);

if ($isWritable) {
    echo "<div style='color:green'>&#10004; Folder Storage Writable.</div>";
} else {
    echo "<div style='color:red'>&#10008; Folder Storage TIDAK Writable! (Permission Issue)</div>";
    echo "Coba chmod 775 atau 755 pada folder storage recursively.<br>";
}

// 3. Cek Versi PHP
echo "<div>Versi PHP Server: " . phpversion() . "</div>";

// 4. Baca Log Error Terakhir
echo "<h3>Terakhir Error Log (storage/logs/laravel.log):</h3>";

if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50); // Ambil 50 baris terakhir
    echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc; overflow:auto;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "File log belum ada atau tidak bisa dibaca.";
}

// 5. Cek .env
if (file_exists($corePath . '/.env')) {
    echo "<div style='color:green'>&#10004; File .env ditemukan.</div>";
} else {
    echo "<div style='color:red'>&#10008; File .env TIDAK ditemukan! Rename .env.production jadi .env</div>";
}
?>
