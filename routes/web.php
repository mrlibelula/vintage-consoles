<?php

use App\Livewire\About;
use App\Livewire\Dashboard;
use App\Livewire\DosPlayer;
use App\Livewire\Genres;
use App\Livewire\JsPlayer;
use App\Livewire\Play;
use App\Livewire\Publishers;
use Illuminate\Support\Facades\Route;


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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // 
});
