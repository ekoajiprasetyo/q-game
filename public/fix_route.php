<?php
// FILE: public/fix_route.php
// Upload ke folder public_html/game/
// Akses: https://game.q-link.my.id/fix_route.php

require __DIR__ . '/../../q-game-core/vendor/autoload.php';
$app = require __DIR__ . '/../../q-game-core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

echo "<h1>Clearing Caches...</h1>";

try {
    // 1. Clear Route Cache (PENTING!)
    Illuminate\Support\Facades\Artisan::call('route:clear');
    echo "Route Cache: <span style='color:green'>CLEARED</span><br>";

    // 2. Clear Config cache
    Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "Config Cache: <span style='color:green'>CLEARED</span><br>";
    
    // 3. Clear View Cache
    Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "View Cache: <span style='color:green'>CLEARED</span><br>";

    echo "<br><strong>Done! Coba login siswa lagi sekarang.</strong>";
    echo "<br><br>⚠️ JANGAN LUPA HAPUS FILE INI!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
