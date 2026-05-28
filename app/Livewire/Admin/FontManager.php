<?php

namespace App\Livewire\Admin;

use App\Models\AppFont;
use App\Services\AppFontService;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class FontManager extends Component
{
    use WithFileUploads;

    public $fonts;

    public int $activeFontId;

    public $fontFile = null;

    public string $label = '';

    public string $familyName = '';

    protected AppFontService $fontsService;

    public function boot(AppFontService $fontsService): void
    {
        $this->fontsService = $fontsService;
    }

    public function mount(): void
    {
        $this->refreshFonts();
        $this->activeFontId = $this->fontsService->active()->id;
    }

    public function updatedFontFile(): void
    {
        if (! $this->fontFile) {
            return;
        }

        $originalName = $this->fontFile->getClientOriginalName();
        $guessedFamily = $this->fontsService->guessFamilyName($originalName);

        if ($this->label === '') {
            $this->label = $guessedFamily;
        }

        if ($this->familyName === '') {
            $this->familyName = $guessedFamily;
        }
    }

    public function activate(int $fontId): void
    {
        $font = AppFont::query()->findOrFail($fontId);

        $this->fontsService->activate($font);
        $this->activeFontId = $font->id;
        $this->refreshFonts();

        session()->flash('success', "Active app font set to {$font->label}.");
    }

    public function install(): void
    {
        $validated = $this->validate([
            'fontFile' => [
                'required',
                File::default()->extensions(['ttf', 'otf', 'woff', 'woff2'])->max(5120),
            ],
            'label' => ['required', 'string', 'max:120'],
            'familyName' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9 _-]+$/'],
        ]);

        try {
            $font = $this->fontsService->install(
                $validated['fontFile'],
                $validated['label'],
                $validated['familyName'],
            );
        } catch (\InvalidArgumentException $exception) {
            $this->addError('fontFile', $exception->getMessage());

            return;
        }

        $this->reset(['fontFile', 'label', 'familyName']);
        $this->refreshFonts();

        session()->flash('success', "Installed {$font->label}.");
    }

    public function delete(int $fontId): void
    {
        $font = AppFont::query()->findOrFail($fontId);

        try {
            $this->fontsService->delete($font);
        } catch (\InvalidArgumentException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }

        $this->refreshFonts();

        session()->flash('success', 'Font deleted.');
    }

    public function render()
    {
        return view('livewire.admin.font-manager');
    }

    private function refreshFonts(): void
    {
        $this->fonts = $this->fontsService->all();
    }
}
