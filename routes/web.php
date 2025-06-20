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

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/dos/{slug}', DosPlayer::class)->name('dos-player');
    Route::get('/js/{slug}', JsPlayer::class)->name('js-player');
    Route::get('/play/{slug}', Play::class)->name('play');
    Route::get('/about', About::class)->name('about');
    Route::get('/genres/{slug}', Genres::class)->name('genres');
    Route::get('/publishers/{slug}', Publishers::class)->name('publishers');
});
