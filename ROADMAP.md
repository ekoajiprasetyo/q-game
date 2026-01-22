# 🎮 Q-Game: Game Tarik Tambang Pembelajaran

## 📋 Deskripsi Proyek
Aplikasi game edukatif berbasis web yang menggabungkan konsep permainan **tarik tambang (tug of war)** dengan sistem kuis pembelajaran untuk digunakan di kelas. 

### 🎯 Konsep Utama: **Split-Screen Group Battle**
- **Layar dibagi menjadi 2 bagian** untuk 2 kelompok yang berkompetisi secara bersamaan
- **Game ditampilkan di proyektor/layar besar** sehingga seluruh kelas bisa melihat
- **Kelompok vs Kelompok** - bukan individu vs individu
- Perwakilan dari masing-masing tim maju untuk menjawab pertanyaan
- Jawaban benar akan "menarik" tali ke arah tim tersebut

---

## 🚀 Development Status: **STABLE (Production Ready)**
> UI/UX Admin Panel dan Mekanisme Game telah stabil dan teruji. Fokus selanjutnya adalah optimasi performa dan fitur tambahan.

---

## 🏗️ Arsitektur Teknologi

### Frontend (Game Engine)
- **Phaser.js 3** - Game engine untuk membuat game 2D interaktif
- **HTML5 Canvas** - Rendering game split-screen
- **Vite** - Build tool & development server
- **TailwindCSS & Custom CSS** - Styling untuk UI admin/dashboard

### Backend (API & Data Management)
- **Laravel 10/11** - Framework PHP untuk REST API
- **MySQL** - Database utama

### Input System
- **Keyboard Shortcuts** - Tim Biru (A,S,D,F), Tim Merah (1,2,3,4)
- **Control Bar** - Mouse interaction

---

## 🎯 Progress Roadmap

### 📌 FASE 1: Fondasi Backend & Database
**Status:** ✅ **COMPLETED**
- [x] Database schema & migrations (users, topics, materials, questions, sessions, rounds)
- [x] Eloquent Models & Relationships
- [x] Database Seeders (users, sample topics/questions)
- [x] Authentication System (Admin/Teacher roles)
- [x] API Routes & Controllers

### 📌 FASE 2: Admin Panel & CRUD
**Status:** ✅ **COMPLETED**
- [x] **Dashboard**: Statistik ringkas & aktivitas terbaru
- [x] **Manajemen Topik**: Filter, Search, CRUD Modal, Color coding
- [x] **Manajemen Materi**: Hierarki Topik -> Materi, Assign Soal
- [x] **Bank Soal**:
  - [x] Rich Text Support (Summernote WYSIWYG Editor)
  - [x] KaTeX LaTeX Support (Math Formulas: $...$, $$...$$)
  - [x] Image Paste/Upload dalam Editor
  - [x] Tipe Soal (Pilihan Ganda, Isian, True/False)
  - [x] Filter canggih & Pagination
  - [x] Preview Modal Soal
- [x] **Manajemen User**: Role based access control (RBAC), Statistik Guru/Admin
- [x] **UI/UX Polish**:
  - [x] Consistent Orange Theme
  - [x] Responsive Cards & Tables
  - [x] Interactive Modals (Scrollable body, fixed header)

### 📌 FASE 3: Game Mechanics (Phaser.js)
**Status:** ✅ **COMPLETED**
- [x] Scene Management (Boot, Preload, Menu, Setup, Game, Result)
- [x] Split-screen layout implementation
- [x] **Gameplay Elements**:
  - [x] Rope/Tali Physics (Tarik-menarik)
  - [x] Karakter/Avatar Tim
  - [x] Timer Countdown & Animation
  - [x] Score Display
- [x] **Input Handling**: Keyboard listener & Visual feedback
- [x] **Logic**:
  - [x] Scoring System
  - [x] Win/Loss Condition
  - [x] Correct/Incorrect Feedback
- [x] Sound Effects Integration

### 📌 FASE 4: Game Session & History
**Status:** ✅ **COMPLETED**
- [x] Setup Game Session (Pilih Topik/Materi, Mode)
- [x] Generate Game PIN for Session
- [x] Result Page (Winner, Score breakdown)
- [x] **Review System**:
  - [x] Detail setiap ronde (Jawaban benar vs Jawaban tim)
  - [x] Waktu respon tiap tim
  - [x] Modal history di Admin Panel

---

### � FASE 5: Optimization & Hardening
**Status:** ✅ **COMPLETED** (Jan 2026)
- [x] **Performance Tuning**:
  - [x] **Database Indexing**: Optimasi query dengan index pada kolom kritikal (topic, material, PIN).
  - [x] **Asset Compression**: Otomatis konversi gambar upload ke format **WebP** & Resize.
  - [x] **Lazy Loading**: loading="lazy" pada gambar soal untuk mempercepat initial load.
- [x] **Security Enhancements**:
  - [x] **XSS Protection**: HTML Purifier service untuk sanitasi input soal dari script berbahaya.
  - [x] **Rate Limiting**: Proteksi login (5x/menit) dan spam submission (60x/menit).

---

## 💡 Rekomendasi Pengembangan Lanjut (Next Steps)

Berikut adalah langkah-langkah selanjutnya yang direkomendasikan untuk membuat aplikasi lebih kaya fitur:

### 1. � Laporan & Ekspor Data (Priority: High)
- [ ] **Export PDF/Excel**: Fitur untuk mendownload hasil game per sesi (rekap nilai) untuk kebutuhan administrasi guru.
- [ ] **Student Analytics**: (Future) Jika ada login siswa, track progress individual siswa.

### 2. 🛡️ Maintenance (Priority: Medium)
- [ ] **Data Backup**: Implementasi command scheduler untuk backup database otomatis harian/mingguan.
- [ ] **Cache Query**: Cache hasil query dashboard admin (statistik) yang berat agar loading lebih cepat (Redis/File Cache).

### 3. 🎮 Game Experience (Priority: Low - Nice to Have)
- [ ] **Power-Ups**: Tambahkan item "Freeze Time" atau "Double Score" untuk variasi gameplay.
- [ ] **Background Themes**: Pilihan tema visual (Hutan, Angkasa, Bawah Laut) yang bisa dipilih saat setup.
- [ ] **Tournament Mode**: Sistem bracket otomatis untuk kompetisi antar kelas.

### 4. 📱 Mobile Responsiveness (Priority: Medium)
- [ ] **Admin Mobile View**: Pastikan tabel dan modal di admin panel nyaman diakses via HP (untuk guru yang mobile).
- [ ] **PWA Support**: Jadikan aplikasi Installable (Progressive Web App) agar aset game tersimpan offline.

---

## 🗂️ Struktur File Utama (Updated)

```
q-game/
├── app/Http/Controllers/Admin/
│   ├── DashboardController.php   # Statistik & Recap
│   ├── TopicController.php       # Topik & Materi Parent
│   ├── MaterialController.php    # Materi & Game Session Link
│   ├── QuestionController.php    # Bank Soal
│   ├── UserController.php        # Manajemen Guru/Admin
│   └── GameSessionController.php # History & Review
├── resources/views/admin/
│   ├── dashboard.blade.php
│   ├── topics/                   # Index (Topic List)
│   ├── materials/                # Index (Material List inside Topic)
│   ├── questions/                # Index (Question Bank)
│   ├── sessions/                 # History & Modal Review
│   └── users/                    # User CRUD
├── resources/js/game/
│   ├── scenes/                   # Phaser Scenes (Boot, Game, Result, etc)
│   └── components/               # Game UI Components
└── routes/web.php                # Authentication & Admin Routes
```
