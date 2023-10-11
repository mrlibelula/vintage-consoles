<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Accordion extends Component
{
    public bool $toggler;

    /**
     * Create a new component instance.
     */
    public function __construct(bool $toggler)
    {
        $this->toggler = filter_var($toggler, FILTER_VALIDATE_BOOLEAN);
        
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.accordion');
    }
}
