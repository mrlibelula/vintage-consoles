@props(['ob' => 'squares', 'customSlidesPerView' => []])
@php
    if ($ob === 'squares') {
        $slides_per_view = [ 
            'sm' => 2,
            'md' => 3,
            'xl' => 4,
        ]; 
        $space_between = [
            'sm' => 5,
            'md' => 10,
            'xl' => 15,
        ];
    }
    if ($ob === 'group') {
        $slides_per_view = [ 
            'sm' => 1,
            'md' => 2,
            'xl' => 3,  
        ]; 
        $space_between = [
            'sm' => 10,
            'md' => 20,
            'xl' => 30,
        ];
    }

    if ($ob === 'group') {
        $slides_per_view = [ 
            'sm' => 1,
            'md' => 2,
            'xl' => 3,
        ]; 
        $space_between = [
            'sm' => 10,
            'md' => 20,
            'xl' => 30,
        ];
    }

    if(count($customSlidesPerView)) {
        $slides_per_view = $customSlidesPerView; 
        $space_between = [
            'sm' => 10,
            'md' => 20,
            'xl' => 30,
        ];
    }

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

    $dark_wallpaper = $dark_wallps[rand(0, count($dark_wallps) - 1)] ?? '';
    $light_wallpaper = $light_wallps[rand(0, count($light_wallps) - 1)] ?? '';
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
    {{-- effect="fade" --}}
    speed="600" 
    parallax="true"
    {{-- init="false" --}}
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

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>
</swiper-container>