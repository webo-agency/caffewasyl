jQuery(document).ready(function ($) {
    'use strict';
    var customPostsSlider = $('.gpl-slick-slider');
    var sectionNavigation   = '';
    customPostsSlider.each(function () {
        var sectionId = '#' + $(this).attr('id');
        sectionNavigation = $(sectionId).data('navigation');
        
        $(sectionId).children('.gpl-post-slider-one').slick({
            customPaging: function(slider, i){
                var thumb = $(slider.$slides[i]).data('thumb');
                if( sectionNavigation === 'thumbnail' ) {
                    return ('<a><img src="' + thumb + '"/></a>');
                } else {
                    return('<button>'+i+'</button>');
                }
            },
            arrows:  $(this).data('navigation') === 'dots' || $(this).data('navigation') === 'none' || $(this).data('navigation') === 'thumbnail' ? false : true,
            dots: $(this).data('navigation') === 'arrows' || $(this).data('navigation') === 'none' ? false : true,
            infinite: true,
            speed: 500,
            slidesToShow: $(this).data('count') === 1 ? 1 : $(this).data('slidesToShow'),
            slidesToScroll: 1,
            autoplay: $(this).data('autoplay'),
            dotsClass: $(this).data('navigation') === 'thumbnail' ? "slick-dots slick-thumb" : "slick-dots",
            autoplaySpeed: 3000,
            cssEase: "linear",
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]

        });
    });
});