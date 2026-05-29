<?php

namespace App\Livewire;

use App\Notifications\SiteDataRestored;
use App\Service\Tool;
use App\Services\GameRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Navigation extends Component
{
    public string $search = '';
    public array $search_results = [];

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->clearSearchResults();
            return;
        }

        $repo  = app(GameRepository::class);
        $games = $repo->searchGames($this->search, 50);

        $results = [];
        foreach ($games as $idx => $game) {
            $console = $game->console;
            $results[] = [
                'result_id'            => $idx,
                'console_id'           => $console->id,
                'console_short_name'   => $console->short_name,
                'console_long_name'    => $console->long_name,
                'console_console_icon' => $console->console_icon,
                'console_console_logo' => $console->console_logo,
                'game_id'              => $game->id,
                'game_title'           => $game->title,
                'game_preview'         => $game->game_preview,
                'game_poster'          => $game->poster,
                'game_cartridge'       => $game->cartridge,
                'game_rating'          => $game->rating,
                'game_slug'            => $game->slug,
            ];
        }

        $this->search_results = collect($results)->sortBy('game_title')->values()->toArray();
        $this->dispatch('loader-top-off');
    }

    public function clearSearchResults(): void
    {
        $this->search        = '';
        $this->search_results = [];
    }

    public function setCursorStyle(string $style): void
    {
        if (! Auth::check()) {
            return;
        }

        if (! in_array($style, ['default', 'alternate'], true)) {
            $style = 'alternate';
        }

        Auth::user()->forceFill([
            'cursor_style' => $style,
        ])->save();
    }

    public function gameRoute(array $console, array $game): string
    {
        return Tool::gameRoute($console, $game);
    }

    public function dismissRestoreNotification(string $id): void
    {
        if (! Auth::check()) {
            return;
        }

        Auth::user()
            ->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $restoreNotification = null;

        if (Auth::check()) {
            $restoreNotification = Auth::user()
                ->unreadNotifications()
                ->where('type', SiteDataRestored::class)
                ->latest()
                ->first();
        }

        return view('livewire.navigation', [
            'restoreNotification' => $restoreNotification,
        ]);
    }
}
