<?php

use App\Livewire\OrderByLista;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('OrderByLista sorting', function () {
    it('renders games in the requested list order', function () {
        $console = Console::factory()->create([
            'id' => 1,
            'short_name' => 'nes',
        ]);

        Game::factory()->create([
            'console_id' => $console->id,
            'title' => 'Zulu',
            'slug' => 'zulu',
            'rating' => 0.2,
        ]);
        Game::factory()->create([
            'console_id' => $console->id,
            'title' => 'Alpha',
            'slug' => 'alpha',
            'rating' => 0.9,
        ]);

        Livewire::test(OrderByLista::class, [
            'selected_console' => $console,
            'gameSortField' => 'rating',
            'gameSortDirection' => 'desc',
            'paginate' => 10,
        ])
            ->assertSeeInOrder(['Alpha', 'Zulu']);
    });
});
