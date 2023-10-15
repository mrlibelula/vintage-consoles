<style>
    :root {
        --currencyPrefix: "$";
    }
    .contenedorCards {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        align-content: center;
        box-sizing: border-box;
    }

    .contenedorCards .card {
        width: 270px;
        transition: ease all 0.3s;
    }

    .contenedorCards .card.esFav .wrapper .infoProd .actions .action.aFavs {
        transform: rotateX(360deg) scale(1.2);
    }

    .contenedorCards .card.esFav .wrapper .infoProd .actions .action.aFavs svg path,
    .contenedorCards .card.esFav .wrapper .infoProd .actions .action.aFavs svg circle {
        fill: #fff;
        transition-delay: 0.2s;
    }

    .contenedorCards .card.enCarrito .wrapper .infoProd .actions .action.alCarrito .inCart {
        transform: scale(1);
    }

    .contenedorCards .card.enCarrito .wrapper .infoProd .actions .action.alCarrito .outCart {
        transform: scale(0);
    }

    .contenedorCards .card .wrapper {
        margin: 0px 0px 0px 0px;
        padding-top: 260px;
        box-sizing: border-box;
        position: relative;
        transition: ease all 0.3s;
    }

    .contenedorCards .card .wrapper:hover {
        transform: translateY(-10px);
    }

    .contenedorCards .card .wrapper:hover .imgProd {
        height: 165px;
    }

    .contenedorCards .card .wrapper .colorProd {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 200px;
        /* background-color: #b82d44; */
        background-color: #181818;
        background-size: cover;
        background-position: center bottom;
        background-repeat: no-repeat;
    }

    .contenedorCards .card .wrapper .imgProd {
        background-size: contain;
        background-position: center bottom;
        background-repeat: no-repeat;
        position: absolute;
        bottom: calc(100% - 265px);
        width: 100%;
        height: 120px;
        transition: ease all 0.3s;
    }

    .contenedorCards .card .wrapper .infoProd {
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

    .contenedorCards .card .wrapper .infoProd p {
        width: 100%;
        /* font-size: 14px; */
        text-align: center;
    }

    .contenedorCards .card .wrapper .infoProd .nombreProd {
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

    .contenedorCards .card .wrapper .infoProd .extraInfo {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .contenedorCards .card .wrapper .infoProd .actions {
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

    .contenedorCards .card .wrapper .infoProd .actions .preciosGrupo {
        flex-grow: 1;
        position: relative;
    }

    .contenedorCards .card .wrapper .infoProd .actions .preciosGrupo .precio {
        /* font-family: "Roboto", sans-serif; */
        color: #1d1d1d;
        /* font-size: 25px; */
        font-weight: 600;
        text-align: left;
    }

    .contenedorCards .card .wrapper .infoProd .actions .preciosGrupo .precio.precioOferta {
        position: absolute;
        left: 0;
        top: -15px;
        color: red;
        /* font-size: 15px; */
        text-decoration: line-through;
    }

    .contenedorCards .card .wrapper .infoProd .actions .preciosGrupo .precio.precioOferta:before {
        /* font-size: 12px; */
    }

    .contenedorCards .card .wrapper .infoProd .actions .preciosGrupo .precio:before {
        content: var(--currencyPrefix);
        /* font-size: 20px; */
    }

    .contenedorCards .card .wrapper .infoProd .actions .action {
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

    .contenedorCards .card .wrapper .infoProd .actions .action svg {
        position: absolute;
        transition: cubic-bezier(0.68, -0.55, 0.27, 1.55) all 0.3s;
    }

    .contenedorCards .card .wrapper .infoProd .actions .action svg path,
    .contenedorCards .card .wrapper .infoProd .actions .action svg circle {
        stroke: currentColor;
        fill: transparent;
        transition: ease all 0.3s;
    }

    .contenedorCards .card .wrapper .infoProd .actions .action.aFavs {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1;
        width: 25px;
        height: 25px;
        color: #fff;
    }

    .contenedorCards .card .wrapper .infoProd .actions .action.alCarrito svg.inCart {
        transform: scale(0);
    }
</style>
<div class="h-full flex items-start justify-start">
    <div class="group contenedorCards rounded-xl overflow-hidden border-4 border-cod-gray-800 shadow-md shadow-black bg-gradient-to-b from-black via-black to-cod-gray-800 h-full">
        <div class="card h-full">
            <div class="wrapper rounded-md overflow-hidden">
                <div class="colorProd rounded-md" 
                    style="background-image: url({{ $game['box'] }})"
                ></div>
                <div class="imgProd flex items-center justify-center" 
                    style="background-image: url({{ $game['poster'] }});"
                ></div>
                <div class="infoProd">
                    <p class="mt-6 mb-4 px-6 leading-none text-xl text-cod-gray-300 group-hover:text-cod-gray-100 smooth-300">
                        {{ $game['title'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
