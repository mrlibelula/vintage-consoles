<?php

namespace App\View\Components;

use App\Models\AppFont as AppFontModel;
use App\Services\AppFontService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppFont extends Component
{
    public AppFontModel $font;

    public string $fontUrl;

    public string $mimeType;

    public string $cssFormat;

    public string $cssFamily;

    public function __construct(AppFontService $fonts)
    {
        $this->font = $fonts->active();
        $this->fontUrl = $fonts->publicUrl($this->font);
        $this->mimeType = $fonts->mimeType($this->font);
        $this->cssFormat = $fonts->cssFormat($this->font);
        $this->cssFamily = $fonts->cssFamily($this->font);
    }

    public function render(): View|Closure|string
    {
        return view('components.app-font');
    }
}
