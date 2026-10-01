<div class="hps-section">

    {{-- ── Section Label ── --}}
    <div class="hps-label-row">
        <span class="hps-eyebrow">Our Initiatives</span>
        <h2 class="hps-heading">Featured Projects</h2>
        <p class="hps-subheading">Discover meaningful causes and be part of something greater</p>
    </div>

    {{-- ── Slider ── --}}
    @if($projects && $projects->count())
    <div class="hps-wrapper" id="hpsWrapper">

        {{-- Track (clip wrapper keeps border-radius; outer wrapper exposes nav btns) --}}
        <div class="hps-track-clip">
        <div class="hps-track" id="hpsTrack">
            @foreach($projects as $project)
            <div class="hps-slide" data-index="{{ $loop->index }}">
                {{-- Image half --}}
                <div class="hps-img-col">
                    <div class="hps-img-frame">
                        <img
                            src="{{ $project->icon_image ? $project->icon_image_url : asset('img/causes/1.png') }}"
                            alt="{{ $project->project_title }}"
                            class="hps-img"
                            loading="lazy"
                        >
                        {{-- Floating category pill --}}
                        <span class="hps-cat-pill">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true"><circle cx="5" cy="5" r="5" fill="#227722"/></svg>
                            {{ $project->category->name ?? 'General' }}
                        </span>
                    </div>
                </div>

                {{-- Content half --}}
                <div class="hps-content-col">
                    <div class="hps-content-inner">

                        <div class="hps-index-pill">
                            <span class="hps-idx-cur">{{ str_pad($loop->index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="hps-idx-sep">/</span>
                            <span class="hps-idx-tot">{{ str_pad($projects->count(), 2, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <h3 class="hps-title">{{ $project->project_title }}</h3>

                        <div class="hps-divider"></div>

                        <p class="hps-desc">{{ \Illuminate\Support\Str::limit($project->project_description, 260) }}</p>

                        <a href="{{ route('project.single', $project->id) }}" class="hps-learn-btn" aria-label="Learn more about {{ $project->project_title }}">
                            <span>Learn More</span>
                            <svg class="hps-arrow-icon" width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                                <path d="M3.75 9H14.25M9 3.75L14.25 9L9 14.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        </div>{{-- /.hps-track-clip --}}

        {{-- Nav buttons --}}
        <button class="hps-nav hps-nav-prev" id="hpsPrev" aria-label="Previous project">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button class="hps-nav hps-nav-next" id="hpsNext" aria-label="Next project">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        {{-- Dot indicators --}}
        <div class="hps-dots" id="hpsDots" aria-label="Slide indicators">
            @foreach($projects as $project)
            <button class="hps-dot{{ $loop->first ? ' active' : '' }}" data-dot="{{ $loop->index }}" aria-label="Go to project {{ $loop->index + 1 }}"></button>
            @endforeach
        </div>

    </div>
    @else
    <div class="hps-empty">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true"><rect width="48" height="48" rx="12" fill="#f3f4f6"/><path d="M16 32l6-8 5 6 4-5 7 7" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="19" cy="20" r="3" stroke="#9ca3af" stroke-width="2"/></svg>
        <p>No active projects at the moment. Check back soon!</p>
    </div>
    @endif

    {{-- See all CTA --}}
    <div class="hps-footer">
        <a href="{{ route('projects') }}" class="hps-see-all">
            Explore All Projects
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M8 3l5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>

    {{-- ═══════════════════════════ STYLES ═══════════════════════════ --}}
    <style>
        /* ── Section wrapper ── */
        .hps-section {
            background: #f0f4f0;
            padding: 80px 0 64px;
        }

        /* ── Label row ── */
        .hps-label-row {
            text-align: center;
            margin-bottom: 52px;
            padding: 0 16px;
        }
        .hps-eyebrow {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 6px 20px;
            border-radius: 100px;
            margin-bottom: 16px;
        }
        .hps-heading {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: #111827;
            margin: 0 0 14px;
            letter-spacing: -0.6px;
            line-height: 1.15;
        }
        .hps-subheading {
            font-size: 1.1rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.6;
        }

        /* ── Slider overflow container ── */
        .hps-wrapper {
            position: relative;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 80px;
        }
        /* clip only the slide track, not the nav buttons */
        .hps-track-clip {
            overflow: hidden;
            border-radius: 28px;
        }

        /* ── Track ── */
        .hps-track {
            display: flex;
            transition: transform 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: transform;
        }

        /* ─────────────────────────────────────────
           SLIDE — fixed height, two equal halves
        ───────────────────────────────────────── */
        .hps-slide {
            flex: 0 0 100%;
            /* Fixed height so image column has a concrete parent to fill */
            height: 560px;
            display: grid;
            grid-template-columns: 55% 45%;
            align-items: stretch;
            background: #fff;
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
        }

        /* ── Image column ── */
        .hps-img-col {
            position: relative;
            /* must be a block-level box with explicit height = parent */
            overflow: hidden;
            height: 100%;
        }
        .hps-img-frame {
            position: absolute;
            inset: 0;            /* fills parent absolutely */
        }
        .hps-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 9s ease;
        }
        .hps-slide:hover .hps-img {
            transform: scale(1.06);
        }
        /* subtle dark gradient over image for pill contrast */
        .hps-img-frame::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.28) 0%, transparent 60%);
            pointer-events: none;
        }
        .hps-cat-pill {
            position: absolute;
            top: 28px;
            left: 28px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            color: #111827;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: 100px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }

        /* ── Content column ── */
        .hps-content-col {
            display: flex;
            align-items: center;
            background: #fff;
            height: 100%;
            overflow: hidden;
        }
        .hps-content-inner {
            padding: 52px 56px;
            width: 100%;
        }
        .hps-index-pill {
            display: inline-flex;
            align-items: baseline;
            gap: 5px;
            margin-bottom: 28px;
        }
        .hps-idx-cur {
            font-size: 2.6rem;
            font-weight: 900;
            color: #227722;
            line-height: 1;
        }
        .hps-idx-sep {
            font-size: 1.1rem;
            color: #d1d5db;
        }
        .hps-idx-tot {
            font-size: 1rem;
            color: #9ca3af;
            font-weight: 500;
        }
        .hps-title {
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            font-weight: 800;
            color: #111827;
            margin: 0 0 20px;
            line-height: 1.2;
            letter-spacing: -0.4px;
        }
        .hps-divider {
            width: 56px;
            height: 4px;
            background: linear-gradient(90deg, #227722, #4ade80);
            border-radius: 3px;
            margin-bottom: 22px;
        }
        .hps-desc {
            font-size: 1.05rem;
            color: #4b5563;
            line-height: 1.8;
            margin: 0 0 40px;
        }

        /* ── Learn More button ── */
        .hps-learn-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #227722;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            padding: 15px 32px;
            border-radius: 14px;
            text-decoration: none;
            transition: background 0.25s ease, transform 0.2s ease, box-shadow 0.25s ease;
            box-shadow: 0 6px 18px rgba(34,119,34,0.3);
        }
        .hps-learn-btn:hover {
            background: #1a5c1a;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(34,119,34,0.35);
            color: #fff;
            text-decoration: none;
        }
        .hps-learn-btn:active { transform: translateY(0); }
        .hps-arrow-icon {
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .hps-learn-btn:hover .hps-arrow-icon { transform: translateX(4px); }

        /* ── Nav buttons ── */
        .hps-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            z-index: 10;
        }
        .hps-nav:hover {
            background: #227722;
            border-color: #227722;
            color: #fff;
            box-shadow: 0 8px 24px rgba(34,119,34,0.25);
            transform: translateY(-50%) scale(1.08);
        }
        .hps-nav-prev { left: 0; }
        .hps-nav-next { right: 0; }

        /* ── Dots ── */
        .hps-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 28px;
        }
        .hps-dot {
            width: 8px;
            height: 8px;
            border-radius: 100px;
            background: #d1d5db;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }
        .hps-dot.active {
            width: 28px;
            background: #227722;
        }

        /* ── Empty state ── */
        .hps-empty {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        .hps-empty p { font-size: 1rem; margin: 0; }

        /* ── Footer ── */
        .hps-footer {
            text-align: center;
            margin-top: 40px;
        }
        .hps-see-all {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #227722;
            font-weight: 700;
            font-size: 0.95rem;
            text-decoration: none;
            border: 2px solid #227722;
            padding: 12px 32px;
            border-radius: 100px;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
        }
        .hps-see-all:hover {
            background: #227722;
            color: #fff;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(34,119,34,0.2);
        }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            .hps-wrapper { padding: 0 20px; }
            /* Stack: image on top, content below */
            .hps-slide {
                grid-template-columns: 1fr;
                grid-template-rows: 300px auto;
                height: auto;
            }
            .hps-img-col { height: 300px; }
            .hps-img-frame { position: absolute; inset: 0; }
            .hps-content-inner { padding: 36px 32px; }
            .hps-nav-prev { left: -4px; }
            .hps-nav-next { right: -4px; }
            .hps-title { font-size: 1.55rem; }
        }
        @media (max-width: 575px) {
            .hps-section { padding: 52px 0 44px; }
            .hps-slide { grid-template-rows: 240px auto; }
            .hps-img-col { height: 240px; }
            .hps-content-inner { padding: 28px 22px 32px; }
            .hps-nav { width: 44px; height: 44px; }
            .hps-title { font-size: 1.35rem; }
            .hps-desc { font-size: 0.97rem; }
        }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            .hps-track { transition: none; }
            .hps-img { transition: none; }
        }
    </style>

    {{-- ═══════════════════════════ SCRIPT ═══════════════════════════ --}}
    <script>
    (function () {
        const track    = document.getElementById('hpsTrack');
        const btnPrev  = document.getElementById('hpsPrev');
        const btnNext  = document.getElementById('hpsNext');
        const dotsWrap = document.getElementById('hpsDots');
        if (!track) return;

        const slides = track.querySelectorAll('.hps-slide');
        const dots   = dotsWrap ? dotsWrap.querySelectorAll('.hps-dot') : [];
        const total  = slides.length;
        let   idx    = 0;
        let   timer  = null;
        const AUTO_INTERVAL = 5500;

        function goTo(i) {
            idx = ((i % total) + total) % total;
            track.style.transform = 'translateX(-' + (idx * 100) + '%)';
            dots.forEach((d, n) => d.classList.toggle('active', n === idx));
        }

        function next() { goTo(idx + 1); }
        function prev() { goTo(idx - 1); }

        function startAuto() {
            stopAuto();
            timer = setInterval(next, AUTO_INTERVAL);
        }
        function stopAuto() {
            if (timer) { clearInterval(timer); timer = null; }
        }
        function restartAuto() { stopAuto(); startAuto(); }

        if (btnNext) btnNext.addEventListener('click', function () { next(); restartAuto(); });
        if (btnPrev) btnPrev.addEventListener('click', function () { prev(); restartAuto(); });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(dot.dataset.dot));
                restartAuto();
            });
        });

        var wrapper = document.getElementById('hpsWrapper');
        if (wrapper) {
            wrapper.addEventListener('mouseenter', stopAuto);
            wrapper.addEventListener('mouseleave', startAuto);
        }

        goTo(0);
        if (total > 1) startAuto();
    })();
    </script>

</div>{{-- /.hps-section --}}

    <!-- Donation Modal -->
    @if($showModal && $selectedProject)
    <div class="modal fade show d-block" id="donationModal" tabindex="-1" role="dialog" style="background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
            <div class="modal-content" style="border-radius:24px;border:none;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,0.25);">

                <!-- Green header band -->
                <div style="background:linear-gradient(135deg,#227722 0%,#1a5c1a 100%);padding:22px 24px 32px;position:relative;text-align:center;">
                    <button type="button" wire:click="closeModal" style="position:absolute;top:12px;right:14px;background:rgba(255,255,255,0.15);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.28)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M1 1l10 10M11 1L1 11" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.18);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="rgba(255,255,255,0.35)" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h5 style="color:#fff;font-size:1.15rem;font-weight:700;margin:0 0 3px;font-family:'Playfair Display',serif;">Donate to {{ $selectedProject->project_title }}</h5>
                    <p style="color:rgba(255,255,255,0.72);font-size:0.78rem;margin:0;">Your contribution makes a difference</p>
                    <div style="position:absolute;bottom:-1px;left:0;right:0;line-height:0;">
                        <svg viewBox="0 0 400 16" preserveAspectRatio="none" style="display:block;width:100%;height:16px;"><path d="M0,16 C100,0 300,0 400,16 L400,16 L0,16 Z" fill="#fff"/></svg>
                    </div>
                </div>

                <!-- Form body -->
                <div style="padding:24px 28px 28px;background:#fff;">
                    <form wire:submit.prevent="donate">

                        <!-- Email -->
                        <div style="margin-bottom:14px;">
                            <label style="display:block;font-size:0.71rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Email Address <span style="color:#ef4444;">*</span></label>
                            <div class="hps-don-iw">
                                <span class="hps-don-px">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </span>
                                <input type="email" wire:model="email" class="hps-don-in" placeholder="you@example.com" required>
                            </div>
                            @error('email') <span style="color:#ef4444;font-size:0.74rem;margin-top:3px;display:block;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Amount -->
                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:0.71rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Donation Amount <span style="color:#ef4444;">*</span></label>
                            <div class="hps-don-iw">
                                <span class="hps-don-px hps-don-px-cur">₦</span>
                                <input type="number" min="100" step="1" wire:model.live="customAmount" class="hps-don-in hps-don-in-lg" placeholder="Enter amount">
                            </div>
                            @error('amount') <span style="color:#ef4444;font-size:0.74rem;margin-top:3px;display:block;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Payment method -->
                        @if($paymentReference)
                            <button type="button" wire:click="verifyPayment('{{ $paymentReference }}')" style="width:100%;padding:14px;background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;font-weight:700;border:none;border-radius:14px;font-size:0.95rem;cursor:pointer;box-shadow:0 8px 20px rgba(249,115,22,0.25);display:flex;align-items:center;justify-content:center;gap:8px;">
                                Verify Payment
                                <span wire:loading style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:hps-don-spin 0.8s linear infinite;"></span>
                            </button>
                            <p style="text-align:center;margin-top:8px;font-size:0.73rem;color:#9ca3af;">Click this if the payment window closed but this modal didn't.</p>
                        @else
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="flex:1;height:1px;background:#f3f4f6;"></div>
                                <span style="font-size:0.62rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1.3px;white-space:nowrap;">Choose payment method</span>
                                <div style="flex:1;height:1px;background:#f3f4f6;"></div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                                {{-- Paystack - Commented out (testing mode only)
                                <button type="submit" wire:loading.attr="disabled" wire:target="donate" class="hps-gw-card hps-gw-paystack">
                                    <span wire:loading.remove wire:target="donate" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('paystack.png') }}" alt="Paystack" class="hps-gw-img">
                                        </div>
                                    </span>
                                    <span wire:loading wire:target="donate" class="hps-gw-loading">
                                        <svg class="hps-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/></svg>
                                        Processing…
                                    </span>
                                </button>
                                --}}

                                <!-- Squad -->
                                <button type="button" id="proj-squad-pay-btn" wire:click="payWithSquad" wire:loading.attr="disabled" wire:target="payWithSquad" class="hps-gw-card hps-gw-squad">
                                    <span id="proj-squad-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('GTCO-Squad-Hackathon-Program.jpg') }}" alt="Pay via Squad" class="hps-gw-img">
                                        </div>
                                    </span>
                                    <span id="proj-squad-btn-loading" class="hps-gw-loading" style="display:none;">
                                        <svg class="hps-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/></svg>
                                        Redirecting…
                                    </span>
                                </button>

                                <!-- Interswitch -->
                                <button type="button" id="proj-isw-pay-btn" wire:click="payWithInterswitch" wire:loading.attr="disabled" wire:target="payWithInterswitch" class="hps-gw-card hps-gw-interswitch">
                                    <span id="proj-isw-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('interswitch2.jpg') }}" alt="Pay via Interswitch" class="hps-gw-img">
                                        </div>
                                    </span>
                                    <span id="proj-isw-btn-loading" class="hps-gw-loading" style="display:none;">
                                        <svg class="hps-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/></svg>
                                        Redirecting…
                                    </span>
                                </button>
                            </div>

                            <div style="display:flex;align-items:center;justify-content:center;gap:5px;padding-top:10px;border-top:1px solid #f3f4f6;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span style="font-size:0.66rem;color:#9ca3af;font-weight:500;">256-bit SSL · Squad · Interswitch</span>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <style>
            .hps-don-iw {
                display:flex;align-items:center;
                background:#f9fafb;border:1.5px solid #e5e7eb;
                border-radius:11px;overflow:hidden;
                transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
            }
            .hps-don-iw:focus-within { border-color:#227722;background:#fff;box-shadow:0 0 0 3px rgba(34,119,34,0.08); }
            .hps-don-px { padding:0 11px;display:flex;align-items:center;flex-shrink:0; }
            .hps-don-px-cur { font-weight:800;color:#227722;font-size:1rem; }
            .hps-don-in {
                border:none;outline:none;background:transparent;
                height:47px;padding:0 10px 0 2px;
                font-size:0.9rem;color:#1f2937;font-weight:500;flex:1;min-width:0;
            }
            .hps-don-in-lg { font-weight:700;font-size:1.02rem; }

            .hps-gw-card {
                background:#fff;border:2px solid #e5e7eb;border-radius:13px;
                padding:14px 10px;cursor:pointer;
                transition:all 0.25s cubic-bezier(0.4,0,0.2,1);
                display:flex;flex-direction:column;align-items:center;justify-content:center;
                min-height:74px;position:relative;overflow:hidden;
            }
            .hps-gw-card::after { content:'';position:absolute;inset:0;border-radius:11px;opacity:0;transition:opacity 0.25s ease; }
            .hps-gw-card:hover:not([disabled]) { transform:translateY(-4px) scale(1.02);box-shadow:0 12px 28px rgba(0,0,0,0.1); }
            .hps-gw-card:hover:not([disabled])::after { opacity:1; }
            .hps-gw-card:active:not([disabled]) { transform:translateY(-1px) scale(0.98);box-shadow:0 4px 12px rgba(0,0,0,0.08);transition-duration:0.1s; }
            .hps-gw-card[disabled] { opacity:0.5;cursor:not-allowed;filter:grayscale(0.4); }
            .hps-gw-img { height:48px;width:auto;max-width:140px;object-fit:contain;border-radius:6px;transition:transform 0.25s cubic-bezier(0.4,0,0.2,1),filter 0.25s ease; }
            .hps-gw-card:hover:not([disabled]) .hps-gw-img { transform:scale(1.06);filter:brightness(1.05); }
            .hps-gw-card:active:not([disabled]) .hps-gw-img { transform:scale(0.96); }
            .hps-gw-squad:hover:not([disabled]) { border-color:#00b8a9;box-shadow:0 0 0 3px rgba(0,184,169,0.12),0 12px 28px rgba(0,184,169,0.1); }
            .hps-gw-squad::after { background:linear-gradient(135deg,rgba(0,184,169,0.04),rgba(0,184,169,0.01)); }
            .hps-gw-interswitch:hover:not([disabled]) { border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,0.12),0 12px 28px rgba(37,99,235,0.1); }
            .hps-gw-interswitch::after { background:linear-gradient(135deg,rgba(37,99,235,0.04),rgba(37,99,235,0.01)); }

            .hps-gw-loading { display:flex;align-items:center;gap:5px;font-size:0.7rem;color:#6b7280;font-weight:600; }
            @keyframes hps-don-spin { to { transform:rotate(360deg); } }
            .hps-gw-spin { animation:hps-don-spin 0.8s linear infinite; }
        </style>
    </div>
    @endif

    <!-- Detailed Project View Modal -->
    @if($showImageGallery && $galleryProject)
    @php
        $galleryPhotos = [];
        // Add main project image
        $galleryPhotos[] = [
            'url' => $galleryProject->icon_image ? $galleryProject->icon_image_url : asset('img/causes/1.png'),
            'description' => $galleryProject->project_description,
            'title' => $galleryProject->project_title
        ];
        // Add other photos
        foreach($galleryProject->photos as $photo) {
            $galleryPhotos[] = [
                'url' => $photo->image_url,
                'description' => $photo->description ?? '',
                'title' => $photo->title ?? ''
            ];
        }
    @endphp
    <div class="project-details-modal" style="display: block;"
         x-data="{
            activeIndex: 0,
            showDesc: true,
            photos: {{ json_encode($galleryPhotos) }}
         }">
        
        <div class="project-details-overlay" wire:click="closeImageGallery"></div>
        
        <div class="project-details-container">
            <!-- Header -->
            <div class="project-details-header">
                <div class="d-flex align-items-center">
                    <div class="project-icon mr-3">
                        <img src="{{ $galleryProject->icon_image ? $galleryProject->icon_image_url : asset('img/causes/1.png') }}" alt="Icon">
                    </div>
                    <div>
                        <h3 class="mb-0" style="font-weight: 800; font-size: 1.4rem; color: #1f2937; letter-spacing: -0.5px;">{{ $galleryProject->project_title }}</h3>
                        <span class="text-muted" style="font-size: 0.85rem; font-weight: 500;">
                            <i class="fa fa-camera mr-1 text-success"></i> {{ count($galleryProject->photos) + 1 }} Photos
                        </span>
                    </div>
                </div>
                <button type="button" class="close-btn" wire:click="closeImageGallery">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="project-details-body">
                <div class="row h-100">
                    <!-- Left Column: Image Viewer -->
                    <div class="col-lg-8 mb-4 mb-lg-0 d-flex flex-column">
                        <!-- Main Image -->
                        <div class="main-image-area mb-3 position-relative shadow-sm">
                            <template x-if="photos.length > 0">
                                <img :src="photos[activeIndex].url" class="main-image" :alt="photos[activeIndex].title">
                            </template>
                            
                            <!-- Image Counter -->
                            <div class="image-counter-badge">
                                <span x-text="(activeIndex + 1) + ' / ' + photos.length"></span>
                            </div>

                            <!-- Navigation Arrows -->
                            <button class="nav-arrow prev" @click="activeIndex = (activeIndex > 0) ? activeIndex - 1 : photos.length - 1">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button class="nav-arrow next" @click="activeIndex = (activeIndex < photos.length - 1) ? activeIndex + 1 : 0">
                                <i class="fa fa-chevron-right"></i>
                            </button>

                            <!-- Description Overlay -->
                            <div class="image-desc-overlay" x-show="showDesc && (photos[activeIndex].title || photos[activeIndex].description)" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform translate-y-4"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform translate-y-4">
                                <button @click="showDesc = false" class="close-desc-btn"><i class="fa fa-times"></i></button>
                                <h5 x-text="photos[activeIndex].title || 'Photo Details'"></h5>
                                <p x-text="photos[activeIndex].description"></p>
                            </div>
                        </div>

                        <!-- Thumbnails -->
                        <div class="thumbnails-strip">
                            <template x-for="(photo, index) in photos" :key="index">
                                <div class="thumbnail-item" 
                                     :class="{'active': activeIndex === index}"
                                     @click="activeIndex = index; showDesc = true">
                                    <img :src="photo.url" alt="Thumbnail">
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right Column: Info -->
                    <div class="col-lg-4">
                        <div class="project-info-sidebar h-100">
                            <!-- Funding Status -->
                            <div class="info-card mb-4 bg-white border-0 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="card-title mb-0 border-0 p-0">Funding Status</h5>
                                    <span class="badge badge-soft-success px-3 py-2 rounded-pill">Active</span>
                                </div>
                                
                                <div class="progress-wrapper mb-4">
                                    @php
                                        $raised = floatval($galleryProject->raised ?? 0);
                                        $target = floatval($galleryProject->target ?? 0);
                                        $percentage = ($target > 0) ? round(($raised / $target) * 100, 1) : 0;
                                    @endphp
                                    
                                    <div class="d-flex justify-content-between mb-2 align-items-end">
                                        <div>
                                            <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Raised</span>
                                            <span class="h4 mb-0 text-dark font-weight-bold">₦{{ number_format($raised) }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Goal</span>
                                            <span class="h6 mb-0 text-muted">₦{{ number_format($target) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="progress mb-2" style="height: 8px; border-radius: 4px; background-color: #f3f4f6;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ min($percentage, 100) }}%; background: linear-gradient(90deg, #227722, #1a5c1a); border-radius: 4px;">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-2 small">
                                        <span class="text-success font-weight-bold">{{ $percentage }}% Funded</span>
                                        <span class="text-muted">₦{{ number_format(max(0, $target - $raised)) }} to go</span>
                                    </div>
                                </div>

                                <button wire:click="openDonationModal({{ $galleryProject->id }})" 
                                        class="btn btn-block py-3 font-weight-bold text-white shadow-sm hover-lift"
                                        style="background: #227722; border-radius: 12px; transition: all 0.3s;">
                                    Donate Now <i class="fa fa-heart ml-2 text-white-50"></i>
                                </button>
                            </div>

                            <!-- About Project -->
                            <div class="info-card bg-white border-0 shadow-sm flex-fill">
                                <h5 class="card-title mb-3 border-0 p-0">About Project</h5>
                                <div class="description-text custom-scrollbar" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                                    {{ $galleryProject->project_description }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .project-details-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-details-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .project-details-container {
            position: relative;
            width: 95%;
            max-width: 1200px;
            height: 90vh;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 10001;
            animation: modalSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(0,0,0,0.05);
        }

        @keyframes modalSlideUp {
            from { transform: translateY(40px) scale(0.95); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        .project-details-header {
            padding: 20px 30px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .project-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f3f4f6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .project-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .close-btn {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #6b7280;
        }

        .close-btn:hover {
            background: #f3f4f6;
            color: #1f2937;
            transform: rotate(90deg);
        }

        .project-details-body {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: #fcfcfc;
        }

        /* Image Viewer Styles */
        .main-image-area {
            width: 100%;
            height: 500px;
            background: #f8f9fa;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #f3f4f6;
        }

        .main-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(0,0,0,0.05);
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            opacity: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .main-image-area:hover .nav-arrow {
            opacity: 1;
        }

        .nav-arrow.prev { left: 20px; }
        .nav-arrow.next { right: 20px; }
        .nav-arrow:hover { background: #fff; transform: translateY(-50%) scale(1.1); }

        .image-counter-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.9);
            color: #1f2937;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .image-desc-overlay {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .close-desc-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            color: #9ca3af;
            cursor: pointer;
        }

        .image-desc-overlay h5 {
            margin-bottom: 6px;
            font-weight: 700;
            color: #111827;
            font-size: 1.1rem;
        }

        .image-desc-overlay p {
            margin-bottom: 0;
            font-size: 0.95rem;
            color: #4b5563;
            line-height: 1.5;
        }

        .thumbnails-strip {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding: 4px;
        }

        .thumbnail-item {
            width: 70px;
            height: 70px;
            flex-shrink: 0;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
            opacity: 0.6;
            background: #f3f4f6;
        }

        .thumbnail-item:hover {
            opacity: 1;
            transform: translateY(-2px);
        }

        .thumbnail-item.active {
            border-color: #227722;
            opacity: 1;
            box-shadow: 0 4px 12px rgba(34, 119, 34, 0.15);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Sidebar Styles */
        .info-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f3f4f6;
            margin-bottom: 20px;
        }

        .card-title {
            font-weight: 800;
            color: #111827;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
        }

        .badge-soft-success {
            background-color: #ecfdf5;
            color: #227722;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .description-text {
            color: #4b5563;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(34, 119, 34, 0.2);
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        @media (max-width: 991px) {
            .project-details-container {
                height: 100%;
                width: 100%;
                max-width: 100%;
                border-radius: 0;
            }
            .main-image-area {
                height: 300px;
            }
        }
    </style>
    @endif

    <!-- Payment Integration Scripts -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('initiate-paystack', (data) => {
                const paymentData = Array.isArray(data) ? data[0] : data;

                let handler = PaystackPop.setup({
                    key: paymentData.key,
                    email: paymentData.email,
                    amount: paymentData.amount,
                    currency: paymentData.currency,
                    ref: paymentData.ref,
                    metadata: paymentData.metadata,
                    onClose: function(){
                        console.log('Payment window closed.');
                    },
                    callback: function(response){
                        let component = Livewire.find('{{ $this->getId() }}');
                        if (component) {
                            component.call('verifyPayment', response.reference);
                        } else {
                            Livewire.dispatch('project-payment-success', { reference: response.reference });
                        }
                    }
                });

                handler.openIframe();
            });

            // ── Squad redirect ─────────────────────────────────────────────
            Livewire.on('initiate-squad', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('proj-squad-pay-btn');
                const btnText = document.getElementById('proj-squad-btn-text');
                const btnLoad = document.getElementById('proj-squad-btn-loading');

                const showLoading = () => {
                    if (btn)     btn.disabled = true;
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoad) btnLoad.style.display = 'flex';
                };
                const hideLoading = () => {
                    if (btn)     btn.disabled = false;
                    if (btnText) btnText.style.display = 'flex';
                    if (btnLoad) btnLoad.style.display = 'none';
                };

                showLoading();

                try {
                    const res = await fetch('/api/squad/pay', {
                        method:  'POST',
                        headers: {
                            'Content-Type':     'application/json',
                            'Accept':           'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            amount:        p.amount,
                            email:         p.email,
                            customer_name: p.customer_name || '',
                            project_id:    p.project_id || null,
                        }),
                    });

                    const result = await res.json();

                    if (result.checkout_url) {
                        window.location.href = result.checkout_url;
                    } else {
                        alert(result.message || 'Unable to initiate Squad payment. Please try again.');
                        hideLoading();
                    }
                } catch (err) {
                    console.error('Squad payment error:', err);
                    alert('A network error occurred. Please check your connection and try again.');
                    hideLoading();
                }
            });

            // ── Interswitch redirect ─────────────────────────────────────────
            Livewire.on('initiate-interswitch', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('proj-isw-pay-btn');
                const btnText = document.getElementById('proj-isw-btn-text');
                const btnLoad = document.getElementById('proj-isw-btn-loading');

                const showLoading = () => {
                    if (btn)     btn.disabled = true;
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoad) btnLoad.style.display = 'flex';
                };
                const hideLoading = () => {
                    if (btn)     btn.disabled = false;
                    if (btnText) btnText.style.display = 'flex';
                    if (btnLoad) btnLoad.style.display = 'none';
                };

                showLoading();

                try {
                    const res = await fetch('/api/interswitch/pay', {
                        method: 'POST',
                        headers: {
                            'Content-Type':'application/json',
                            'Accept':'application/json',
                            'X-Requested-With':'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            amount:        p.amount,
                            email:         p.email,
                            customer_name: p.customer_name || '',
                            project_id:    p.project_id || null,
                            callback_url:  window.location.origin + '/api/interswitch/redirect'
                        }),
                    });

                    const result = await res.json();
                    if (!res.ok) throw new Error(result.message || 'Unable to initialize Interswitch payment.');
                    if (!result.checkout_url || !result.payload) throw new Error('Interswitch did not return a valid payment URL.');

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = result.checkout_url;
                    form.style.display = 'none';
                    const allowedFields = ['merchant_code','pay_item_id','txn_ref','amount','currency','site_redirect_url','cust_name','cust_email','cust_id','pay_item_name','mode'];
                    Object.entries(result.payload).forEach(([key, value]) => {
                        if (allowedFields.includes(key) && value !== undefined && value !== null) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }
                    });
                    document.body.appendChild(form);
                    form.submit();
                } catch (err) {
                    console.error('Interswitch payment error:', err);
                    alert(err.message || 'Failed to initiate Interswitch payment.');
                    hideLoading();
                }
            });

            Livewire.on('close-donation-modal', () => {
                const modal = document.getElementById('donationModal');
                if (modal) {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    const backdrops = document.getElementsByClassName('modal-backdrop');
                    while(backdrops.length > 0){
                        backdrops[0].parentNode.removeChild(backdrops[0]);
                    }
                }
            });

            // Toast notification handler
            Livewire.on('show-toast', (data) => {
                const toastData = Array.isArray(data) ? data[0] : data;
                
                const toast = document.createElement('div');
                toast.className = `alert alert-${toastData.type} toast-notification`;
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    min-width: 300px;
                    padding: 15px 20px;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    animation: slideIn 0.3s ease-out;
                `;
                toast.innerHTML = `
                    <strong>${toastData.type === 'success' ? '✓' : '✗'}</strong> ${toastData.message}
                `;
                
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.animation = 'slideOut 0.3s ease-in';
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            });
        });
    </script>
</div>
