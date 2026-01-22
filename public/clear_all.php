<?php
/**
 * Script Pembersih Cache Total untuk Shared Hosting
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h3>Laravel Cache Cleaner</h3>";

require __DIR__.'/../../q-game-core/vendor/autoload.php';
$app = require __DIR__.'/../../q-game-core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Running commands...<br><pre>";

try {
    // 1. Clear Config (Paling Penting)
    echo "1. Config Clear: ";
    Illuminate\Support\Facades\Artisan::call('config:clear');
    echo Illuminate\Support\Facades\Artisan::output() . "<br>";

    // 2. Clear Cache
    echo "2. Cache Clear: ";
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo Illuminate\Support\Facades\Artisan::output() . "<br>";

    // 3. Clear View
    echo "3. View Clear: ";
    Illuminate\Support\Facades\Artisan::call('view:clear');
    echo Illuminate\Support\Facades\Artisan::output() . "<br>";

    // 4. Clear Route
    echo "4. Route Clear: ";
    Illuminate\Support\Facades\Artisan::call('route:clear');
    echo Illuminate\Support\Facades\Artisan::output() . "<br>";
    
    // 5. Optimize Clear
    echo "5. Optimize Clear: ";
    Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo Illuminate\Support\Facades\Artisan::output() . "<br>";

    echo "</pre><h3 style='color:green'>SELESAI. Silakan coba buka website Anda.</h3>";

} catch (Exception $e) {
    echo "</pre><h3 style='color:red'>ERROR: " . $e->getMessage() . "</h3>";
}
?>
