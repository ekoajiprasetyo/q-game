# Panduan Deployment Q-Game ke game.q-link.my.id

Dokumen ini berisi langkah-langkah untuk mengupload dan mengkonfigurasi aplikasi Q-Game di hosting cPanel/Shared Hosting.

## 1. Persiapan File (Di Komputer Lokal)

Saya telah menyiapkan script untuk membuat paket deployment. Jalankan perintah berikut di terminal (jika belum):
`./prepare_package.ps1` (Akan saya buatkan script ini).

Ini akan membuat file `q-game-deployment.zip` yang bersih (tanpa folder development yang tidak perlu).

## 2. Persiapan Database (Di cPanel)

1.  Login ke cPanel hosting Anda.
2.  Buka **MySQL Database Wizard**.
3.  Buat database baru, misalnya: `uXXXX_qgame`.
4.  Buat user database baru, misalnya: `uXXXX_quser`.
5.  Berikan password yang kuat. **Simpan detail ini!**
6.  Assign user ke database dengan hak akses **ALL PRIVILEGES**.

## 3. Upload File

1.  Buka **File Manager** di cPanel.
2.  Masuk ke root directory (biasanya di luar `public_html`, sejajar dengan folder tersebut).
3.  Buat folder baru bernama `q-game-core`.
4.  Upload `q-game-deployment.zip` ke dalam folder `q-game-core`.
5.  Extract file zip tersebut di sana.

Struktur folder Anda seharusnya terlihat seperti ini:
```
/
├── public_html/
│   └── ...
├── q-game-core/
│   ├── app/
│   ├── bootstrap/
│   ├── ...
│   └── .env.production
└── ...
```

## 4. Konfigurasi Aplikasi

1.  Masuk ke folder `q-game-core`.
2.  Rename file `.env.production` menjadi `.env`.
3.  Edit file `.env` tersebut:
    *   Set `APP_URL=https://game.q-link.my.id`
    *   Isi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai yang Anda buat di langkah 2.
    *   Pastikan `APP_DEBUG=false`.

## 5. Mengatur Folder Publik (Public HTML)

Karena ini adalah subdomain (`game.q-link.my.id`), kita perlu meletakkan file publik di tempat yang diarahkan oleh subdomain tersebut.

1.  Tentukan folder root dokumen untuk subdomain `game.q-link.my.id`. Biasanya ada di `public_html/game` atau folder khusus yang Anda set saat membuat subdomain.
2.  Pindahkan **seluruh isi** folder `q-game-core/public` ke folder root subdomain tersebut (misalnya `public_html/game`).
3.  Edit file `index.php` yang baru saja Anda pindahkan (di `public_html/game/index.php`):

    Cari baris ini:
    ```php
    require __DIR__.'/../vendor/autoload.php';
    ...
    $app = require __DIR__.'/../bootstrap/app.php';
    ```

    Ubah menjadi (sesuaikan path agar mengarah kembali ke folder core):
    ```php
    require __DIR__.'/../../q-game-core/vendor/autoload.php';
    ...
    $app = require __DIR__.'/../../q-game-core/bootstrap/app.php';
    ```
    *(Jumlah `../` tergantung seberapa dalam folder subdomain Anda dari root).*

## 6. Setup Storage Link

Agar gambar/file bisa diakses, Anda perlu membuat symlink.
Di cPanel, seringkali kita tidak punya akses SSH. Anda bisa menggunakan route khusus atau script PHP sederhana.

**Cara Script PHP:**
1.  Buat file `link.php` di folder publik subdomain (`public_html/game/link.php`).
2.  Isi dengan:
    ```php
    <?php
    $target = '/home/username_cpanel/q-game-core/storage/app/public';
    $shortcut = '/home/username_cpanel/public_html/game/storage';
    symlink($target, $shortcut);
    echo "Symlink created";
    ?>
    ```
    *(Ganti path sesuai struktur hosting Anda. Anda bisa melihat path lengkap di sidebar kiri File Manager).*
3.  Buka browser: `https://game.q-link.my.id/link.php`.
4.  Jika sukses, hapus file `link.php`.

## 7. Migrasi Database

Karena Anda menggunakan MySQL di hosting dan SQLite di lokal, Anda perlu menjalankan migrasi.

**Opsi A: Via SSH (Jika ada)**
1.  `cd q-game-core`
2.  `php artisan migrate --force`

**Opsi B: Via Route (Hati-hati, hapus setelah pakai)**
1.  Buat file `migrate.php` di folder publik.
2.  Isi dengan:
    ```php
    <?php
    require __DIR__.'/../../q-game-core/vendor/autoload.php';
    $app = require __DIR__.'/../../q-game-core/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    // Panggil artisan
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo nl2br(Illuminate\Support\Facades\Artisan::output());
    ?>
    ```
3.  Akses di browser.
4.  **HAPUS SEGERA** setelah selesai.

Selesai! Aplikasi Anda siap digunakan.
