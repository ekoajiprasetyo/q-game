# Panduan Deployment Q-Game ke game.q-link.my.id

Dokumen ini berisi langkah-langkah untuk mengupload dan mengkonfigurasi aplikasi Q-Game di hosting cPanel/Shared Hosting.

> **PENTING**: Q-Game terintegrasi dengan database Q-Link. Lihat [INTEGRATION_GUIDE.md](./INTEGRATION_GUIDE.md) untuk detail teknis.

## 1. Persiapan (Komputer Lokal)

### Build Assets Frontend
```powershell
npm run build
```

### Buat Paket Deployment
Jalankan script berikut untuk membuat file zip yang siap upload:
```powershell
./prepare_package.ps1
```
Ini akan membuat file `q-game-deployment.zip` yang bersih.

## 2. Konfigurasi Database

**Q-Game menggunakan database Q-Link yang sama!**

Di hosting, database sudah ada:
- **Database Name**: `englishh_qlink`
- **Username**: `englishh_quser` (atau sesuai konfigurasi)

Anda **TIDAK** perlu membuat database baru. Cukup pastikan user database memiliki akses ke tabel Q-Game.

## 3. Upload File

1. Buka **File Manager** di cPanel.
2. Masuk ke root directory (sejajar dengan `public_html`).
3. Buat folder `q-game-core` (jika belum ada).
4. Upload `q-game-deployment.zip` ke dalam folder tersebut.
5. Extract file zip.

Struktur folder:
```
/
├── public_html/
│   └── game/          <- Folder publik subdomain
├── q-game-core/       <- Folder aplikasi Laravel
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── ...
│   └── .env.production
└── ...
```

## 4. Konfigurasi Aplikasi

1. Masuk ke folder `q-game-core`.
2. Rename `.env.production` menjadi `.env`.
3. Edit file `.env`:

```env
APP_KEY=                          # Akan di-generate
APP_URL=https://game.q-link.my.id
APP_DEBUG=false

# Database Q-Link
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=englishh_qlink        # Database Q-Link
DB_USERNAME=englishh_quser        # Username database
DB_PASSWORD=PASSWORD_ANDA         # ← ISI PASSWORD

# SSO dengan Q-Link
SESSION_DOMAIN=.q-link.my.id
```

## 5. Konfigurasi Folder Publik

### Untuk subdomain `game.q-link.my.id`

1. Tentukan folder root subdomain (biasanya `public_html/game`).
2. Pindahkan **seluruh isi** `q-game-core/public` ke folder subdomain tersebut.
3. Edit `index.php` di folder subdomain:

```php
// Ubah path ini:
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';

// Menjadi (sesuaikan path):
require __DIR__.'/../../q-game-core/vendor/autoload.php';
$app = require __DIR__.'/../../q-game-core/bootstrap/app.php';
```

## 6. Setup Storage Symlink

Buat symlink untuk mengakses file storage:

**Via Script PHP (Jika tidak ada SSH):**

1. Buat file `link.php` di folder publik subdomain:
```php
<?php
$target = '/home/USERNAME_CPANEL/q-game-core/storage/app/public';
$shortcut = '/home/USERNAME_CPANEL/public_html/game/storage';
symlink($target, $shortcut);
echo "Symlink created!";
?>
```

2. Akses: `https://game.q-link.my.id/link.php`
3. **HAPUS file `link.php`** setelah berhasil.

## 7. Migrasi Database

Migrasi Q-Game **HANYA membuat tabel-tabel milik Q-Game**. Tabel `users`, `sessions`, dll tetap dari Q-Link.

**Via SSH (Jika ada):**
```bash
cd q-game-core
php artisan key:generate --force
php artisan migrate --force
php artisan optimize
```

**Via Script PHP (Tanpa SSH):**

Buat `migrate.php`:
```php
<?php
require __DIR__.'/../../q-game-core/vendor/autoload.php';
$app = require __DIR__.'/../../q-game-core/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

// Generate key jika belum ada
Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
echo "Key generated<br>";

// Migrate
Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo nl2br(Illuminate\Support\Facades\Artisan::output());

// Optimize
Illuminate\Support\Facades\Artisan::call('optimize');
echo "<br>Optimized!";
?>
```

Akses di browser, lalu **HAPUS SEGERA** setelah selesai!

## 8. Verifikasi

1. Buka `https://game.q-link.my.id`
2. Coba login dengan akun **guru** atau **admin** dari Q-Link
3. Pastikan halaman game dan admin dapat diakses

## ⚠️ Catatan Penting

- **Role `user` (siswa)** tidak bisa login ke admin panel Q-Game
- **Hanya `admin` dan `guru`** yang bisa mengakses panel admin
- Session akan **shared** dengan Q-Link berkat `SESSION_DOMAIN=.q-link.my.id`

## 🆘 Troubleshooting

### Login gagal padahal akun benar
- Cek apakah role akun adalah `admin` atau `guru`
- Pastikan `SESSION_DOMAIN` sudah diset

### Error "SQLSTATE[42S02]: Table not found"
- Jalankan migrasi terlebih dahulu
- Pastikan koneksi database benar

### Asset tidak muncul (CSS/JS 404)
- Pastikan `npm run build` sudah dijalankan
- Cek path di `index.php`

---

Selesai! Aplikasi Q-Game sekarang terintegrasi dengan Q-Link. 🎉
