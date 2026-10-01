<footer class="footer" style="background-color: #022c22; color: #fff; position: relative; padding-top: 80px; font-family: 'Inter', sans-serif;">
    <!-- Wave Shape -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; overflow: hidden; line-height: 0;">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" style="position: relative; display: block; width: calc(100% + 1.3px); height: 60px;">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="#ffffff"></path>
        </svg>
    </div>

    <div class="footer_top" style="padding: 60px 0 40px;">
        <div class="container">
            <div class="row">
                <!-- Column 1: About & Contact -->
                <div class="col-lg-5 col-md-6 mb-4 mb-lg-0">
                    <div class="footer_widget">
                        <div class="footer_logo mb-4">
                            <a href="{{ url('/') }}" class="d-flex align-items-center" style="text-decoration: none;">
                                <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                    <img src="{{ asset('abu_logo.png') }}" alt="ABU Logo" style="height: 30px; width: auto; filter: brightness(0) invert(1);">
                                </div>
                                <span style="font-size: 1.25rem; font-weight: 700; color: #fff;">GIVE ABU</span>
                            </a>
                        </div>
                        <p style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; max-width: 400px;">
                            Supporting the advancement of education, research, and infrastructure at Ahmadu Bello University through sustainable funding and community engagement.
                        </p>
                        
                        <div class="contact_info">
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-map-marker-alt mt-1 mr-3" style="color: #10b981;"></i>
                                <span style="color: #d1d5db; font-size: 0.95rem;">Ahmadu Bello University Main Campus, Zaria</span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-envelope mr-3" style="color: #10b981;"></i>
                                <a href="mailto:contact@abu-endowment.cloud" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none;">contact@giveabu.com</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone-alt mr-3" style="color: #10b981;"></i>
                                <a href="tel:+234800ABUFUND" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none;">+234-803-663-0915</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-2 col-md-3 col-6 mb-4 mb-lg-0">
                    <div class="footer_widget">
                        <h4 style="color: #fff; font-size: 1rem; text-decoration: none; text-transform: uppercase; margin-bottom: 25px; letter-spacing: 0.5px;">Quick Links</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3"><a href="{{ url('/') }}" 
                            style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Home</a></li>
                            <li class="mb-3"><a href="{{ route('about') }}" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">About</a></li>
                            <li class="mb-3"><a href="#" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Projects</a></li>
                            <li class="mb-3"><a href="#make-donation" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Donate</a></li>
                            <li class="mb-3"><a href="#" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Contact</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 3: Legal -->
                <div class="col-lg-2 col-md-3 col-6 mb-4 mb-lg-0">
                    <div class="footer_widget">
                        <h4 style="color: #fff; font-size: 1rem;  text-transform: uppercase; margin-bottom: 25px; letter-spacing: 0.5px;">Legal</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3"><a href="#" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Privacy Policy</a></li>
                            <li class="mb-3"><a href="#" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Terms of Service</a></li>
                            <li class="mb-3"><a href="#" style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; text-decoration: none; transition: color 0.3s;">Cookie Policy</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 4: Connect -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer_widget">
                        <h4 style="color: #fff; font-size: 1rem;  text-transform: uppercase; margin-bottom: 25px; letter-spacing: 0.5px;">Connect With Us</h4>
                        <div class="social_links d-flex gap-3">
                            <a href="#" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; transition: all 0.3s; margin-right: 10px;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; transition: all 0.3s; margin-right: 10px;">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; transition: all 0.3s;">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="copy-right_text" style="border-top: 1px solid rgba(255,255,255,0.1); padding: 25px 0;">
        <div class="container">
            <p style="color: #9ca3af; font-size: 0.9rem; margin: 0; text-align: center;">
                &copy; <script>document.write(new Date().getFullYear());</script> Ahmadu Bello University Zaria Development Foundation. All rights reserved.
            </p>
        </div>
    </div>

    <style>
        /* Footer Typography */
        .footer h4,
        .footer_logo span {
            font-family: 'Merriweather', serif !important;
        }



        .footer p, 
        .contact_info span, 
        .contact_info a,
        .footer .list-unstyled li a,
        .copy-right_text {
            font-family: 'Inter', sans-serif !important;
        }
    </style>
</footer>
