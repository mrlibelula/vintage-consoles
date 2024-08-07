<div class="h-full_ h-[17rem] flex items-start justify-start py-4">
    <style>
        .containerCards {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            align-content: center;
            box-sizing: border-box;
        }
    
        .containerCards .card {
            width: 230px;
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
            padding-top: 260px;
            box-sizing: border-box;
            position: relative;
            transition: ease all 0.3s;
        }
    
        .containerCards .card .wrapper:hover {
            transform: translateY(-10px);
        }
    
        .containerCards .card .wrapper:hover .imgProd {
            height: 165px;
        }
    
        .containerCards .card .wrapper .colorProd {
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            /* height: 157px; */
            height: 177px;
            background-size: cover;
            background-position: center bottom;
            background-repeat: no-repeat;
            border-radius: 0px;
        }
    
        .containerCards .card .wrapper .imgProd {
            background-size: contain;
            background-position: center bottom;
            background-repeat: no-repeat;
            position: absolute;
            bottom: calc(100% - 225px);
            width: 100%;
            height: 100px;
            transition: ease all 0.3s;
        }
    
        .containerCards .card .wrapper .infoProd {
            display: flex;
            flex-direction: column;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: center;
            align-content: center;
            /* height: 170px; */
            /* padding: 20px; */
            box-sizing: border-box;
        }
    
        .containerCards .card .wrapper .infoProd p {
            width: 100%;
            /* font-size: 14px; */
            text-align: center;
        }
    
        .containerCards .card .wrapper .infoProd .nombreProd {
            /* font-family: "Roboto", sans-serif; */
            /* margin-bottom: 10px; */
            /* font-size: 16px; */
            /* font-weight: 600; */
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
    <div class="group relative containerCards rounded-xl overflow-hidden bg-cod-gray-200/80 dark:bg-cod-gray-900/90 h-full">
        <div class="card h-full">
            @isset($game['console_short_name'])
            <div class="absolute z-20 text-center w-full dark:bg-cod-gray-800 text-xs font-semibold font-mono py-1 text-white/50 dark:text-gray-400 dark:opacity-80">
                {{ $game['console_short_name'] === 'atari2600' ? 'Atari 2600' : $game['console_short_name'] }}
            </div>
            @endisset
            <div class="wrapper overflow-hidden h-full justify-between">
                <!-- Placeholder for the box background -->
                <div class="colorProd relative lazy-load-bg" 
                    data-bg-url="{{ $game['box'] }}" 
                    style="background-image: url({{ asset('images/placeholder-wide-dark.jpg') }});"
                >
                </div>
                <div class=" absolute top-0 w-full h-[177px] bg-gradient-to-b from-transparent/30 via-transparent/50 to-cod-gray-200 dark:from-transparent/30 dark:via-transparent/50 dark:to-cod-gray-900">
                    &nbsp;
                </div>
                <!-- Placeholder for the poster background -->
                <div class="imgProd flex items-center justify-center lazy-load-bg" 
                    data-bg-url="{{ $game['poster'] }}" 
                    style="background-image: url({{ asset('images/placeholder-poster-homer.jpg') }});">
                </div>
                <div class="infoProd_ flex flex-col justify-between text-center h-full">
                    <p class="-mt-4 px-6 leading-none text-xl text-cod-gray-800 group-hover:text-rose-700 dark:text-cod-gray-300 dark:group-hover:text-rose-400 smooth-300">
                        {{ Str::limit($game['title'], 25) }}
                    </p>
                    
                </div>
            </div>
        </div>
    </div>
</div>
