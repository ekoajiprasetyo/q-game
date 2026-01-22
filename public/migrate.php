<?php
/**
 * Script Helper untuk Menjalankan Migrasi Database di Hosting Shared
 * Upload file ini ke folder PUBLIC subdomain Anda.
 */

// AKTIFKAN ERROR REPORTING AGAR KITA TAHU MASALAHNYA
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$accessKey = 'buka_sesama';

if (!isset($_GET['key']) || $_GET['key'] !== $accessKey) {
    die("Akses Ditolak. Gunakan parameter key yang benar.");
}

echo "<h3>Database Migration Tool (Debug Mode)</h3>";
echo "Mencoba memuat Laravel Core...<br>";

// Cek folder sebelum require
$autoloadPath = __DIR__.'/../../q-game-core/vendor/autoload.php';
echo "Mencari autoloader di: " . realpath($autoloadPath) . " (" . $autoloadPath . ")<br>";

if (!file_exists($autoloadPath)) {
    die("<h3 style='color:red'>FATAL: File autoload.php tidak ditemukan! Periksa struktur folder Anda.</h3>Path yang dicari: $autoloadPath");
}

try {
    require $autoloadPath;
    $app = require __DIR__.'/../../q-game-core/bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    echo "Laravel Core berhasil dimuat.<br>";
    echo "DB Connection: " . config('database.default') . "<br>";

    echo "<h4>Menjalankan Migrasi...</h4>";
    echo "<pre>";
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo Illuminate\Support\Facades\Artisan::output();
    echo "</pre>";
    echo "<h4 style='color:green'>SELESAI.</h4>";

} catch (\Throwable $e) { // Gunakan Throwable untuk menangkap semua Error & Exception
    echo "<h3 style='color:red'>TERJADI ERROR:</h3>";
    echo "<strong>Pesan:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . " baris " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
