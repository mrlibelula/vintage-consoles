@push('styles')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
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
        background: url("images/libe.jpg") center center no-repeat;
        background-size: 320px;
        filter: gray;
        /* IE6-9 */
        -webkit-filter: grayscale(1);
        /* Google Chrome, Safari 6+ & Opera 15+ */
        filter: grayscale(1);
        /* Microsoft Edge and Firefox 35+ */
    }

    .card0:hover {
        background: url("images/libe.jpg") left center no-repeat;
        background-size: 300px;
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
        height: 200px;
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
            <a href="https://facebook.com/mrlibelula/" target="_other_FB">
                <i class="fa fa-facebook hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            </a>
            <a href="https://twitter.com/mrlibelula/" target="_other_X">
                <i class="fa fa-twitter hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            </a>
            <a href="https://github.com/mrlibelula/" target="_other_github">
                <i class="fa fa-github hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            </a>
            <a href="https://www.linkedin.com/in/mrlibelula/" target="_other_in">
                <i class="fa fa-linkedin hover:text-gray-300 cursor-pointer" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</div>