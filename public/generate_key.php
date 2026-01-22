<?php
/**
 * Script Helper untuk Men-generate Application Key
 */

require __DIR__.'/../../q-game-core/vendor/autoload.php';
$app = require __DIR__.'/../../q-game-core/bootstrap/app.php';

$app->make(Illuminate\Contracts\Http\Kernel::class);

echo "<h3>Laravel Application Key Generator</h3>";

// Generate Key
$key = 'base64:'.base64_encode(
    Symfony\Component\Encryption\Encrypter::generateKey(config('app.cipher'))
);

echo "Copy kode di bawah ini dan paste ke file <b>.env</b> di bagian <b>APP_KEY=</b><br><br>";
echo "<textarea rows='3' cols='60' style='font-size:1.5em; padding:10px;'>$key</textarea>";
echo "<br><br>Setelah di-save di .env, aplikasi Anda seharusnya sudah berjalan normal.";
?>
