<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RedDot extends Component
{
    public function __construct(public bool $beaming = false)
    {
    }

    public function render(): View|Closure|string
    {
        return view('components.red-dot');
    }
}
