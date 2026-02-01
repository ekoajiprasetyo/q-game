# Daftar File yang Perlu Diupload ke Hosting

Update terakhir: 2026-02-01

## 📋 Perubahan Utama

1. **Tabel di-rename dengan prefix `game_`** untuk menghindari konflik dengan Q-Exam
2. **Model di-update** dengan explicit `$table` property
3. **Migrasi di-update** untuk tabel baru
4. **Role `teacher` → `guru`** untuk kompatibilitas dengan Q-Link

---

## 📁 File yang WAJIB Diupload

### 1. Database Migrations (folder `database/migrations/`)

| File | Keterangan |
|------|------------|
| `0001_01_01_000000_create_users_table.php` | SKIP (tabel ada di Q-Link) |
| `0001_01_01_000001_create_cache_table.php` | SKIP (tabel ada di Q-Link) |
| `0001_01_01_000002_create_jobs_table.php` | SKIP (tabel ada di Q-Link) |
| `2026_01_06_080309_create_topics_table.php` | `game_topics` (RENAMED) |
| `2026_01_06_080311_create_questions_table.php` | `game_questions` (RENAMED) |
| `2026_01_06_080312_create_game_sessions_table.php` | Updated foreign key |
| `2026_01_06_080314_create_game_rounds_table.php` | Updated foreign key |
| `2026_01_11_005101_create_materials_table.php` | `game_materials` (RENAMED) |
| `2026_01_11_150000_add_pin_and_material_to_game_sessions_table.php` | Tidak berubah |
| `2026_01_19_100000_add_performance_indexes.php` | Updated table names |
| `2026_01_19_123953_create_tournament_tables.php` | `game_tournaments` (RENAMED) |
| `2026_01_20_000001_add_pin_and_match_config_to_tournaments.php` | Updated table names |
| `2026_01_21_205535_update_game_mode_enum_in_game_sessions_table.php` | Tidak berubah |

### 2. Models (folder `app/Models/`)

| File | Perubahan |
|------|-----------|
| `User.php` | Role `guru`, field Q-Link, helper methods |
| `Topic.php` | `$table = 'game_topics'` |
| `Material.php` | `$table = 'game_materials'` |
| `Question.php` | `$table = 'game_questions'` |
| `Tournament.php` | `$table = 'game_tournaments'` |
| `TournamentTeam.php` | `$table = 'game_tournament_teams'` |
| `TournamentMatch.php` | `$table = 'game_tournament_matches'` |
| `GameSession.php` | Tidak perlu diubah (sudah pakai `game_sessions`) |
| `GameRound.php` | Tidak perlu diubah (sudah pakai `game_rounds`) |

### 3. Controllers (folder `app/Http/Controllers/Admin/`)

| File | Perubahan |
|------|-----------|
| `UserController.php` | Role `guru`, filter admin+guru |

### 4. Middleware (folder `app/Http/Middleware/`)

| File | Perubahan |
|------|-----------|
| `CheckRole.php` | **FILE BARU** |
| `IsAdmin.php` | Tidak berubah |

### 5. Bootstrap

| File | Perubahan |
|------|-----------|
| `bootstrap/app.php` | Registrasi middleware `role` |

### 6. Views
| File | Perubahan |
|------|-----------|
| `resources/views/admin/users/index.blade.php` | `value="guru"`, `$totalGuru` |
| `resources/views/game/index.blade.php` | Tombol SSO Login & Toast Notification |

---

## 🔧 Konfigurasi di Hosting

### Edit `.env` di hosting:

```env
# Ganti ke database Q-Link
DB_DATABASE=englishh_qlink
DB_USERNAME=englishh_quser
DB_PASSWORD=[ISI_PASSWORD]

# SSO dengan Q-Link
SESSION_DOMAIN=.q-link.my.id
```

---

## 🚀 Langkah Setelah Upload

### 1. Hapus tabel lama Q-Game (opsional)

Jika database Q-Game lama masih ada terpisah, Anda bisa menghapusnya.

### 2. Jalankan migrasi

```bash
php artisan migrate --force
```

Atau via browser: buat file `migrate.php` sementara.

### 3. Clear cache

```bash
php artisan optimize:clear
php artisan optimize
```

### 4. Test login

Login dengan akun guru/admin dari Q-Link.

---

## 📊 Mapping Tabel Lama → Baru

| Tabel Lama | Tabel Baru | Database |
|------------|------------|----------|
| `topics` | `game_topics` | Q-Link |
| `materials` | `game_materials` | Q-Link |
| `questions` | `game_questions` | Q-Link |
| `game_sessions` | `game_sessions` | Q-Link (tetap) |
| `game_rounds` | `game_rounds` | Q-Link (tetap) |
| `tournaments` | `game_tournaments` | Q-Link |
| `tournament_teams` | `game_tournament_teams` | Q-Link |
| `tournament_matches` | `game_tournament_matches` | Q-Link |
| `users` | `users` | Q-Link (shared) |
| `sessions` | `sessions` | Q-Link (shared) |

---

## ⚠️ Data Lama

Jika ada data di hosting Q-Game yang perlu dipertahankan, Anda perlu:
1. Export dari database lama
2. Rename kolom yang berubah
3. Import ke database Q-Link

Kolom `created_by` di tabel-tabel perlu disesuaikan dengan ID user di Q-Link.
