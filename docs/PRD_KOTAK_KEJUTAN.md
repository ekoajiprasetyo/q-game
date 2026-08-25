# PRD — Kotak Kejutan

**Produk:** Q-Game
**Fitur:** Game kedua — Kotak Kejutan
**Status:** Draft siap implementasi
**Bahasa produk:** Indonesia
**Pemilik:** Q-Link / Q-Game

## 1. Ringkasan

Kotak Kejutan adalah permainan kuis berbasis papan kartu dan giliran tim.
Guru memilih bank soal, menyiapkan 2–8 tim, lalu setiap tim membuka satu
kartu. Kartu dapat berisi pertanyaan atau efek kejutan yang memengaruhi skor.
Guru memoderasi jawaban dan menentukan hasil setiap pertanyaan.

Nama yang ditampilkan pada kartu pilihan game:

> **Kotak Kejutan**
> Pilih kartu, jawab pertanyaan, dan kumpulkan poin bersama tim.

Fitur ini terinspirasi oleh pola permainan papan kuis kelas, tetapi memakai
nama, visual, ikon, animasi, aturan, dan implementasi milik Q-Game sendiri.
Tidak memakai merek, aset, maupun kode pihak lain.

## 2. Masalah dan peluang

Saat ini Q-Game hanya menyediakan Tarik Tambang: permainan dua tim dengan
jawaban yang diproses cepat. Guru membutuhkan format yang:

- dapat dimainkan banyak tim;
- memberi ruang diskusi sebelum jawaban disahkan;
- tetap memakai bank soal Q-Game yang sama;
- menarik saat diproyeksikan di kelas;
- mudah dikendalikan satu guru tanpa perangkat siswa.

Kotak Kejutan memenuhi kebutuhan tersebut melalui pilihan kartu, giliran,
skor, dan efek acak yang dapat diatur tingkat kompetitifnya.

## 3. Tujuan

### Tujuan produk

- Menambah jenis permainan kedua yang berbeda jelas dari Tarik Tambang.
- Memungkinkan 2–8 tim bermain memakai bank soal yang sudah tersedia.
- Menjaga guru sebagai moderator hasil jawaban.
- Mendukung permainan tanpa data awal: topik, materi, dan soal dibuat guru.
- Memakai arsitektur Q-Game yang sudah terintegrasi dengan Q-Link.

### Metrik keberhasilan awal

- Guru dapat membuat dan menyelesaikan satu permainan tanpa bantuan teknis.
- Tidak ada perubahan pada tabel atau alur Tarik Tambang yang ada.
- Semua kartu memiliki hasil yang tercatat di riwayat permainan.
- Game dapat selesai dan pemenang dihitung konsisten untuk 2–8 tim.

### Bukan tujuan fase pertama

- Multiplayer mandiri dari perangkat siswa.
- Jawaban sinkron real-time/WebSocket.
- Marketplace atau berbagi set permainan publik.
- AI pembuat soal.
- Menyalin tampilan atau daftar efek dari produk lain.

## 4. Pengguna dan hak akses

| Pengguna | Peran |
|---|---|
| Admin | Membuat/mengelola seluruh konten dan semua permainan. |
| Guru | Membuat konten sendiri dan menjalankan permainan sendiri. |
| Siswa/peserta | Melihat layar permainan; tidak login dan tidak mengubah state. |

Aturan kepemilikan mengikuti pola Q-Game saat ini: admin dapat melihat semua
konten; guru dibatasi pada konten dan permainan yang dibuatnya.

## 5. Pengalaman pengguna

### 5.1 Kartu pilihan game

Halaman `/game/setup` menampilkan dua kartu:

1. **Tarik Tambang** — alur yang ada, tidak diubah.
2. **Kotak Kejutan** — subtitle: *Pilih kartu, jawab pertanyaan, dan
   kumpulkan poin bersama tim.*

Memilih Kotak Kejutan membuka setup khusus permainan ini.

### 5.2 Setup guru

Guru mengisi:

- topik wajib;
- materi opsional (jika dipilih, soal hanya dari materi tersebut);
- judul permainan opsional;
- jumlah tim: 2–8;
- nama dan warna tiap tim;
- ukuran papan rekomendasi yang berubah menurut jumlah tim dan ketersediaan
  soal, dengan batas maksimal 36 kartu;
- mode permainan: Quiz, Klasik, atau Ramah;
- isi setiap slot kartu kejutan pada mode Klasik/Ramah; posisi kartu tetap
  diacak, tetapi hanya efek yang dipilih guru yang dapat muncul;
- nilai dasar pertanyaan: memakai `points` pada soal;
- batas waktu opsional per pertanyaan;
- opsi acak urutan soal;
- PIN permainan enam digit untuk membuka layar permainan.

Validasi sebelum mulai:

- jumlah soal yang tersedia minimal sama dengan jumlah kartu pertanyaan;
- nama tim tidak boleh kosong atau duplikat;
- jumlah tim harus sesuai batas mode;
- hanya guru pemilik atau admin yang boleh memulai/mengubah permainan.

### 5.3 Alur permainan utama

1. Layar menampilkan papan kartu tertutup, skor semua tim, dan indikator
   giliran.
2. Tim aktif memilih satu kartu yang belum dibuka.
3. Jika kartu adalah pertanyaan, layar menampilkan soal, gambar/audio bila
   ada, dan timer bila diaktifkan.
4. Guru menekan **Benar** atau **Salah** setelah diskusi/jawaban tim.
5. Skor atau efek diterapkan dan direkam.
6. Kartu berubah menjadi status selesai; giliran pindah ke tim berikutnya.
7. Setelah semua kartu selesai, atau guru mengakhiri permainan, sistem
   menampilkan peringkat dan riwayat singkat.

Guru selalu dapat menjeda, melanjutkan, atau mengakhiri permainan. Aksi
destruktif seperti mengakhiri permainan memerlukan konfirmasi.

## 6. Mode permainan

| Mode | Isi papan | Tujuan |
|---|---|---|
| **Quiz** | Semua kartu adalah pertanyaan. | Kuis adil tanpa efek acak. |
| **Klasik** | Pertanyaan dan power-up seimbang. | Kompetitif, penuh kejutan. |
| **Ramah** | Pertanyaan, bonus positif, dan efek netral. | Cocok untuk siswa lebih muda atau kelas yang tidak ingin pengurangan skor. |

Konfigurasi awal yang disarankan:

| Ukuran papan | Quiz | Klasik | Ramah |
|---:|---:|---:|---:|
| 8 | 8 soal | 6 soal + 2 efek | 6 soal + 2 efek |
| 12 | 12 soal | 9 soal + 3 efek | 9 soal + 3 efek |
| 16 | 16 soal | 12 soal + 4 efek | 12 soal + 4 efek |
| 24 | 24 soal | 18 soal + 6 efek | 18 soal + 6 efek |
| 36 | 36 soal | 27 soal + 9 efek | 27 soal + 9 efek |

Papan rekomendasi harus membagi jumlah kartu secara merata kepada tim dan
hanya muncul bila bank soal mencukupi. Konfigurasi awal: 2/4 tim memakai
`8, 16, 24, 36`; 3 tim memakai `9, 15, 24, 36`; 5 tim memakai
`10, 15, 20, 25, 30, 35`; 6 tim memakai `12, 18, 24, 30, 36`; 7 tim memakai
`14, 21, 28, 35`; dan 8 tim memakai `8, 16, 24, 32`.

Guru memilih kandidat efek saat setup. Jumlah slot mengikuti ukuran papan;
guru harus memilih sedikitnya sejumlah slot yang dibutuhkan dan sistem memilih
secara acak tanpa duplikasi. Daftar kandidat dan kartu yang akhirnya terpilih
disimpan pada konfigurasi sesi agar permainan dapat diaudit.

## 7. Power-up Q-Game

Nama, ikon, dan visual dibuat khusus Q-Game. Nilai bonus acak harus memakai
rentang yang dikonfigurasi server, bukan dipercaya dari browser.

### Variasi kartu standar

| Kode | Nama tampilan | Mode | Efek |
|---|---|---|---|
| `gift_points` | Hadiah Poin | Klasik, Ramah | Tambah poin acak 5–25. |
| `gold_bonus` | Harta Karun | Klasik, Ramah | Tambah 50 poin. |
| `double_score` | Pengganda Skor | Klasik, Ramah | Skor tim aktif menjadi dua kali lipat. |
| `target_bonus` | Hadiah untuk Lawan | Klasik, Ramah | Tim lawan pilihan mendapat 5–25 poin tanpa mengurangi skor tim aktif. |
| `give_points` | Berbagi Poin | Klasik, Ramah | Pindahkan 5–25 poin dari tim aktif kepada tim lawan pilihan. |
| `reset_self` | Skor Nol | Klasik, Ramah | Skor tim aktif menjadi 0. |
| `rise_to_top` | Loncat Teratas | Klasik, Ramah | Skor tim aktif menjadi satu poin di atas skor tertinggi. |
| `blank` | Kejutan Kosong | Klasik, Ramah | Tidak mendapat poin dan giliran berakhir. |
| `self_penalty` | Rugi Acak | Klasik, Ramah | Tim aktif kehilangan 5–25 poin. |
| `big_penalty` | Penalti Besar | Klasik, Ramah | Tim aktif kehilangan 50 poin. |
| `take_points` | Tarik Poin | Klasik | Pindahkan 5–25 poin dari tim lawan pilihan ke tim aktif. |
| `take_all_points` | Sapu Poin | Klasik | Pindahkan seluruh poin tim lawan pilihan ke tim aktif. |
| `target_penalty` | Pengurang Skor | Klasik | Tim lawan pilihan kehilangan 5–25 poin. |
| `swap_scores` | Tukar Skor | Klasik | Tukar total skor tim aktif dengan tim lawan pilihan. |
| `reset_all` | Reset Bersama | Klasik | Semua skor tim menjadi 0. |
| `drop_to_bottom` | Jatuh Terbawah | Klasik | Skor tim aktif menjadi satu poin di bawah skor terendah. |

### Aturan keselamatan dan keadilan

- Mode Ramah tidak memuat efek yang mengurangi skor tim lawan. Risiko, bila
  muncul, hanya diterapkan pada tim yang membuka kartu.
- Skor dapat bernilai negatif karena kartu `Jatuh Terbawah`, `Rugi Acak`, dan
  `Penalti Besar` harus tetap bermakna saat skor awal 0.
- Efek yang membutuhkan tim target harus meminta pilihan guru.
- Guru dapat menonaktifkan seluruh power-up saat setup, yang setara mode Quiz.
- Detail random (nilai bonus, target otomatis) disimpan sebagai payload hasil.

## 8. Aturan giliran dan skor

- Urutan tim mengikuti setup dan berputar secara circular.
- Kartu selesai tidak dapat dipilih kembali.
- Jawaban benar memberi `question.points`; jawaban salah memberi 0 poin.
- Guru dapat membatalkan kartu yang sedang terbuka sebelum disahkan.
- Pemenang adalah tim dengan skor tertinggi. Jika seri, semua tim seri
  ditampilkan sebagai pemenang; fase pertama tidak memakai tie-breaker.

## 9. Desain teknis

### 9.1 Prinsip integrasi

- Database: `qlink_prod` PostgreSQL.
- Schema Q-Game: `q_game` dengan search path `q_game,core,public`.
- Identitas bersama: `core.users`; session SSO: `core.sessions`.
- Semua tabel baru memakai schema `q_game` tanpa prefix aplikasi redundan.
- Tidak mengubah tabel `q_game.game_sessions` dan `q_game.game_rounds` milik
  Tarik Tambang.

### 9.2 Tabel baru

Nama bersifat rancangan awal dan mengikuti konvensi Q-Exam/Q-Space.

#### `surprise_sessions`

Mewakili satu permainan Kotak Kejutan.

| Kolom | Keterangan |
|---|---|
| `id` | Primary key. |
| `title` | Judul opsional. |
| `session_pin` | PIN unik enam digit. |
| `status` | `waiting`, `active`, `paused`, `finished`, `cancelled`. |
| `mode` | `quiz`, `classic`, `friendly`. |
| `topic_id` | FK ke `q_game.topics`. |
| `material_id` | FK nullable ke `q_game.materials`. |
| `board_size` | 8, 12, 16, atau 24. |
| `question_time_limit` | Nullable; detik. |
| `current_team_id` | FK nullable ke `surprise_teams`. |
| `current_card_id` | FK nullable ke `surprise_cards`. |
| `config` | JSONB: pengaturan power-up, seed, dan opsi permainan. |
| `started_at`, `ended_at` | Waktu permainan. |
| `created_by` | FK eksplisit ke `core.users`. |
| timestamps | Audit standar Laravel. |

#### `surprise_teams`

| Kolom | Keterangan |
|---|---|
| `id` | Primary key. |
| `surprise_session_id` | FK ke `surprise_sessions`. |
| `name`, `color` | Identitas tim. |
| `turn_order` | Urutan giliran unik per sesi. |
| `score` | Skor saat ini, default 0. |
| `shield_count` | Jumlah perisai aktif. |
| `skip_turns` | Jumlah giliran yang harus dilewati. |
| timestamps | Audit standar. |

#### `surprise_cards`

| Kolom | Keterangan |
|---|---|
| `id` | Primary key. |
| `surprise_session_id` | FK ke `surprise_sessions`. |
| `position` | Posisi kartu pada grid, unik per sesi. |
| `card_type` | `question`, `power_up`, `blank`. |
| `question_id` | FK nullable ke `q_game.questions`. |
| `power_up_code` | Nullable; kode efek. |
| `state` | `hidden`, `revealed`, `resolved`, `cancelled`. |
| `selected_by_team_id` | Tim yang membuka kartu. |
| `revealed_at`, `resolved_at` | Waktu status kartu. |
| `result_payload` | JSONB: jawaban, poin, efek, target, dan hasil final. |
| timestamps | Audit standar. |

#### `surprise_events`

Log append-only untuk audit dan halaman review.

| Kolom | Keterangan |
|---|---|
| `id` | Primary key. |
| `surprise_session_id` | FK ke sesi. |
| `surprise_card_id` | FK nullable ke kartu. |
| `actor_team_id` | Tim aktif nullable. |
| `event_type` | Contoh: `card_revealed`, `answer_marked`, `power_up_applied`, `turn_changed`, `game_finished`. |
| `payload` | JSONB hasil event. |
| `created_by` | FK nullable ke `core.users`. |
| `created_at` | Waktu event. |

Indeks minimum: PIN/status sesi, `surprise_session_id + position`,
`surprise_session_id + turn_order`, dan `surprise_session_id + created_at`.

### 9.3 Model dan layanan

- `SurpriseSession`, `SurpriseTeam`, `SurpriseCard`, `SurpriseEvent`.
- `SurpriseGameService` menjadi satu-satunya tempat untuk:
  - membuat board dari pertanyaan dan power-up;
  - memilih kartu secara atomik;
  - mengesahkan jawaban;
  - menerapkan efek/poin;
  - memajukan giliran;
  - mengakhiri permainan.
- Semua perubahan state memakai `DB::transaction()` dan row locking yang
  sesuai agar klik ganda atau dua layar guru tidak menggandakan poin.

### 9.4 Endpoint awal

Rute mengikuti pola `/game/api/*` yang sudah ada, tetapi semua aksi stateful
harus dibatasi rate limit dan divalidasi kepemilikannya.

| Metode | Endpoint | Fungsi |
|---|---|---|
| `POST` | `/surprise/api/create-session` | Membuat sesi dan board dari setup guru. |
| `POST` | `/surprise/api/verify-pin` | Memuat sesi publik dari PIN aktif. |
| `GET` | `/surprise/api/sessions/{session}` | Mengambil state permainan yang aman untuk layar publik. |
| `POST` | `/surprise/api/sessions/{session}/cards/{card}/reveal` | Membuka kartu tim aktif. |
| `POST` | `/surprise/api/sessions/{session}/cards/{card}/resolve-question` | Guru menandai benar/salah. |
| `POST` | `/surprise/api/sessions/{session}/cards/{card}/resolve-power-up` | Menetapkan target/konfirmasi efek. |
| `POST` | `/surprise/api/sessions/{session}/pause` | Menjeda permainan. |
| `POST` | `/surprise/api/sessions/{session}/resume` | Melanjutkan permainan. |
| `POST` | `/surprise/api/sessions/{session}/finish` | Mengakhiri permainan. |

Endpoint publik hanya boleh membaca state yang diperlukan; operasi moderator
harus menggunakan autentikasi dan pemeriksaan admin/pemilik sesi.

## 10. Antarmuka dan visual

### Setup

- Kartu Kotak Kejutan menggunakan ikon kotak/kado dan warna berbeda dari
  Tarik Tambang.
- Form setup memakai step sederhana: konten → tim → papan & mode → mulai.
- Tampilkan jumlah soal tersedia secara langsung.

### Papan permainan

- Grid responsif dengan nomor/kartu tertutup yang jelas.
- Warna tim aktif terlihat di header dan outline kartu saat memilih.
- Papan tetap terbaca pada proyektor 16:9.
- Kartu soal menampilkan gambar/audio yang telah didukung oleh bank soal.
- Panel guru menawarkan tombol besar: `Benar`, `Salah`, `Batal`, dan
  `Akhiri Permainan`.
- Animasi pendek untuk membuka kartu, skor, dan power-up; hormati preferensi
  reduced motion bila tersedia.

### Aksesibilitas

- Kontras warna skor dan status memenuhi kebutuhan keterbacaan.
- Warna tim selalu disertai nama/ikon, bukan warna saja.
- Semua tombol penting dapat dioperasikan keyboard.
- Power-up menjelaskan efeknya dalam teks, bukan hanya animasi.

## 11. Keamanan dan integritas

- Server menentukan kartu, nilai acak, skor, serta hasil power-up.
- Browser tidak boleh mengirim nilai skor final atau daftar kartu rahasia.
- Gunakan policy/ownership check untuk guru dan admin.
- Gunakan CSRF untuk aksi moderator web; jika endpoint permainan dikecualikan
  dari CSRF, autentikasi dan token/otorisasi alternatif wajib diperketat.
- PIN tidak memberi akses moderator.
- Riwayat event tidak dapat diubah melalui UI.
- Jangan menambahkan helper PHP eksekusi dari web ke `public/`.

## 12. Tahapan implementasi

### Fase 1 — Fondasi data dan setup

1. Tambahkan migrasi, model, relasi, policy, dan indeks.
2. Buat menu/kartu Kotak Kejutan pada setup game.
3. Buat form setup dan validasi jumlah pertanyaan.
4. Buat layanan pembangun board dan PIN.
5. Uji schema PostgreSQL `q_game` secara terisolasi sebelum migrasi produksi.

### Fase 2 — Mode Quiz end-to-end

1. Bangun papan kartu dan indikator giliran.
2. Implementasi buka kartu, soal, keputusan Benar/Salah, skor, dan giliran.
3. Tambahkan pause, resume, finish, hasil akhir, serta review dasar.
4. Uji 2, 4, dan 8 tim dengan database kosong dan bank soal baru.

### Fase 3 — Power-up

1. Tambahkan mode Klasik dan Ramah.
2. Implementasikan efek satu per satu beserta event log.
3. Tambahkan dialog pilihan target.
4. Uji efek skor positif/negatif, seri, dan klik ganda.

### Fase 4 — Penyempurnaan

1. Riwayat permainan lengkap dan halaman review admin.
2. Animasi, suara opsional, aksesibilitas, serta responsivitas proyektor.
3. Observabilitas error dan dokumentasi guru.

## 13. Kriteria penerimaan fase 1–2

- Kartu Kotak Kejutan muncul di setup dan tidak mengubah Tarik Tambang.
- Guru dapat memilih topik/materi, 2–8 tim, dan ukuran papan.
- Sistem menolak setup jika soal tidak mencukupi.
- Satu sesi Quiz dapat dibuat, dimainkan, dijeda, dilanjutkan, dan diakhiri.
- Kartu tidak dapat dibuka dua kali.
- Hanya tim aktif yang dapat membuka kartu.
- Guru dapat mengesahkan benar/salah dan skor berubah tepat satu kali.
- Seluruh tabel baru hanya dibuat di `q_game`.
- Tidak ada data contoh atau seeder yang dijalankan di production.
- SSO Q-Link dan role `admin`/`guru` tetap berfungsi.
- Uji regresi Tarik Tambang tetap lulus.

## 14. Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Soal kurang dari ukuran papan | Validasi di setup dan tampilkan jumlah tersedia. |
| Klik ganda/dua layar guru | Transaksi, locking, state kartu, dan event idempotent. |
| Power-up terasa tidak adil | Mode Ramah, opsi nonaktifkan efek, dan nilai terkonfigurasi. |
| Guru kehilangan konteks saat proyeksi | Panel moderator dan layar permainan memakai state yang sama. |
| Konflik schema/database | Semua objek baru di `q_game`; FK identitas eksplisit ke `core.users`. |
| SSO rusak akibat konfigurasi | Tidak mengubah `core.sessions`, key bersama, atau cookie Q-Link. |

## 15. Keputusan terbuka sebelum fase implementasi

- Apakah layar moderator dan layar proyektor akan memakai perangkat yang sama
  pada fase pertama, atau perlu URL/panel moderator terpisah?
- Apakah kartu pertanyaan yang dijawab salah dapat dicoba tim lain pada fase
  berikutnya? Rekomendasi awal: **tidak**, untuk menjaga alur sederhana.
- Apakah mode Klasik menggunakan distribusi power-up tetap atau guru dapat
  memilih tiap jenis efek? Rekomendasi awal: preset tetap per mode.
- Apakah audio efek diperlukan pada fase pertama? Rekomendasi awal: tidak,
  agar fokus pada stabilitas permainan dan proyektor kelas.
