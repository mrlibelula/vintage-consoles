<?php

use App\Livewire\About;
use App\Livewire\Dashboard;
use App\Livewire\Genres;
use App\Livewire\JsPlayer;
use App\Livewire\Play;
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
Route::get('/{enc_game_id}/play/{console_short_name}/{game_title}', Play::class)->name('play');
Route::get('/player/{enc_json_game}/{console_short_name}', JsPlayer::class)->name('player');
Route::get('/game/genres/{genre_name?}', Genres::class)->name('genres');
Route::get('/pages/about', About::class)->name('about');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // 
});
