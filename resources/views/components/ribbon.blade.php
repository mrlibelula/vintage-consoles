@props(['ob' => 'squares', 'customSlidesPerView' => []])
@php
    if ($ob === 'squares') {
        // Same band as group: 3 until wide, 4 before the right panel, 3 again at 2xl.
        $slides_per_view = [ 
            'sm' => 2,
            'md' => 3,
            'xl' => 3,
            'wide' => 4,
            '2xl' => 3,
        ]; 
        $space_between = [
            'sm' => 5,
            'md' => 10,
            'xl' => 15,
            'wide' => 12,
            '2xl' => 15,
        ];
    }
    if ($ob === 'group') {
        // 3 until there's real room; 4 only in the wide band before the 2xl right panel; 3 again with the panel.
        $slides_per_view = [ 
            'sm' => 2,
            'md' => 2,
            'xl' => 3,
            'wide' => 4,
            '2xl' => 3,
        ]; 
        $space_between = [
            'sm' => 16,
            'md' => 28,
            'xl' => 40,
            'wide' => 36,
            '2xl' => 40,
        ];
    }

    if(count($customSlidesPerView)) {
        $slides_per_view = $customSlidesPerView; 
        $space_between = [
            'sm' => 16,
            'md' => 28,
            'xl' => 40,
            'wide' => 40,
            '2xl' => 40,
        ];
    }

    $slides_per_view['wide'] ??= $slides_per_view['xl'] ?? 3;
    $slides_per_view['2xl'] ??= $slides_per_view['wide'] ?? $slides_per_view['xl'] ?? 3;
    $space_between['wide'] ??= $space_between['xl'] ?? 30;
    $space_between['2xl'] ??= $space_between['wide'] ?? $space_between['xl'] ?? 30;

    $dark_wallps = [
        "https://images.alphacoders.com/461/461092.jpg",
        // "https://getwallpapers.com/wallpaper/full/1/5/d/1067757-space-invaders-wallpaper-1920x1080-for-android-tablet.jpg",
        // "https://e1.pxfuel.com/desktop-wallpaper/519/336/desktop-wallpaper-98-game-all-game.jpg",
        // "https://c4.wallpaperflare.com/wallpaper/502/710/388/8-bit-super-mario-minimalism-video-games-wallpaper-preview.jpg",
        // "https://wallpapers.com/images/hd/hd-pacman-qtm4064qk559tkc7.jpg",
        // "https://wallpapercrafter.com/desktop/100935-pixel-art-8-bit-Aqua-Teen-Hunger-Force-blue-dots-humor.png",
        "https://wallpaper.forfun.com/fetch/61/61c5d7a4c561c7a363421e1877018e62.jpeg",
    ];

    $light_wallps = [
        "https://c.wallhere.com/photos/b1/40/controllers_Nintendo_Nintendo_Entertainment_System_simple_retro_games_white_background_simple_background-161079.jpg!d",
        // "https://static.vecteezy.com/system/resources/previews/027/420/048/non_2x/gamepad-icon-on-light-background-vintage-game-console-joystick-symbol-oldschool-retro-gaming-sign-outline-flat-and-colored-style-flat-design-illustration-vector.jpg",
    ];

    $wallpaperSeed = crc32($ob . json_encode(array_values($customSlidesPerView)));
    $dark_wallpaper = $dark_wallps[$wallpaperSeed % count($dark_wallps)] ?? '';
    $light_wallpaper = $light_wallps[$wallpaperSeed % count($light_wallps)] ?? '';
@endphp
<swiper-container 
    navigation="true"
    {{-- pagination="true"  --}}
    {{-- pagination-clickable="true"  --}}
    centered-slides="false" 
    grab-cursor="true"
    keyboard="true" 
    mousewheel="true"
    {{-- loop="true" --}}
    free-mode="true"
    round-lengths="true"
    {{-- effect="fade" --}}
    speed="600" 
    parallax="true"
    {{-- Offsets are 0 on purpose: with slidesPerView, before/after offsets do not shrink slide
         width and the last card clips. Border room is handled via slide padding in CSS. --}}
    slides-offset-before="0"
    slides-offset-after="0"
    {{-- init="false" --}}
    class="game-ribbon w-full"
    breakpoints='{
        "640": {
            "slidesPerView": {{ $slides_per_view['sm'] }},
            "spaceBetween": {{ $space_between['sm'] }}
        },
        "768": {
            "slidesPerView": {{ $slides_per_view['md'] }},
            "spaceBetween": {{ $space_between['md'] }}
        },
        "1024": {
            "slidesPerView": {{ $slides_per_view['xl'] }},
            "spaceBetween": {{ $space_between['xl'] }}
        },
        "1280": {
            "slidesPerView": {{ $slides_per_view['wide'] }},
            "spaceBetween": {{ $space_between['wide'] }}
        },
        "1536": {
            "slidesPerView": {{ $slides_per_view['2xl'] }},
            "spaceBetween": {{ $space_between['2xl'] }}
        }
    }'

    :style="isDark 
        ? '--swiper-navigation-color: #fff; --swiper-pagination-color: #fff; --swiper-pagination-bottom: -11px;'
        : '--swiper-navigation-color: #000; --swiper-pagination-color: #000; --swiper-pagination-bottom: -11px;'
    "
>
    <div slot="container-start" class="parallax-bg"
        :style="isDark 
            ? 'background-image: url({{ $dark_wallpaper }}); opacity: 0.05;'
            : 'background-image: url({{ $light_wallpaper }}); opacity: 0.05;'
        " 
        data-swiper-parallax="-23%"
    ></div>
    
        {{ $slot }}

</swiper-container>