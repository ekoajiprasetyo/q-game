<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\GameSessionController;

use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home - redirect to game
Route::get('/', function () {
    return redirect('/game');
});

// Game routes (public)
use App\Http\Controllers\GameController;

Route::controller(GameController::class)->group(function () {
    Route::get('/game', 'index')->name('game');
    Route::get('/game/setup', 'setup')->name('game.setup');
    Route::get('/game/play', 'play')->name('game.play');
    Route::post('/game/api/verify-pin', 'verifyPin')->name('game.verify-pin')->middleware('throttle:10,1');
    Route::post('/game/api/update-session', 'updateSession')->name('game.update-session')->middleware('throttle:60,1');
    Route::post('/game/api/cancel-session', 'cancelSession')->name('game.cancel-session')->middleware('throttle:30,1');
    Route::post('/game/api/submit-answer', 'submitAnswer')->name('game.submit-answer')->middleware('throttle:60,1');
    Route::get('/game/review/{session_id}', 'review')->name('game.review');
});

// Public Tournament Routes
use App\Http\Controllers\TournamentPublicController;

Route::controller(TournamentPublicController::class)->group(function () {
    Route::post('/tournament/verify-pin', 'verifyPin')->name('tournament.verify-pin')->middleware('throttle:10,1');
    Route::get('/tournament/bracket', 'bracket')->name('tournament.bracket');
    Route::post('/tournament/match/{match}/start', 'startMatch')->name('tournament.match.start');
});

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes (protected)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users Management (Admin Only)
    Route::resource('users', UserController::class)->middleware('admin');

    // Topics CRUD

    // Topics CRUD
    Route::resource('topics', TopicController::class);

    // Materials CRUD (nested under topics)
    Route::get('topics/{topic}/materials', [MaterialController::class, 'index'])->name('topics.materials.index');
    Route::get('topics/{topic}/materials/create', [MaterialController::class, 'create'])->name('topics.materials.create');
    Route::post('topics/{topic}/materials', [MaterialController::class, 'store'])->name('topics.materials.store');
    Route::get('topics/{topic}/materials/{material}/edit', [MaterialController::class, 'edit'])->name('topics.materials.edit');
    Route::put('topics/{topic}/materials/{material}', [MaterialController::class, 'update'])->name('topics.materials.update');
    Route::delete('topics/{topic}/materials/{material}', [MaterialController::class, 'destroy'])->name('topics.materials.destroy');
    Route::post('topics/{topic}/materials/{material}/generate-pin', [MaterialController::class, 'generatePin'])->name('topics.materials.generate-pin');

    // Questions CRUD
    Route::post('upload-image', [QuestionController::class, 'uploadImage'])->name('upload-image');
    Route::post('questions/bulk-destroy', [QuestionController::class, 'bulkDestroy'])->name('questions.bulk-destroy');
    Route::resource('questions', QuestionController::class);

    // Game Sessions (History)
    Route::get('sessions', [GameSessionController::class, 'index'])->name('sessions.index');
    Route::get('sessions/{session}/modal', [GameSessionController::class, 'modalContent'])->name('sessions.modal');
    Route::get('sessions/{session}', [GameSessionController::class, 'show'])->name('sessions.show');
    Route::delete('sessions/{session}', [GameSessionController::class, 'destroy'])->name('sessions.destroy');

    // Tournaments (Admin)
    Route::post('tournaments/{tournament}/reshuffle', [\App\Http\Controllers\Admin\TournamentController::class, 'reshuffle'])->name('tournaments.reshuffle');
    Route::post('tournaments/matches/{match}/configure', [\App\Http\Controllers\Admin\TournamentController::class, 'configureMatch'])->name('tournaments.matches.configure');
    Route::resource('tournaments', \App\Http\Controllers\Admin\TournamentController::class);
});

// Cache Clearing Helper Route (Temporary)
Route::get('/fix-cache-now', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "Cache cleared successfully! <br>" . nl2br(\Illuminate\Support\Facades\Artisan::output());
});
