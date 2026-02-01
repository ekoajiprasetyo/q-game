<?php
/**
 * Q-Game Migration Helper
 * 
 * File ini membantu menjalankan migrasi database di hosting yang tidak memiliki akses terminal.
 * 
 * CARA PENGGUNAAN:
 * 1. Upload file ini ke folder publik subdomain (misalnya: public_html/game/)
 * 2. Akses via browser: https://game.q-link.my.id/migrate.php?key=GANTI_DENGAN_KEY_RAHASIA
 * 3. HAPUS FILE INI SETELAH SELESAI!
 * 
 * PERINGATAN: File ini memiliki akses penuh ke database Anda.
 *             JANGAN biarkan file ini di server setelah selesai migrasi!
 */

// ===========================================
// KONFIGURASI - GANTI SESUAI KEBUTUHAN
// ===========================================
$SECRET_KEY = 'qgame2026migrate'; // Ganti dengan key rahasia Anda!
$LARAVEL_PATH = __DIR__ . '/../../q-game-core'; // Sesuaikan path ke folder Laravel

// ===========================================
// SECURITY CHECK
// ===========================================
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    http_response_code(403);
    die('Akses ditolak. Gunakan parameter ?key=KUNCI_RAHASIA');
}

// ===========================================
// BOOTSTRAP LARAVEL
// ===========================================
try {
    require $LARAVEL_PATH . '/vendor/autoload.php';
    $app = require $LARAVEL_PATH . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = Illuminate\Http\Request::capture());
} catch (Exception $e) {
    die('Error loading Laravel: ' . $e->getMessage());
}

// ===========================================
// HTML OUTPUT
// ===========================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q-Game Migration Helper</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #1a1a2e; color: #eee; }
        h1 { color: #f39c12; border-bottom: 2px solid #f39c12; padding-bottom: 10px; }
        h2 { color: #3498db; margin-top: 30px; }
        .card { background: #16213e; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .success { background: #27ae60; color: white; padding: 10px 15px; border-radius: 5px; }
        .error { background: #e74c3c; color: white; padding: 10px 15px; border-radius: 5px; }
        .warning { background: #f39c12; color: black; padding: 10px 15px; border-radius: 5px; }
        .info { background: #3498db; color: white; padding: 10px 15px; border-radius: 5px; }
        pre { background: #0f0f23; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 13px; }
        .btn { display: inline-block; padding: 12px 24px; margin: 5px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; }
        .btn-primary { background: #f39c12; color: #1a1a2e; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #0f0f23; color: #f39c12; }
    </style>
</head>
<body>

<h1>🎮 Q-Game Migration Helper</h1>

<?php
$action = $_GET['action'] ?? 'status';

// ===========================================
// NAVIGATION
// ===========================================
?>
<div class="card">
    <a href="?key=<?= $SECRET_KEY ?>&action=status" class="btn btn-primary">📊 Status</a>
    <a href="?key=<?= $SECRET_KEY ?>&action=migrate" class="btn btn-success">🚀 Jalankan Migrasi</a>
    <a href="?key=<?= $SECRET_KEY ?>&action=keygen" class="btn btn-primary">🔑 Generate Key</a>
    <a href="?key=<?= $SECRET_KEY ?>&action=optimize" class="btn btn-primary">⚡ Optimize</a>
    <a href="?key=<?= $SECRET_KEY ?>&action=check_tables" class="btn btn-primary">🔍 Cek Tabel</a>
</div>

<?php
// ===========================================
// ACTIONS
// ===========================================

switch ($action) {
    case 'status':
        echo '<h2>📊 Status Migrasi</h2>';
        echo '<div class="card"><pre>';
        try {
            Illuminate\Support\Facades\Artisan::call('migrate:status');
            echo htmlspecialchars(Illuminate\Support\Facades\Artisan::output());
        } catch (Exception $e) {
            echo '<span class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
        echo '</pre></div>';
        break;

    case 'migrate':
        echo '<h2>🚀 Menjalankan Migrasi</h2>';
        echo '<div class="card"><pre>';
        try {
            Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            echo htmlspecialchars(Illuminate\Support\Facades\Artisan::output());
            echo '</pre><div class="success">✅ Migrasi berhasil dijalankan!</div></div>';
        } catch (Exception $e) {
            echo '</pre><div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
        break;

    case 'keygen':
        echo '<h2>🔑 Generate Application Key</h2>';
        echo '<div class="card"><pre>';
        try {
            Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
            echo htmlspecialchars(Illuminate\Support\Facades\Artisan::output());
            echo '</pre><div class="success">✅ Application key berhasil di-generate!</div></div>';
        } catch (Exception $e) {
            echo '</pre><div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
        break;

    case 'optimize':
        echo '<h2>⚡ Optimasi Aplikasi</h2>';
        echo '<div class="card"><pre>';
        try {
            Illuminate\Support\Facades\Artisan::call('optimize:clear');
            echo "=== CLEAR CACHE ===\n";
            echo htmlspecialchars(Illuminate\Support\Facades\Artisan::output());
            
            Illuminate\Support\Facades\Artisan::call('optimize');
            echo "\n=== OPTIMIZE ===\n";
            echo htmlspecialchars(Illuminate\Support\Facades\Artisan::output());
            
            echo '</pre><div class="success">✅ Optimasi berhasil!</div></div>';
        } catch (Exception $e) {
            echo '</pre><div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
        break;

    case 'check_tables':
        echo '<h2>🔍 Daftar Tabel di Database</h2>';
        echo '<div class="card">';
        try {
            $tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $dbName = env('DB_DATABASE');
            
            echo '<table>';
            echo '<tr><th>#</th><th>Nama Tabel</th><th>Status</th></tr>';
            
            $gameTablesExpected = [
                'game_topics', 'game_materials', 'game_questions',
                'game_sessions', 'game_rounds',
                'game_tournaments', 'game_tournament_teams', 'game_tournament_matches'
            ];
            
            $existingTables = [];
            $i = 1;
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_$dbName"};
                $existingTables[] = $tableName;
                
                $isGameTable = strpos($tableName, 'game_') === 0;
                $status = $isGameTable ? '🎮 Q-Game' : '📦 Q-Link/System';
                
                echo "<tr><td>$i</td><td><code>$tableName</code></td><td>$status</td></tr>";
                $i++;
            }
            echo '</table>';
            
            // Check missing tables
            $missingTables = array_diff($gameTablesExpected, $existingTables);
            if (!empty($missingTables)) {
                echo '<div class="warning" style="margin-top: 15px;">';
                echo '⚠️ Tabel Q-Game yang belum ada: <strong>' . implode(', ', $missingTables) . '</strong>';
                echo '<br><br>Jalankan migrasi untuk membuat tabel-tabel ini.';
                echo '</div>';
            } else {
                echo '<div class="success" style="margin-top: 15px;">✅ Semua tabel Q-Game sudah ada!</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';
        break;
}
?>

<h2>⚠️ Peringatan Keamanan</h2>
<div class="card">
    <div class="warning">
        <strong>PENTING!</strong> Hapus file <code>migrate.php</code> ini setelah selesai migrasi!
        <br><br>
        File ini memberikan akses penuh ke database dan sistem Anda.
    </div>
</div>

<h2>📋 Checklist Migrasi</h2>
<div class="card">
    <ol>
        <li>✅ Upload semua file Q-Game ke hosting</li>
        <li>✅ Edit <code>.env</code> dengan kredensial database Q-Link</li>
        <li>⏳ Klik <strong>"Generate Key"</strong> jika APP_KEY kosong</li>
        <li>⏳ Klik <strong>"Jalankan Migrasi"</strong> untuk membuat tabel</li>
        <li>⏳ Klik <strong>"Optimize"</strong> untuk cache</li>
        <li>⏳ Test login dengan akun guru/admin dari Q-Link</li>
        <li>🗑️ <strong>HAPUS file migrate.php ini!</strong></li>
    </ol>
</div>

<p style="text-align: center; color: #666; margin-top: 40px;">
    Q-Game Migration Helper v1.0 | <?= date('Y-m-d H:i:s') ?>
</p>

</body>
</html>
<?php
// Terminate kernel
$kernel->terminate($request, $response);
