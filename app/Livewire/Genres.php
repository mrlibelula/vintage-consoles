<?php

namespace App\Livewire;

use App\Livewire\Concerns\SortsGameCarousels;
use App\Models\Genre;
use App\Service\Tool;
use App\Services\GameRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Genres extends Component
{
    use SortsGameCarousels;

    public string $genre_name = '';
    public $genres = [];
    public $filtered_games = [];
    public string $ob = 'group';
    public $games = [];
    public string $current_genre = '';

    /** @var string count|alpha */
    public string $genreSort = 'count';

    public function mount(Request $request, string $genre_name = ''): void
    {
        $this->ob = $request->has('ob')
            ? $request->query('ob')
            : Session::get('ob', $this->ob);
        Session::put('ob', $this->ob);

        $repo = app(GameRepository::class);

        // Verify genre exists
        $allGenres = $repo->getAllGenres();
        $contains  = $genre_name
            ? $allGenres->contains('name', $genre_name)
            : false;

        $this->genre_name = $contains ? $genre_name : '';
        $this->genres     = $this->genresForDisplay($allGenres);
        $this->filterGames($this->genre_name);
    }

    public function gameRoute($console, $game): string
    {
        return Tool::gameRoute(
            is_array($console) ? $console : $console->toArray(),
            is_array($game)    ? $game    : $game->toArray()
        );
    }

    public function filterGames(string $genre_name): void
    {
        $repo = app(GameRepository::class);
        $this->filtered_games = $genre_name
            ? $repo->getGamesByGenre($genre_name)
            : collect();
    }

    protected function genresForDisplay($allGenres = null)
    {
        $repo = app(GameRepository::class);
        $list = $allGenres ?? $repo->getAllGenres();

        if ($this->genreSort === 'alpha') {
            $list = $list->sortBy('name')->values();
        }

        return $list;
    }

    public function setGenreSort(string $sort): void
    {
        if (! in_array($sort, ['count', 'alpha'], true)) {
            return;
        }
        $this->genreSort = $sort;
        $this->genres    = $this->genresForDisplay();
    }

    public function filterByGenre(string $genre_name): void
    {
        $this->current_genre = $genre_name;
        $this->filterGames($genre_name);
    }

    public function rendered(): void
    {
        Tool::loadersOff($this, [
            'loader-off',
            'loader-top-off',
            'skeleton-lista-off',
        ]);
        $this->dispatch('fixed-modal-loader-off');
    }

    public function render()
    {
        if (Session::exists('ob')) {
            $ob       = Session::get('ob');
            $this->ob = $ob === 'lista' ? 'squares' : $ob;
        }

        $this->loadGameSortFromSession();

        if ($this->filtered_games) {
            $this->filtered_games = $this->sortGamesForCarousel(collect($this->filtered_games));
        }

        return view('livewire.genres');
    }
}
