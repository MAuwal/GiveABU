<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>About Us- Give ABU</title>
    <meta name="description" content="Learn about ABU Zaria Endowment and Crowd Funding platform">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/abu_logo_white.png') }}">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gijgo.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/slicknav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @livewireStyles
</head>

<body>
    <!-- Header -->
    @livewire('home.header-pages')
    
    @livewire('home.auth-modal')
    
    <!-- Hero Section -->
    @livewire('home.about-hero')

    <!-- Reason Area -->
    @livewire('home.about-reason')

    <!-- Core Values & History -->
    @livewire('home.core-values')

    <!-- Our Leadership -->
    @livewire('home.our-leaders')

    <!-- Mission Section -->
    <!-- livewire('home.mission-section') -->

    <!-- Vision Section -->
    <!-- livewire('home.vision-section') -->

    <!-- Counter Area -->
    <!-- livewire('home.counter-area') -->

    <!-- App Download Section -->
     <div class="pt-4">
         
         @livewire('home.app-download')
     </div>

    <!-- Project Donations Section -->
    <!-- livewire('home.project-donations') -->

    <!-- Footer -->
    @livewire('home.footer-area')

    <!-- JS -->
    <script src="{{ asset('js/vendor/modernizr-3.5.0.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/ajax-form.js') }}"></script>
    <script src="{{ asset('js/waypoints.min.js') }}"></script>
    <script src="{{ asset('js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/scrollIt.js') }}"></script>
    <script src="{{ asset('js/jquery.scrollUp.min.js') }}"></script>
    <script src="{{ asset('js/wow.min.js') }}"></script>
    <script src="{{ asset('js/nice-select.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slicknav.min.js') }}"></script>
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/gijgo.min.js') }}"></script>
    <script src="{{ asset('js/contact.js') }}"></script>
    <script src="{{ asset('js/jquery.ajaxchimp.min.js') }}"></script>
    <script src="{{ asset('js/jquery.form.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/mail-script.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    @livewireScripts
</body>
</html>
