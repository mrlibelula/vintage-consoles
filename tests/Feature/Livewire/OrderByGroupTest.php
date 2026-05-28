<?php

use App\Livewire\OrderByGroup;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('OrderByGroup sorting', function () {
    it('renders games in the requested carousel order', function () {
        $console = Console::factory()->create([
            'id' => 1,
            'short_name' => 'arcade',
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

        $console->load('games');

        Livewire::test(OrderByGroup::class, [
            'selected_console' => $console,
            'gameSortField' => 'rating',
            'gameSortDirection' => 'desc',
        ])
            ->assertSeeInOrder(['Alpha', 'Zulu']);
    });
});
