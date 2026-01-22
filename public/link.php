<?php
/**
 * Script Helper untuk Membuat Symlink Storage di Hosting Shared
 * Upload file ini ke folder PUBLIC subdomain Anda (bersama index.php)
 */

echo "<h3>Storage Link Helper</h3>";

// 1. Deteksi folder dimana file ini berada (Folder Publik Subdomain)
$publicFolder = __DIR__;
echo "File ini berada di: <code>" . $publicFolder . "</code><br>";

// 2. Tentukan lokasi folder Core Aplikasi
// Sesuai panduan, kita asumsikan strukturnya:
// /home/user/q-game-core       <-- Core
// /home/user/public_html/game  <-- Public (disini)
// Jadi dari sini, kita perlu naik 2 level (../../) lalu masuk q-game-core
$coreFolderRelative = $publicFolder . '/../../q-game-core';

// Cek apakah foldernya ketemu
$storageTarget = realpath($coreFolderRelative . '/storage/app/public');

if ($storageTarget) {
    echo "Folder Storage asli ditemukan di: <code>" . $storageTarget . "</code><br><br>";
} else {
    echo "<div style='color:red; font-weight:bold'>ERROR: Folder Storage asli TIDAK DITEMUKAN!</div>";
    echo "Sistem mencari di: <code>" . $coreFolderRelative . '/storage/app/public' . "</code><br>";
    echo "Pastikan: <br>";
    echo "1. Anda menamai folder core sebagai 'q-game-core'.<br>";
    echo "2. Folder 'q-game-core' sejajar dengan folder 'public_html'.<br>";
    echo "3. Jika nama folder beda, edit file link.php baris 18 sesuai struktur Anda.<br>";
    die();
}

// 3. Tentukan lokasi Shortcut yang ingin dibuat
$linkPath = $publicFolder . '/storage';

// 4. Cek apakah link sudah ada
if (file_exists($linkPath)) {
    echo "<div style='color:orange'>PERINGATAN: Folder/Link 'storage' sudah ada di folder publik ini.</div>";
    echo "Jika Anda yakin itu salah/rusak, hapus dulu lewat File Manager di cPanel, lalu refresh halaman ini.<br>";
} else {
    // 5. Eksekusi pembuatan link
    try {
        symlink($storageTarget, $linkPath);
        echo "<div style='color:green; font-weight:bold; font-size:1.2em'>SUKSES! Symlink berhasil dibuat.</div>";
        echo "Sekarang file media harusnya bisa diakses.<br>";
        echo "<br><strong>PENTING:</strong> Segera hapus file link.php ini dari hosting Anda.";
    } catch (Exception $e) {
        echo "<div style='color:red'>GAGAL: " . $e->getMessage() . "</div>";
    }
}
?>
