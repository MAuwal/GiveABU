(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();


    // Initiate the wowjs
    new WOW().init();


    // TOP Menu Sticky
    $(window).on('scroll', function () {
        var scroll = $(window).scrollTop();
        if (scroll < 400) {
            $("#sticky-header").removeClass("sticky");
        } else {
            $("#sticky-header").addClass("sticky");
        }
    });


    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 1500, 'easeInOutExpo');
        return false;
    });


    // Modal Video
    $(document).ready(function () {
        var $videoSrc;
        $('.btn-play').click(function () {
            $videoSrc = $(this).data("src");
        });
        console.log($videoSrc);

        $('#videoModal').on('shown.bs.modal', function (e) {
            $("#video").attr('src', $videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0");
        })

        $('#videoModal').on('hide.bs.modal', function (e) {
            $("#video").attr('src', $videoSrc);
        })
    });


    // Facts counter
    $('[data-toggle="counter-up"]').counterUp({
        delay: 10,
        time: 2000
    });


    // Donation progress
    $('.donation-item .donation-progress').waypoint(function () {
        $('.donation-item .progress .progress-bar').each(function () {
            $(this).css("height", $(this).attr("aria-valuenow") + '%');
        });
    }, { offset: '80%' });


    // Header carousel
    $(".header-carousel").owlCarousel({
        animateOut: 'rotateOutUpRight',
        animateIn: 'rotateInDownLeft',
        items: 1,
        autoplay: true,
        smartSpeed: 1000,
        dots: false,
        loop: true,
        nav: true,
        navText: [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        items: 1,
        autoplay: true,
        smartSpeed: 1000,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        dots: false,
        loop: true,
        nav: true,
        navText: [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });


    // Mobile Menu
    var menu = $('ul#navigation');
    if (menu.length) {
        menu.slicknav({
            prependTo: ".mobile_menu",
            closedSymbol: '<i class="fa fa-bars"></i>',
            openedSymbol: '<i class="fa fa-times"></i>',
            label: '', // Remove the "MENU" label
            closeOnClick: true // Close menu when a link is clicked
        });
    };

    // Click away to close menu
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.slicknav_menu').length && !$(e.target).closest('.mobile_menu').length) {
            $('.slicknav_nav').slideUp();
            $('.slicknav_btn').removeClass('slicknav_open').addClass('slicknav_collapsed');
            $('.slicknav_btn').attr('aria-expanded', 'false');
        }
    });

})(jQuery);

