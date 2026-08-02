<?php

namespace App\Livewire\Concerns;

trait WithToasts
{
    protected function toast(string $type, string $message, int $duration = 5500): void
    {
        $this->dispatch('toast', type: $type, message: $message, duration: $duration);
    }
}
