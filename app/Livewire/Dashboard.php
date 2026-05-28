<?php

namespace App\Livewire;

use App\Models\Console;
use App\Service\Tool;
use App\Services\GameRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Dashboard extends Component
{
    /** @var Console[] */
    public $consoles = [];

    public ?Console $selected_console = null;
    public int $selected_console_id = 0;
    public bool $show_hero = false;
    public string $hero_image = '';
    public string $ob = 'group';
    public array $console_tabs = [
        'nes'       => true,
        'snes'      => false,
        'arcade'    => false,
        'atari2600' => false,
        'pc'        => false,
    ];

    public function setConsole(int $console_id): void
    {
        $this->selected_console_id = $console_id;

        $repo = app(GameRepository::class);
        $basic = collect($this->consoles)->firstWhere('id', $console_id);

        if ($basic) {
            $this->selected_console = $repo->getConsoleWithGames($basic->short_name);
        }
    }

    public function isSelectedTab(string $position): bool
    {
        if (! $this->selected_console) {
            return false;
        }

        $shortName = strtolower($this->selected_console->short_name);
        $keys      = array_keys($this->console_tabs);
        $idx       = array_search($shortName, $keys, true);

        if ($idx === false) {
            return false;
        }

        return match ($position) {
            'first' => $idx === 0,
            'last'  => $idx === count($keys) - 1,
            default => false,
        };
    }

    public function isSelectedTabFirst(): bool
    {
        return $this->isSelectedTab('first');
    }

    public function isSelectedTabLast(): bool
    {
        return $this->isSelectedTab('last');
    }

    public function randomHeroImage(): void
    {
        $this->hero_image = Tool::randomItem([
            'https://mir-s3-cdn-cf.behance.net/project_modules/fs/095f3a88540447.5dda467b0f986.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/9037d9103921551.60adad981588f.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/5abca1103921551.60adad98151e0.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/d1397487806317.5dc359c6c42b1.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/max_3840/cf66c5181242099.65188fd8b9db3.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/db0bd7152875809.6326318396df2.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/max_3840/176255173457931.6490bd22749ed.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/0a1b08142554461.626907c920ad8.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/a05542142554461.626907c99d0ef.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/e36c84143584307.627d06916c3a8.gif',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/297c96143584307.627d06916f17b.gif',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/5ad139143584307.627d06916e5a6.gif',
            'https://i.pinimg.com/originals/1b/96/5c/1b965c3c3b5df0517bdca4e0d95beb15.gif',
            'https://i.pinimg.com/originals/15/e7/e3/15e7e300166c962d3b8a22f60b5cac9e.gif',
            'https://i.pinimg.com/originals/83/ad/fc/83adfc6e00273fac549747a0eb3b4487.gif',
            'https://cdna.artstation.com/p/assets/images/images/058/101/014/original/yurii-ray-mint-white.gif?1673382715',
            'https://i.pinimg.com/originals/d0/e0/e2/d0e0e259bf0aba4da742bedff1d4b8a5.gif',
            'https://wallpapercave.com/wp/wp11383218.gif',
            'https://i.pinimg.com/originals/83/b8/09/83b809857acd41a7bad4935b4734f9fc.gif',
        ]);
    }

    public function mount(Request $request, string $console_short_name = 'nes'): void
    {
        $this->ob = $request->has('ob')
            ? $request->query('ob')
            : Session::get('ob', $this->ob);
        Session::put('ob', $this->ob);

        $console_short_name = strtolower($console_short_name);

        $this->randomHeroImage();

        $repo = app(GameRepository::class);
        $this->consoles = $repo->getConsoles();

        $consoleIdMap = [
            'nes'       => 1,
            'snes'      => 2,
            'arcade'    => 3,
            'atari2600' => 4,
            'pc'        => 5,
        ];

        $id = $consoleIdMap[$console_short_name] ?? $consoleIdMap['nes'];
        $this->setConsole($id);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
