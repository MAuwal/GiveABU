<header>
    <div class="header-area">
        <!-- Top Bar -->
        <div class="header-top-bar" style="background: #fff; padding: 10px 0; border-bottom: 1px solid #eee;">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="top-bar-left d-flex align-items-center" style="gap: 25px;">
                            <a href="tel:+2344545565656" style="color: #064e3b; font-size: 13px; font-weight: 500; text-decoration: none;">
                                <i class="fa fa-phone mr-2"></i> +234-803-663-0915
                            </a>
                            <a href="mailto:abuendowment@gmail.com" style="color: #064e3b; font-size: 13px; font-weight: 500; text-decoration: none;">
                                <i class="fa fa-envelope mr-2"></i> info@giveabu.com
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="top-bar-right d-flex align-items-center justify-content-end" style="gap: 20px;">
                            <div class="social-icons d-flex align-items-center" style="gap: 15px;">
                                <a href="#" style="color: #064e3b;"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" style="color: #064e3b;"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" style="color: #064e3b;"><i class="fab fa-twitter"></i></a>
                            </div>
                            <div class="language-selector">
                                <a href="#" style="color: #064e3b; font-size: 13px; font-weight: 500; text-decoration: none;">
                                    <i class="fa fa-globe mr-1"></i> ENG <i class="fa fa-chevron-down ml-1" style="font-size: 10px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div id="sticky-header" class="main-header-area" style="background: #227722; padding: 15px 0; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Logo Section -->
                    <div class="col-6 col-lg-3">
                        <div class="logo">
                            <a href="{{ url('/') }}" class="d-flex align-items-center" style="text-decoration: none;">
                                <img src="{{ asset('abu_logo.png') }}" alt="ALUMNI FUND" style="height: 50px; width: auto; margin-right: 15px; filter: brightness(0) invert(1);">
                                <div class="logo-text" style="display: flex; flex-direction: column; line-height: 1.2;">
                                    <span style="font-size: 18px; font-weight: 700; color: #fff; letter-spacing: -0.5px;">GIVE ABU</span>
                                    <!-- <span style="font-size: 12px; font-weight: 400; color: rgba(255,255,255,0.9); letter-spacing: 0.5px;">& Crowd Funding</span> -->
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Menu Section (Desktop) -->
                    <div class="col-lg-9 d-none d-lg-block">
                        <div class="main-menu d-flex align-items-center justify-content-end">
                            <nav>
                                <ul id="navigation" class="d-flex align-items-center" style="gap: 40px; margin: 0;">
                                    <li><a href="{{ url('/') }}">Home</a></li>
                                    <li><a href="{{ route('about') }}">About</a></li>
                                    <li><a href="{{ route('projects') }}">Projects</a></li>

                                    
                                    <!-- Mobile Only Items -->
                                    <li class="d-lg-none"><a href="#make-donation">Make Donation</a></li>
                                    @if(!$isLoggedIn)
                                        <li class="d-lg-none"><a href="#" wire:click.prevent="$dispatch('openLoginModal')">Sign In</a></li>
                                    @else
                                        <li class="d-lg-none"><a href="#" wire:click.prevent="logout">Logout</a></li>
                                    @endif
                                </ul>
                            </nav>

                            <!-- Desktop Actions -->
                            <div class="header-right-btn d-flex align-items-center ml-5" style="gap: 15px;">
                                <!-- Auth Section -->
                                @if(!$isLoggedIn)
                                    <a href="#" wire:click.prevent="$dispatch('openLoginModal')" 
                                       class="btn header-btn-outline">
                                        <i class="fa fa-user mr-2"></i> Sign in
                                    </a>
                                @else
                                    <div class="dropdown">
                                        <a href="#" class="d-flex align-items-center" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="text-decoration: none; cursor: pointer; color: #fff;">
                                            @if(isset($user['avatar']) && $user['avatar'])
                                                <img src="{{ $user['avatar'] }}" alt="User" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #fff;">
                                            @else
                                                <div style="width: 35px; height: 35px; border-radius: 50%; background: rgba(255,255,255,0.2); color: #fff; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.5);">
                                                    <i class="fa fa-user" style="font-size: 14px;"></i>
                                                </div>
                                            @endif
                                            <span class="ml-2 font-weight-bold">{{ $user['name'] ?? 'User' }}</span>
                                            @if(!$isVerified)
                                                <span title="Email not verified" style="margin-left: 6px; font-size: 14px; line-height: 1;">&#9888;&#65039;</span>
                                            @endif
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown" style="border-radius: 0px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: 10px; min-width: 230px;">
                                            <div class="px-3 py-2 border-bottom">
                                                <small class="text-muted">{{ $user['email'] ?? '' }}</small>
                                            </div>
                                            @if(!$isVerified)
                                                <div style="background: #fffbeb; border-bottom: 1px solid #fde68a; padding: 10px 16px;">
                                                    <p style="color: #92400e; font-size: 0.78rem; margin: 0 0 6px; font-weight: 600;">&#9888; Email not verified</p>
                                                    @if($verificationResent)
                                                        <p style="color: #065f46; font-size: 0.75rem; margin: 0;">&#10003; Verification email sent — check your inbox.</p>
                                                    @else
                                                        <a href="#" wire:click.prevent="resendVerification" style="color: #064e3b; font-size: 0.78rem; font-weight: 600; text-decoration: underline;">Resend verification email</a>
                                                    @endif
                                                </div>
                                            @endif
                                            <a class="dropdown-item py-2" href="#" wire:click.prevent="$dispatch('openEditProfileModal')">Profile</a>
                                            <a class="dropdown-item py-2 text-danger" href="#" wire:click.prevent="logout">Logout</a>
                                        </div>
                                    </div>
                                @endif

                                <!-- Donate Button -->
                                <a data-scroll-nav="1" href="#make-donation" class="btn header-btn-fill">
                                    <i class="fa fa-heart mr-2"></i> Make Donation
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <div class="col-6 d-lg-none">
                        <div class="mobile_menu d-flex justify-content-end"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap');

        /* Desktop Menu Links */
        #navigation li a {
            color: #fff;
            font-weight: 500;
            font-size: 15px;
        }
        
        /* Typography Updates */
        .logo-text span:first-child,
        .header-btn-outline,
        .header-btn-fill,
        .dropdown-item.text-danger {
            font-family: 'Playfair Display', serif !important;
        }

        /* Mobile Menu Fixes */
        .slicknav_nav {
            background: #fff;
            margin-top: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .slicknav_nav a {
            color: #333 !important; /* Force dark color for mobile menu links */
            margin: 0;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .slicknav_nav a:hover {
            background: #f9fafb;
            color: #227722 !important;
        }
        
        /* Hamburger Menu Alignment */
        .slicknav_menu {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        .slicknav_btn {
            margin: 5px 0 0 0;
            background-color: transparent;
            float: right;
        }
        .slicknav_icon-bar {
            background-color: #fff !important; /* White hamburger lines */
            height: 3px;
            margin-bottom: 4px;
        }
        
        /* Remove Rounded Edges Globally for Header Elements */
        .header-area, 
        .main-header-area, 
        .slicknav_nav,
        .dropdown-menu {
            border-radius: 0px !important;
        }

        /* Button Styles */
        .header-btn-outline {
            background: transparent;
            border: 1px solid #fff;
            color: #fff;
            padding: 10px 30px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s;
            border-radius: 50px !important;
        }
        .header-btn-outline:hover {
            background: #fff;
            color: #227722;
        }
        
        .header-btn-fill {
            background-color: #fff;
            color: #227722;
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            border-radius: 50px !important;
        }
        .header-btn-fill:hover {
            background-color: #f0fdf4;
        }
    </style>
</header>
