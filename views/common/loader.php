<div class="loaderMask">
    <div class="loader">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>
<style>
    .loaderMask  {
        position: fixed;
        top: 0px;
        left: 0px;
        background-color: rgba(0, 0, 0, 0.1);
        z-index: 9998;
        display: none;
        width: 100%;
        height: 100%;
    }

    .loader {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 0px;
    }

    .loader span {
        position: absolute;
        width: 30px;
        height: 30px;
        background: #fff;
        border-radius: 4px;
        animation: animate 2s linear infinite;
    }

    .loader span:nth-child(1) {
        animation-delay: 0s;
        background: #3278a0;
    }

    .loader span:nth-child(2) {
        animation-delay: -0.667s;
        background: #96b2c2;
    }

    .loader span:nth-child(3) {
        animation-delay: -1.33s;
        background: #7aa8c0;
    }

    @keyframes animate {
        0% {
            top: 0px;
            left: 0px;
        }

        12.5% {
            top: 0px;
            left: 50%;
        }

        25% {
            top: 0px;
            left: 50%;
        }

        37.5% {
            top: 50%;
            left: 50%;
        }

        50% {
            top: 50%;
            left: 50%;
        }

        62.5% {
            left: 0px;
            top: 50%;
        }

        75% {
            left: 0px;
            top: 50%;
        }

        87.5% {
            top: 0px;
            left: 0px;
        }

        100% {
            top: 0px;
            left: 0px;
        }
    }
</style>
<script>
    function showLoader() {
        $(".loaderMask").show();
    }

    function hideLoader() {
        $(".loaderMask").hide();
    }

    $(document).ajaxStart(function () {
        showLoader();
    });
    $(document).ajaxComplete(function () {
        hideLoader();
    });
</script>