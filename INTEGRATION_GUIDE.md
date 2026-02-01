# Panduan Integrasi Q-Game dengan Q-Link

Dokumen ini menjelaskan bagaimana Q-Game terintegrasi dengan ekosistem Q-Link.

## 🔗 Arsitektur Integrasi

```
                    ┌─────────────────────────────────────────────────┐
                    │           DATABASE Q-LINK (englishh_qlink)       │
                    │                                                   │
                    │  ┌─────────┐  ┌─────────┐  ┌─────────┐          │
                    │  │  users  │  │sessions │  │  cache  │  ...     │
                    │  └─────────┘  └─────────┘  └─────────┘          │
                    │                                                   │
                    │  ┌─────────────────────────────────────────┐    │
                    │  │         TABEL Q-GAME                     │    │
                    │  │  topics, materials, questions,           │    │
                    │  │  game_sessions, game_rounds,             │    │
                    │  │  tournaments, tournament_teams,          │    │
                    │  │  tournament_matches                      │    │
                    │  └─────────────────────────────────────────┘    │
                    └─────────────────────────────────────────────────┘
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
            ┌───────┴───────┐     ┌───────┴───────┐    ┌───────┴───────┐
            │   Q-Link      │     │   Q-Exam      │    │   Q-Game      │
            │ q-link.my.id  │     │exam.q-link.my │    │game.q-link.my │
            └───────────────┘     └───────────────┘    └───────────────┘
```

## 📦 Konfigurasi Database

### Development (Lokal)
File: `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_qlink
DB_USERNAME=root
DB_PASSWORD=
```

### Production (Hosting)
File: `.env.production`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=englishh_qlink
DB_USERNAME=englishh_quser
DB_PASSWORD=[PASSWORD_HOSTING]
```

## 👤 Mapping Role User

Q-Game menggunakan role yang sama dengan Q-Link:

| Role | Nama | Akses Q-Game |
|------|------|--------------|
| `admin` | Administrator | ✅ Full akses admin panel |
| `guru` | Guru | ✅ Full akses admin panel |
| `user` | Siswa | ❌ Tidak bisa login ke admin |

**Catatan**: Di Q-Game sebelumnya menggunakan `teacher`, sekarang sudah diganti ke `guru` agar konsisten dengan Q-Link.

## 🍪 Session Sharing (SSO)

Untuk SSO antar subdomain, konfigurasi `SESSION_DOMAIN`:

```env
# Production
SESSION_DOMAIN=.q-link.my.id
```

Ini memungkinkan session dibagi antara:
- `q-link.my.id`
- `game.q-link.my.id`
- `exam.q-link.my.id`

## 📋 Tabel Database Q-Game

Tabel-tabel berikut adalah milik Q-Game (akan dibuat saat migrasi):

| Tabel | Deskripsi |
|-------|-----------|
| `topics` | Topik/Kategori soal |
| `materials` | Materi pembelajaran |
| `questions` | Bank soal |
| `game_sessions` | Sesi permainan |
| `game_rounds` | Ronde dalam sesi |
| `tournaments` | Data turnamen |
| `tournament_teams` | Tim peserta turnamen |
| `tournament_matches` | Pertandingan turnamen |

Tabel-tabel berikut **TIDAK** dibuat ulang oleh Q-Game (milik Q-Link):
- `users`
- `sessions`
- `password_reset_tokens`
- `cache`
- `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`

## 🚀 Langkah Deployment

### 1. Persiapan di Hosting
- Pastikan database Q-Link (`englishh_qlink`) sudah ada
- User database memiliki akses ke tabel-tabel Q-Game

### 2. Upload File
Upload semua file Q-Game ke hosting (folder `q-game-core`)

### 3. Konfigurasi .env
```bash
# Copy dan edit
cp .env.production .env

# Edit dengan credential hosting
DB_DATABASE=englishh_qlink
DB_USERNAME=englishh_quser
DB_PASSWORD=isi_password_anda
```

### 4. Jalankan Migrasi
Migrasi hanya akan membuat tabel-tabel Q-Game, tidak mengganggu tabel Q-Link:
```bash
php artisan migrate --force
```

### 5. Generate App Key
```bash
php artisan key:generate --force
```

### 6. Optimize
```bash
php artisan optimize
```

## ⚠️ Catatan Penting

1. **Jangan hapus tabel users dari Q-Link!** Q-Game menggunakan tabel users yang sama.
2. **Backup database sebelum migrasi** untuk berjaga-jaga.
3. **SESSION_DOMAIN wajib diset** di production untuk SSO berfungsi.
4. **User dengan role `user` (siswa)** tidak bisa login ke admin panel Q-Game.

## 🔧 Troubleshooting

### Login tidak berhasil dengan akun Q-Link
- Pastikan role akun adalah `admin` atau `guru`
- Cek apakah SESSION_DOMAIN sudah diset dengan benar

### Tabel users tidak ditemukan
- Migrasi Q-Link harus dijalankan terlebih dahulu
- Q-Game bergantung pada tabel users dari Q-Link

### Foreign key error saat migrasi
- Pastikan tabel `users` sudah ada sebelum menjalankan migrasi Q-Game
