<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'ABU Endowment - Charity Organization')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="@yield('keywords', '')" name="keywords">
    <meta content="@yield('description', '')" name="description">

    <!-- Favicon -->
    <link href="{{ asset('img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@600;700&family=Open+Sans&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->

    @yield('content')

    <!-- Footer Start -->
     
    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-5 py-5">
                <!-- Contact / Office -->
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Our Office</h4>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Ahmadu Bello University, Samaru, Zaria, Kaduna State, Nigeria</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+234 701 234 5678</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>endowment@abu.edu.ng</p>
                    <div class="d-flex pt-3">
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-x-twitter"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-primary me-2" href="#!"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Quick Links</h4>
                    <a class="btn btn-link" href="#about">About Us</a>
                    <a class="btn btn-link" href="#donate">Make a Donation</a>
                    <a class="btn btn-link" href="#projects">Our Projects</a>
                    <a class="btn btn-link" href="#faq">FAQs</a>
                    <a class="btn btn-link" href="#contact">Contact Us</a>
                </div>

                <!-- Hours -->
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Office Hours</h4>
                    <p class="mb-1">Monday - Friday</p>
                    <h6 class="text-light">08:00 am - 05:00 pm</h6>
                    <p class="mb-1">Saturday - Sunday</p>
                    <h6 class="text-light">Closed</h6>
                </div>

                <!-- Gallery -->
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-light mb-4">Gallery</h4>
                    <div class="row g-2">
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('img/gallery-1.jpg') }}" alt="ABU Campus">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('img/gallery-2.jpg') }}" alt="Endowment Project">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('img/gallery-3.jpg') }}" alt="Student Beneficiaries">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('img/gallery-4.jpg') }}" alt="Community Project">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('img/gallery-5.jpg') }}" alt="ABU Alumni">
                        </div>
                        <div class="col-4">
                            <img class="img-fluid w-100" src="{{ asset('img/gallery-6.jpg') }}" alt="Research Facility">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="copyright pt-5">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="fw-semi-bold" href="#!">ABU Endowment</a>, All Rights Reserved.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        Developed for <a class="fw-semi-bold" href="#!">Ahmadu Bello University</a> | Supported by Alumni & Partners
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#!" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('lib/wow/wow.min.js') }}"></script>
    <script src="{{ asset('lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('lib/counterup/counterup.min.js') }}"></script>

    <!-- Template Javascript -->
    <script src="{{ asset('js/main.js') }}"></script>

    @stack('scripts')
</body>

</html>