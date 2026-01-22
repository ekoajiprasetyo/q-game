<?php
/**
 * Script untuk Menjalankan Seeder (Mengisi Data Awal)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h3>Database Seeder Tool</h3>";

require __DIR__.'/../../q-game-core/vendor/autoload.php';
$app = require __DIR__.'/../../q-game-core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    echo "Menjalankan UserSeeder...<br>";
    
    // Panggil spesifik class UserSeeder
    Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\UserSeeder',
        '--force' => true 
    ]);
    
    echo "<pre>" . Illuminate\Support\Facades\Artisan::output() . "</pre>";
    
    echo "<div style='color:green; font-weight:bold; font-size:1.2em'>SUKSES!</div>";
    echo "User Admin (admin@qgame.com) dan Guru (guru@qgame.com) telah dibuat.<br>";
    echo "Silakan Login sekarang.";

} catch (Exception $e) {
    echo "<div style='color:red'>ERROR: " . $e->getMessage() . "</div>";
}
?>
