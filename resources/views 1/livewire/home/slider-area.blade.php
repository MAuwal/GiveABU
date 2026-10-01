<div class="slider_area" style="position: relative; overflow: hidden;">
    <div class="single_slider hero-slide d-flex align-items-center" style="position: relative; overflow: hidden;">
        <!-- Background Image -->
        <div style="position: absolute; inset: 0; background-image: url('{{ asset('img/banner/ABU COMMUNITY LEGACY IMAGE.png') }}'); background-size: cover; background-position: center;"></div>

        <!-- Overlay -->
        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom right, rgba(6, 78, 59, 0.9), rgba(16, 185, 129, 0.8)); mix-blend-mode: multiply;"></div>

        <div class="container hero-container" style="position: relative; z-index: 2;">
            <div class="row">
                <div class="col-lg-8">
                    <div class="slider_text">
                        <!-- Badge -->
                        <div class="hero-badge d-inline-flex align-items-center mb-4" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 30px; padding: 8px 20px; backdrop-filter: blur(5px);">
                            <span style="width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; margin-right: 10px; flex-shrink: 0;"></span>
                            <span style="color: #fff; font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Building a Sustainable Future</span>
                        </div>

                        <!-- Heading -->
                        <h3 class="hero-heading" style="font-weight: 700; line-height: 1.1; margin-bottom: 24px; color: #fff;">
                            Invest in <span style="font-family: 'Playfair Display', serif; font-style: italic; color: #10b981;">Africa's</span><br>
                            Next Generation of<br>
                            Leaders
                        </h3>

                        <!-- Subtext -->
                        <p class="hero-subtext" style="color: rgba(255,255,255,0.9); margin-bottom: 36px; max-width: 600px; line-height: 1.6;">
                            Join us in creating sustainable impact for Ahmadu Bello University.
                            Your contribution helps fund scholarships, cutting-edge research,
                            and vital infrastructure development.
                        </p>

                        <!-- Buttons -->
                        <div class="hero-btns d-flex align-items-center" style="gap: 16px;">
                            <a href="#make-donation" class="btn hero-btn-primary" style="background: #fff; color: #064e3b; border-radius: 5px; font-weight: 600; transition: all 0.3s;">
                                Make a Donation <i class="fa fa-arrow-right ml-2"></i>
                            </a>
                            <a href="#" class="btn hero-btn-outline d-flex align-items-center justify-content-center" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); color: #fff; border-radius: 5px; font-weight: 600; transition: all 0.3s;">
                                <i class="fa fa-play mr-2" style="font-size: 12px;"></i> Watch Our Story
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="hero-stats row mt-5 pt-3">
                            <div class="hero-stat col-auto">
                                <h4 style="color: #fff; font-weight: 700; margin-bottom: 4px;">50k+</h4>
                                <span style="color: rgba(255,255,255,0.7); font-size: 14px;">Alumni</span>
                            </div>
                            <div class="hero-stat col-auto" style="border-left: 1px solid rgba(255,255,255,0.2); border-right: 1px solid rgba(255,255,255,0.2);">
                                <h4 style="color: #fff; font-weight: 700; margin-bottom: 4px;">120</h4>
                                <span style="color: rgba(255,255,255,0.7); font-size: 14px;">Research Labs</span>
                            </div>
                            <div class="hero-stat col-auto">
                                <h4 style="color: #fff; font-weight: 700; margin-bottom: 4px;">₦2B+</h4>
                                <span style="color: rgba(255,255,255,0.7); font-size: 14px;">Funds Raised</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave Divider -->
        <div style="position: absolute; bottom: -1px; left: 0; width: 100%; line-height: 0;">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
                <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z" fill="#ffffff"></path>
            </svg>
        </div>
    </div>

    <style>
    /* ── Desktop ─────────────────────────────────── */
    .hero-slide        { min-height: 850px; }
    .hero-container    { padding-top: 60px; padding-bottom: 100px; }
    .hero-heading      { font-size: 4.5rem; }
    .hero-subtext      { font-size: 1.1rem; }
    .hero-btn-primary,
    .hero-btn-outline  { padding: 15px 35px; font-size: 16px; }
    .hero-stat         { padding-left: 2rem; padding-right: 2rem; }
    .hero-stat h4      { font-size: 2rem; }

    /* ── Tablet (≤ 991px) ────────────────────────── */
    @media (max-width: 991px) {
        .hero-slide     { min-height: 700px; }
        .hero-heading   { font-size: 3.2rem; }
    }

    /* ── Mobile (≤ 767px) ────────────────────────── */
    @media (max-width: 767px) {
        .hero-slide        { min-height: auto; }
        .hero-container    { padding-top: 70px; padding-bottom: 50px; }
        .hero-badge        { margin-bottom: 20px !important; }
        .hero-badge span:last-child { font-size: 10px; letter-spacing: 0.5px; }
        .hero-heading      { font-size: 2.4rem; margin-bottom: 18px !important; }
        .hero-subtext      { font-size: 0.95rem; margin-bottom: 28px !important; max-width: 100% !important; }

        .hero-btns         { flex-direction: column !important; align-items: stretch !important; gap: 12px !important; }
        .hero-btn-primary,
        .hero-btn-outline  { padding: 13px 20px; font-size: 15px; text-align: center; width: 100%; justify-content: center; }

        .hero-stats        { margin-top: 32px !important; padding-top: 20px !important; }
        .hero-stat         { padding-left: 1rem; padding-right: 1rem; }
        .hero-stat h4      { font-size: 1.5rem; }
        .hero-stat span    { font-size: 12px; }
    }

    /* ── Small mobile (≤ 400px) ──────────────────── */
    @media (max-width: 400px) {
        .hero-heading  { font-size: 2rem; }
        .hero-stat     { padding-left: 0.6rem; padding-right: 0.6rem; }
    }
    </style>
</div>
