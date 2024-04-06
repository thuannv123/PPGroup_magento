require([
    'jquery',
    'domReady!'
], function ($) {
    var h_reduce = 0;

    var setHeightSwatch = setInterval(heightSwatch, 100);
    function stopHeightSwatch() {
        clearInterval(setHeightSwatch);
    }

    function heightSwatch() {
        if ($('.product-sticky-bottom').outerHeight() > $('.sys-info-replace').outerHeight()) {
            $('.sys-info-replace').css('height',$('.product-sticky-bottom').outerHeight());
        }
    }

    function changeImage() {
        if($('.image-replace-sticky img').length == 0) {
            $('.image-replace-sticky').html($('.fotorama__stage__frame:first').html());
        } else {
            if($('.image-replace-sticky img').attr('src') != $('.fotorama__stage__frame:first img').attr('src')) {

                $('.image-replace-sticky img').attr('src',$('.fotorama__stage__frame:first img').attr('src'));
            }
        }
    }

    setInterval(changeImage, 100);

    function changePriceSticky() {
        $('.product-info-price-sticky').html($('.price-box.price-final_price').html());
    }

    setInterval(changePriceSticky, 100);

    $(window).scroll(function() {
        var scrollTop  = $(window).scrollTop();
        h_reduce = $('.product-info-right').offset().top + $('.product-info-right').outerHeight() - 25;
        if (scrollTop >= h_reduce) {
            $('.product-info-main').addClass('sticky');
            $('.page-wrapper').css('padding-bottom', $('.product-sticky-bottom').outerHeight());
            $('.btt-button.action').addClass('has-sticky');
        } else {
            $(".product-info-main").removeClass('sticky');
            $('.page-wrapper').css('padding-bottom', 0);
            $('.btt-button.action').removeClass('has-sticky');
        }
    });
});
