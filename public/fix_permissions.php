<?php
/**
 * Script Helper untuk Memperbaiki Permission File & Folder
 * Upload ke folder PUBLIC, jalankan sekali, lalu HAPUS.
 */

// Konfigurasi
$targetDir = __DIR__ . '/../../q-game-core'; // Sesuaikan lokasi core

echo "<h3>Permission Fixer Tool</h3>";
echo "Target Folder: " . realpath($targetDir) . "<br><hr>";

if (!is_dir($targetDir)) {
    die("<h3 style='color:red'>Folder target tidak ditemukan!</h3>");
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$countDir = 0;
$countFile = 0;

echo "<div style='font-family:monospace; font-size:12px; height:300px; overflow:auto; border:1px solid #ccc; padding:10px;'>";

foreach ($iterator as $item) {
    // Abaikan folder .git atau storage (storage biasanya butuh 775, tapi 755 seringkali cukup di shared hosting)
    if (strpos($item->getPathname(), '.git') !== false) continue;

    if ($item->isDir()) {
        chmod($item->getPathname(), 0755);
        // echo "DIR  [755]: " . $item->getPathname() . "<br>";
        $countDir++;
    } else {
        chmod($item->getPathname(), 0644);
        // echo "FILE [644]: " . $item->getPathname() . "<br>";
        $countFile++;
    }
}

echo "</div>";
echo "<hr>";
echo "<h3 style='color:green'>SELESAI!</h3>";
echo "Berhasil memperbaiki izin untuk:<br>";
echo "<b>$countDir</b> Folder (menjadi 755)<br>";
echo "<b>$countFile</b> File (menjadi 644)<br>";
echo "<br>Silakan coba jalankan <b>migrate.php</b> kembali.";
?>
