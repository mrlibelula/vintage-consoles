<?php

use App\Livewire\About;
use App\Livewire\Dashboard;
use App\Livewire\DosPlayer;
use App\Livewire\Genres;
use App\Livewire\JsPlayer;
use App\Livewire\Play;
use App\Livewire\Publishers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\LoginController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// oauth2
Route::get('/login/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/login/google/redirect', [LoginController::class, 'handleGoogleCallback'])->name('login.google.callback');

// Debugging route to understand authentication state in production
Route::get('/debug-auth', function () {
    if (!app()->environment('production')) {
        return 'This route only works in production';
    }
    
    return response()->json([
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'app_url' => config('app.url'),
        'request_url' => request()->fullUrl(),
        'is_secure' => request()->isSecure(),
        'headers' => request()->headers->all(),
        'server_https' => $_SERVER['HTTPS'] ?? 'not set',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'not set',
    ]);
})->name('debug.auth');

// Debug route to monitor memory usage (remove in production)
Route::get('/debug/memory', function () {
    $sessionSize = strlen(serialize(session()->all()));
    $consolesBasic = session('consoles_basic', []);
    $consolesBasicSize = strlen(serialize($consolesBasic));
    
    return response()->json([
        'php_memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
        'php_memory_peak' => memory_get_peak_usage(true) / 1024 / 1024 . ' MB',
        'session_total_size' => $sessionSize . ' bytes',
        'consoles_basic_size' => $consolesBasicSize . ' bytes',
        'consoles_basic_count' => count($consolesBasic),
        'json_file_size' => Storage::disk('data')->size('vintage-consoles.json') . ' bytes'
    ]);
})->name('debug.memory');

// Route::get('/', function () {
//     return view('landing');
// });

Route::get('/{console_short_name?}', Dashboard::class)->name('home');
Route::get('/dashboard/{console_short_name?}', Dashboard::class)->name('dashboard');
Route::get('/emulator/{console_short_name}/{game_title_slug}', Play::class)->name('play');
Route::get('/player/{enc_json_game}/{console_short_name}', JsPlayer::class)->name('player');
Route::get('/dosplayer/{enc_json_game}/{console_short_name}', DosPlayer::class)->name('dosplayer');
Route::get('/games/genres/{genre_name?}', Genres::class)->name('genres');
Route::get('/games/publishers/{publisher_name?}', Publishers::class)->name('publishers');
Route::get('/creator/about', About::class)->name('about');

// Game file serving route - accessible to all users (no middleware)
Route::get('/games/serve/{console}/{filename}', function ($console, $filename) {
    $gamePath = storage_path("data/games/{$console}/{$filename}");
    
    if (!file_exists($gamePath)) {
        abort(404, 'Game file not found');
    }
    
    // Get the file's MIME type
    $mimeType = mime_content_type($gamePath);
    
    // If we can't determine the MIME type, use application/octet-stream
    if (!$mimeType) {
        $mimeType = 'application/octet-stream';
    }
    
    // Return the file with appropriate headers
    return response()->file($gamePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
    ]);
})->name('game.serve');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Admin routes - protected by admin middleware
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/games', App\Livewire\Admin\GameManager::class)->name('admin.games');
    });
});
