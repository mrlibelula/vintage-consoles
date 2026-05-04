<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;
use App\Service\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Dashboard extends Component
{
    public array $consoles = [];
    public array $selected_console = [];
    public int $selected_console_id = 0;
    public bool $show_hero = false;
    public string $hero_image;
    public string $ob = 'group';
    public array $console_tabs = [
        'nes' => true,
        'snes' => false,
        'arcade' => false,
        'atari2600' => false,
        'pc' => false,
    ];

    public function setConsole(int $console_id)
    {
        $this->selected_console = [];
        $this->selected_console_id = $console_id;
        
        // First try to find in basic console data for navigation
        $basicConsole = Tool::findItemByKey($this->consoles, 'id', $console_id);
        
        if ($basicConsole) {
            // Load full console data including games for the selected console
            $gameSession = new GameSession();
            $this->selected_console = $gameSession->getFullConsoleData($basicConsole['short_name']);
        }
    }
    
    /**
     * Determines if the selected tab is the first or the last in the list
     *
     * @param string $position
     * @return boolean
     */
    public function isSelectedTab(string $position): bool
    {
        if ($this->selected_console) {
            $console_short_name = strtolower($this->selected_console['short_name']);
            $loop = 0;
            foreach ($this->console_tabs as $short_name => $is_active) {
                $loop++;
                if ($console_short_name === $short_name) {
                    switch ($position) {
                        case 'first':
                            return $loop === 1 ? true : false;
                            break;
                        
                        case 'last':
                            return $loop === count($this->console_tabs) ? true : false;
                            break;

                        default:
                            return false;
                            break;
                    }
                    
                }
            }
        }
        return false;
    }

    public function isSelectedTabFirst(): bool
    {
        return $this->isSelectedTab('first');
    }

    public function isSelectedTabLast(): bool
    {
        return $this->isSelectedTab('last');
    }

    public function randomHeroImage()
    {
        $this->hero_image = Tool::randomItem([
            'https://mir-s3-cdn-cf.behance.net/project_modules/fs/095f3a88540447.5dda467b0f986.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/9037d9103921551.60adad981588f.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/5abca1103921551.60adad98151e0.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/d1397487806317.5dc359c6c42b1.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/max_3840/cf66c5181242099.65188fd8b9db3.jpg',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/db0bd7152875809.6326318396df2.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/max_3840/176255173457931.6490bd22749ed.jpg',
            // 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/654316152668877.6321ff1105714.png',
            // 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/3c7534152668877.6321ff1106636.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/0a1b08142554461.626907c920ad8.png',
            // 'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/05a1bc142554461.626907c99ca90.png',
            'https://mir-s3-cdn-cf.behance.net/project_modules/2800_opt_1/a05542142554461.626907c99d0ef.png',

            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/e36c84143584307.627d06916c3a8.gif',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/297c96143584307.627d06916f17b.gif',
            // 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/6060fa143584307.627d06916b633.gif',
            'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/5ad139143584307.627d06916e5a6.gif',
            // 'https://mir-s3-cdn-cf.behance.net/project_modules/1400_opt_1/b0366c143584307.627d06916fa75.gif',
            'https://i.pinimg.com/originals/1b/96/5c/1b965c3c3b5df0517bdca4e0d95beb15.gif',
            'https://i.pinimg.com/originals/15/e7/e3/15e7e300166c962d3b8a22f60b5cac9e.gif',
            'https://i.pinimg.com/originals/83/ad/fc/83adfc6e00273fac549747a0eb3b4487.gif',
            'https://cdna.artstation.com/p/assets/images/images/058/101/014/original/yurii-ray-mint-white.gif?1673382715',
            'https://i.pinimg.com/originals/d0/e0/e2/d0e0e259bf0aba4da742bedff1d4b8a5.gif',
            // 'https://media4.giphy.com/media/QP77j3YqAtLuDyhHzL/giphy.gif?cid=6c09b952t8ko9hhq93a60hv5z0wi5j0fgv7w1tbmiil3tuee&ep=v1_internal_gif_by_id&rid=giphy.gif&ct=g',
            'https://wallpapercave.com/wp/wp11383218.gif',
            'https://i.pinimg.com/originals/83/b8/09/83b809857acd41a7bad4935b4734f9fc.gif',        
        ]);
    }

    public function loadConsoles($data_source = 'vintage-consoles.json', $disk = 'data')
    {
        // Use optimized approach - only load basic console data for dashboard
        if (!Session::has('consoles_basic')) {
            new GameSession();
        }
        
        // Get basic console data from session (without full game details)
        $this->consoles = Session::get('consoles_basic', []);
    }

    public function mount(Request $request, string $console_short_name = 'nes')
    {
        if ($request->has('ob')) {
            $this->ob = $request->query('ob');
        } else {
            $this->ob = Session::has('ob') ? Session::get('ob') : $this->ob;
        }
        Session::put('ob', $this->ob);
        
        $console_short_name = strtolower($console_short_name);
        
        $this->randomHeroImage();
        $this->loadConsoles();
        
        $console_ids = [
            'nes' => 1,
            'snes' => 2,
            'arcade' => 3,
            'atari2600' => 4,
            'pc' => 5,
        ];
        isset($console_ids[$console_short_name])
            ? $this->setConsole($console_ids[$console_short_name])
            : $this->setConsole($console_ids['nes']);
        
        // No longer pass full console data to GameSession
        // GameSession will handle its own optimization
        $this->initGameSessionEnviro();
    }
    
    public function initGameSessionEnviro()
    {
        // Just ensure GameSession is initialized with optimized data
        if (!Session::has('consoles_basic')) {
            new GameSession();
        }
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
