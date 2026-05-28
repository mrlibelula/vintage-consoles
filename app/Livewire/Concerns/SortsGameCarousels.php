<?php

namespace App\Livewire\Concerns;

use App\Services\GameRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

trait SortsGameCarousels
{
    public string $gameSortField = 'rating';

    public string $gameSortDirection = 'desc';

    protected function defaultCarouselSortDirection(string $field): string
    {
        return $field === 'rating' ? 'desc' : 'asc';
    }

    protected function loadGameSortFromSession(): void
    {
        if (Session::exists('game_sort_field')) {
            $field = Session::get('game_sort_field');
            if (in_array($field, ['title', 'rating'], true)) {
                $this->gameSortField = $field;
            }
        }

        if (Session::exists('game_sort_direction')) {
            $direction = Session::get('game_sort_direction');
            if (in_array($direction, ['asc', 'desc'], true)) {
                $this->gameSortDirection = $direction;
            }
        }
    }

    public function sortCarouselBy(string $field): void
    {
        if (! in_array($field, ['title', 'rating'], true)) {
            return;
        }

        if ($this->gameSortField === $field) {
            $this->gameSortDirection = $this->gameSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->gameSortField = $field;
            $this->gameSortDirection = $this->defaultCarouselSortDirection($field);
        }

        Session::put('game_sort_field', $this->gameSortField);
        Session::put('game_sort_direction', $this->gameSortDirection);
    }

    protected function sortGamesForCarousel(Collection $games): Collection
    {
        return app(GameRepository::class)->sortGamesCollection(
            $games,
            $this->gameSortField,
            $this->gameSortDirection
        );
    }
}
