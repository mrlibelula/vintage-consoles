<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;

class OrderByGroup extends Component
{
    public array $selected_console;

    /**
     * Generates game route
     *
     * @param array $game
     * @return string
     */
    public function gameRoute(array $game): string
    {
        return Tool::gameRoute($this->selected_console, $game);
    }

    public function rendered(): void
    {
        // Let one frame pass so parent Alpine skeleton-group-on runs after wire:navigate morph.
        $this->js("requestAnimationFrame(() => requestAnimationFrame(() => {
            window.dispatchEvent(new CustomEvent('skeleton-group-off'));
        }))");
    }

    public function render()
    {
        return view('livewire.order-by-group');
    }
}
