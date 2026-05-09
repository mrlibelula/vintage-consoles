<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;

class OrderBySquares extends Component
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
        $this->js("requestAnimationFrame(() => requestAnimationFrame(() => {
            window.dispatchEvent(new CustomEvent('skeleton-square-off'));
        }))");
    }

    public function render()
    {
        return view('livewire.order-by-squares');
    }
}
