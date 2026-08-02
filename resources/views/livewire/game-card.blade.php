<div class="flex h-[calc(275px+2rem)] w-full min-w-0 shrink-0 items-stretch justify-start py-4">
    <style>
        .containerCards {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            align-content: center;
            box-sizing: border-box;
            width: 100%;
        }
    
        .containerCards .card {
            width: 100%;
            transition: ease all 0.3s;
        }
    
        .containerCards .card.esFav .wrapper .infoProd .actions .action.aFavs {
            transform: rotateX(360deg) scale(1.2);
        }
    
        .containerCards .card.esFav .wrapper .infoProd .actions .action.aFavs svg path,
        .containerCards .card.esFav .wrapper .infoProd .actions .action.aFavs svg circle {
            fill: #fff;
            transition-delay: 0.2s;
        }
    
        .containerCards .card.enCarrito .wrapper .infoProd .actions .action.alCarrito .inCart {
            transform: scale(1);
        }
    
        .containerCards .card.enCarrito .wrapper .infoProd .actions .action.alCarrito .outCart {
            transform: scale(0);
        }
    
        .containerCards .card .wrapper {
            margin: 0px 0px 0px 0px;
            padding-top: 275px;
            box-sizing: border-box;
            position: relative;
            transition: ease all 0.3s;
        }
    
        .containerCards .card .wrapper:hover .imgProd {
            height: 88px;
        }
    
        .containerCards .card .wrapper .mediaContainer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 165px;
            overflow: hidden;
        }

        .containerCards .card .wrapper:hover .colorProd {
            transform: scale(1.06);
        }
    
        .containerCards .card .wrapper .colorProd {
            display: block;
            position: absolute;
            /* Slightly oversized + left-biased so cover crops baked-in left letterboxing on tall GIFs */
            top: -7.5%;
            left: -10%;
            width: 120%;
            height: 115%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transform-origin: center center;
            transition: ease all 0.3s;
        }

        .containerCards .card .wrapper .colorProd video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
    
        .containerCards .card .wrapper .imgProd {
            background-size: contain;
            background-position: center bottom;
            background-repeat: no-repeat;
            position: absolute;
            bottom: calc(100% - 242px);
            width: 100%;
            height: 66px;
            transition: ease all 0.3s;
        }
    
        .containerCards .card .wrapper .infoProd {
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: center;
            align-content: center;
            box-sizing: border-box;
        }
    
        .containerCards .card .wrapper .infoProd p {
            width: 100%;
            text-align: center;
        }
    
        .containerCards .card .wrapper .infoProd .nombreProd {
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
        }
    
        .containerCards .card .wrapper .infoProd .extraInfo {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
    
        .containerCards .card .wrapper .infoProd .actions {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            align-content: center;
            width: 100%;
            margin-top: auto;
            padding-top: 10px;
        }
    
        .containerCards .card .wrapper .infoProd .actions .preciosGrupo {
            flex-grow: 1;
            position: relative;
        }
    
        .containerCards .card .wrapper .infoProd .actions .preciosGrupo .precio {
            /* font-family: "Roboto", sans-serif; */
            color: #1d1d1d;
            /* font-size: 25px; */
            font-weight: 600;
            text-align: left;
        }
    
        .containerCards .card .wrapper .infoProd .actions .preciosGrupo .precio.precioOferta {
            position: absolute;
            left: 0;
            top: -15px;
            color: red;
            /* font-size: 15px; */
            text-decoration: line-through;
        }
    
        .containerCards .card .wrapper .infoProd .actions .preciosGrupo .precio.precioOferta:before {
            /* font-size: 12px; */
        }
    
        .containerCards .card .wrapper .infoProd .actions .preciosGrupo .precio:before {
            content: var(--currencyPrefix);
            /* font-size: 20px; */
        }
    
        .containerCards .card .wrapper .infoProd .actions .action {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            align-content: center;
            margin-left: 15px;
            width: 35px;
            height: 35px;
            position: relative;
            transition: cubic-bezier(0.68, -0.55, 0.27, 1.55) all 0.3s;
            cursor: pointer;
            color: #1d1d1d;
        }
    
        .containerCards .card .wrapper .infoProd .actions .action svg {
            position: absolute;
            transition: cubic-bezier(0.68, -0.55, 0.27, 1.55) all 0.3s;
        }
    
        .containerCards .card .wrapper .infoProd .actions .action svg path,
        .containerCards .card .wrapper .infoProd .actions .action svg circle {
            stroke: currentColor;
            fill: transparent;
            transition: ease all 0.3s;
        }
    
        .containerCards .card .wrapper .infoProd .actions .action.aFavs {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1;
            width: 25px;
            height: 25px;
            color: #fff;
        }
    
        .containerCards .card .wrapper .infoProd .actions .action.alCarrito svg.inCart {
            transform: scale(0);
        }
    </style>
    <div class="group relative containerCards h-full w-full overflow-hidden rounded-xl border-2 border-cod-gray-500 bg-cod-gray-200/80 shadow dark:border-cod-gray-700/50 dark:bg-cod-gray-900/90">
        <div class="card h-full">
            @if($showConsoleLabel && isset($game->console) && $game->console)
            <div class="absolute z-20 text-center w-full bg-cod-gray-500/70 dark:bg-cod-gray-800/70 text-xs font-semibold font-mono py-1 text-white/50 dark:text-gray-400">
                {{ $game->console->short_name === 'atari2600' ? 'Atari 2600' : $game->console->short_name }}
            </div>
        @endif
            @php
                $boxUrl   = $game->game_preview ?? '';
                $boxLower = strtolower($boxUrl);
                $isWebm   = str_contains($boxLower, '.webm');
                $isMp4    = str_contains($boxLower, '.mp4');
                $isVideo  = $isWebm || $isMp4;
                $mp4Url   = $isWebm ? str_replace('.webm', '.mp4', $boxUrl) : ($isMp4 ? $boxUrl : '');
            @endphp
            <div class="wrapper flex h-full w-full items-center justify-between">
                <div class="mediaContainer">
                    @if ($isVideo)
                        <!-- Video box background -->
                        <div class="colorProd lazy-load-video">
                            <video autoplay muted loop playsinline>
                                @if ($isWebm)
                                    <source data-src="{{ $boxUrl }}" type="video/webm">
                                @endif
                                @if ($mp4Url)
                                    <source data-src="{{ $mp4Url }}" type="video/mp4">
                                @endif
                            </video>
                        </div>
                    @else
                        <!-- GIF / image box background -->
                        <div
                            class="colorProd lazy-load-bg bg-cod-gray-300 dark:bg-cod-gray-800"
                            data-bg-url="{{ $boxUrl }}"
                        ></div>
                    @endif
                </div>
                <div class="absolute top-0 w-full h-[165px] bg-gradient-to-b from-transparent/30 via-transparent to-cod-gray-200 dark:from-transparent dark:via-transparent dark:to-cod-gray-900 pointer-events-none">
                </div>
                <!-- Placeholder for the poster background -->
                
                <div class="imgProd flex items-center justify-center lazy-load-bg opacity-70 group-hover:opacity-90 smooth-300" 
                    {{-- data-bg-url="{{ $game['poster'] }}"  --}}
                    {{-- style="background-image: url({{ asset('images/placeholder-poster-homer.jpg') }});" --}}
                >
                </div>
                <div class="infoProd absolute inset-x-0 bottom-0 top-[165px] flex items-center justify-center text-center">
                    <p class="nombreProd min-h-[2.5rem] min-w-0 w-full px-2 py-2 text-center text-[1rem] font-thin leading-tight tracking-wider text-cod-gray-800 group-hover:text-rose-700 dark:text-cod-gray-300 dark:group-hover:text-rose-400 smooth-300">
                        {{ Str::limit($game->title, 55) }}
                    </p>
                    
                </div>
            </div>
        </div>
    </div>
</div>
