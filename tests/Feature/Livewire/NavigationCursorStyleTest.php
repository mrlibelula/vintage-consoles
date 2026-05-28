<?php

use App\Livewire\Navigation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('persists cursor_style for authenticated user', function () {
    $user = User::factory()->create([
        'cursor_style' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Navigation::class)
        ->call('setCursorStyle', 'alternate')
        ->assertOk();

    expect($user->fresh()->cursor_style)->toBe('alternate');
});

it('normalizes invalid cursor_style values to default', function () {
    $user = User::factory()->create([
        'cursor_style' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(Navigation::class)
        ->call('setCursorStyle', 'nope')
        ->assertOk();

    expect($user->fresh()->cursor_style)->toBe('alternate');
});

it('does nothing for guests', function () {
    $user = User::factory()->create([
        'cursor_style' => null,
    ]);

    Livewire::test(Navigation::class)
        ->call('setCursorStyle', 'alternate')
        ->assertOk();

    expect($user->fresh()->cursor_style)->toBe('alternate');
});

