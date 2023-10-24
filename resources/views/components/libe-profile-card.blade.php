@push('styles')
<style>
    .border-card {
        height: 259px;
        width: 200px;
        background: transparent;
        border-radius: 10px;
        transition: border 1s;
        position: relative;
    }

    .cardy {
        height: 269px;
        width: 210px;
        background: #808080;
        border-radius: 10px;
        transition: background 1s;
        overflow: hidden;
        background: #000;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    .card0 {
        background: url("https://libe.dev/images/libe.jpg") center center no-repeat;
        background-size: 320px;
        filter: gray;
        /* IE6-9 */
        -webkit-filter: grayscale(1);
        /* Google Chrome, Safari 6+ & Opera 15+ */
        filter: grayscale(1);
        /* Microsoft Edge and Firefox 35+ */
    }

    .card0:hover {
        background: url("https://libe.dev/images/libe.jpg") left center no-repeat;
        background-size: 400px;
    }

    .card0:hover .fa {
        opacity: 1;
    }

    .fa {
        opacity: 0;
        transition: opacity 1s;
    }

    .icons {
        position: absolute;
        fill: #fff;
        color: #fff;
        height: 170px;
        top: 5px;
        left: 5px;
        width: 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-around;
    }
</style>
@endpush
<div class="group cardy card0">
    <div class="border-card">
        <div class="icons rounded-lg group-hover:bg-black transition duration-1000 ease-in-out">
            <i class="fa fa-facebook hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            <i class="fa fa-twitter hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            <i class="fa fa-github hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            <i class="fa fa-linkedin hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
        </div>
    </div>
</div>