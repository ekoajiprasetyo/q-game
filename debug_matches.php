<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pin = '553780';
$t = App\Models\Tournament::where('pin', $pin)->first();
if(!$t) die("Tournament not found");

$matches = $t->matches()->orderBy('round')->orderBy('match_number')->get();
foreach($matches as $m) {
    echo "ID: {$m->id} | R: {$m->round} | #{$m->match_number} | Win: " . ($m->winner_team_id ?? 'NULL') . 
         " | Next: " . ($m->next_match_id ?? 'NULL') . 
         " | T1: " . ($m->team_1_id ?? 'NULL') . 
         " | T2: " . ($m->team_2_id ?? 'NULL') . "\n";
}
