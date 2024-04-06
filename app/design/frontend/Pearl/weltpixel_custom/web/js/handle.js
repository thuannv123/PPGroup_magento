define(["jquery", "matchMedia"], function ($, matchMedia) {
    "use strict";

    $(document).ready(function () {
        //load more promotion
        $(".online-promotion .promotion-content:lt(6)").show();
        $(".partner-promotion .promotion-content:lt(4)").show();
        $("#load-more-promotion").on("click", function () {
            $(".online-promotion .promotion-content:hidden:lt(6)").slideDown();
            $(".partner-promotion .promotion-content:hidden:lt(4)").slideDown();
            if ($(".promotion-content:hidden").length === 0) {
                $("#load-more-promotion").hide();
            }
        });

        $(".footer-bottom .no-padding .row .mobile-toggle .no-padding-mob").click(function () {
            $(this).parent().toggleClass("active");
        });
        //close menu mobile
        $(".close-menu-mobile").click(function () {
            $("html").removeClass("nav-open");
            setTimeout(function () {
                $("html").removeClass("nav-before-open");
            }, 500);
        });

        $(".child-member .member-title").click(function () {
            $(this).parent().toggleClass("active");
        });


        // logic filter category page

        if ($('body').hasClass('catalog-category-view') &&
            $('#amasty-shopby-product-list').length
        ) {
            var $widthbody = $('.page-wrapper').width(),
                $widthContent = $('.page-main').width(),
                $cateImage = $(".catalog-category-view .page-wrapper .category-image"),
                $cateBreadcrumbs = $('.catalog-category-view .page-wrapper .breadcrumbs');


            $(document).on('click', '.custom-filter', function () {
                $(this).toggleClass('active');
                $('.filter-content').toggleClass('active');
                $('.sidebar-main').toggleClass('active')
            })

            if ($cateImage.length > 0) {
                $($cateBreadcrumbs).appendTo($cateImage);
            };
            $(window).scroll(function () {
                if ($(window).width() >= 769) {
                    var $cateView = $('.catalog-category-view .page-wrapper .category-view');
                    if ($cateView.length > 0) {
                        var scrollPosition = $(window).scrollTop();
                        if ($cateImage.length > 0) {
                            if (scrollPosition >= 760) {
                                $cateView.css({
                                    "background-color": "#ffffff",
                                    "position": "fixed",
                                    "top": "56px",
                                    "width": $widthContent,
                                    "left": (($widthbody - $widthContent) / 2),
                                    "z-index": "9"
                                });
                            } else {
                                $cateView.css({
                                    "position": "relative",
                                    "left": "0",
                                    "top": "50%",
                                    "z-index": "3"
                                });
                            };
                        } else {
                            if (scrollPosition >= 160) {
                                $cateView.css({
                                    "background-color": "#ffffff",
                                    "position": "fixed",
                                    "top": "56px",
                                    "width": $widthContent,
                                    "left": (($widthbody - $widthContent) / 2),
                                    "z-index": "9"
                                });
                            } else {
                                $cateView.css({
                                    "position": "relative",
                                    "left": "0",
                                    "top": "50%",
                                    "z-index": "3"
                                });

                            };
                        }
                    }
                } else {
                    var $filterActive = $('.catalog-category-view .page-wrapper .sidebar-main .block.filter'),
                        scrollPosition = $(window).scrollTop(),
                        filterFixed = $('.theme-pearl:not(.filter-active) #layered-filter-block .filter-title strong');

                    if ($cateImage.length > 0) {
                        if (scrollPosition > 350) {
                            // have banner
                            $filterActive.addClass('scroll-filter');
                            $('.toolbar-sorter .custom-select').addClass('toolbar-scroll');
                            $(filterFixed).css({
                                "position": "fixed",
                                "top": "54px",
                                "z-index": "22",
                                "left": "15px"
                            });
                            $('.toolbar-sorter').css({
                                "z-index": "21"
                            });
                            $('.toolbar-sorter .custom-select .select-selected').css({
                                "position": "fixed",
                                "top": "54px",
                                "right": "15px",
                                "text-align": "right",
                                "background": "#ffffff",
                                "width": "100%"
                            })
                            if($('html').attr('lang') == 'th'){
                                $('.toolbar-sorter .custom-select .select-selected.active').css({
                                    "top": "75px"
                                })
                            }
                            $('.toolbar-sorter .custom-select .select-items').css({
                                "position": "relative",
                                "top": "30px"
                            })
                        } else {
                            $filterActive.removeClass('scroll-filter');
                            $('.toolbar-sorter .custom-select').removeClass('toolbar-scroll');
                            $(filterFixed).css({
                                "position": "absolute",
                                "top": "0",
                                "left": "0"
                            });
                            $('.toolbar-sorter .custom-select .select-selected').css("position", "unset");
                            $('.toolbar-sorter .custom-select .select-items').css({
                                "position": "unset"
                            })
                        };
                    } else {
                        if (scrollPosition > 150) {
                            $filterActive.addClass('scroll-filter');
                            $('.toolbar-sorter .custom-select').addClass('toolbar-scroll');
                            $(filterFixed).css({
                                "position": "fixed",
                                "top": "54px",
                                "z-index": "22",
                                "left": "15px"
                            });
                            $('.toolbar-sorter').css({
                                "z-index": "21"
                            });
                            $('.toolbar-sorter .custom-select .select-selected').css({
                                "position": "fixed",
                                "top": "54px",
                                "right": "15px",
                                "text-align": "right",
                                "background": "#ffffff",
                                "width": "100%"
                            })
                            if($('html').attr('lang') == 'th'){
                                $('.toolbar-sorter .custom-select .select-selected.active').css({
                                    "top": "75px"
                                })
                            }
                            $('.toolbar-sorter .custom-select .select-items').css({
                                "position": "relative",
                                "top": "30px"
                            })
                        }
                        else {
                            $filterActive.removeClass('scroll-filter');
                            $('.toolbar-sorter .custom-select').removeClass('toolbar-scroll');
                            $(filterFixed).css({
                                "position": "absolute",
                                "top": "0",
                                "left": "0"
                            });
                            $('.toolbar-sorter .custom-select .select-selected').css("position", "unset");
                            $('.toolbar-sorter .custom-select .select-items').css({
                                "position": "unset"
                            })
                        };

                    }
                }
            });
        }else if(
            $('body').hasClass('catalog-category-view') &&
            $('#amasty-shopby-product-list').length == 0
        ){
            var $cateView = $('.catalog-category-view .page-wrapper .category-view');
            if($cateView.length && $(window).width() >= 769){
                $cateView.css({'display': 'block'});
            }
        }

        //logic search page
        if ($('body').hasClass('catalogsearch-result-index')) {


            $(document).on('click', '.custom-filter', function () {
                $(this).toggleClass('active');
                $('.filter-content').toggleClass('active');
                $('.sidebar-main').toggleClass('active')
            })
            if ($(window).width() >= 769) {
                var $toolbarSearch = $('.catalogsearch-result-index .page-wrapper .toolbar-products'),
                    $widthbody = $('.page-wrapper').width(),
                    $widthContent = $('.page-main').width(),
                    $sidebarSearch = $('.catalogsearch-result-index .page-wrapper .sidebar-main'),
                    $titleSearch = $('.catalogsearch-result-index .page-wrapper .page-title-wrapper');
                if ($titleSearch.length > 0) {
                    $titleSearch.append($toolbarSearch);
                    $titleSearch.append($sidebarSearch);
                }

                //scroll 
                var scrollPosition = $(window).scrollTop();

                if (scrollPosition >= 360) {
                    $titleSearch.css({
                        "background-color": "#ffffff",
                        "position": "fixed",
                        "top": "56px",
                        "width": $widthContent,
                        "left": (($widthbody - $widthContent) / 2),
                        "z-index": "9"
                    });
                } else {
                    $titleSearch.css({
                        "position": "relative",
                        "left": "0",
                        "top": "50%",
                        "z-index": "3"
                    });
                };
            }
        }

        $(window).on('scroll', function () {
            const scrollTop = $(window).scrollTop();
            if (scrollTop == 0) {
                var headerHeight = parseInt($('.header-placeholder').height());
                $('.quickcart-wrapper .block-quickcart').css('top', headerHeight);
            } else {
                var headerHeight = parseInt($('.page-header').height());
                $('.quickcart-wrapper .block-quickcart').css('top', headerHeight);
            }
        })

    });
});
