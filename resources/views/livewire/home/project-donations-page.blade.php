<div class="popular_causes_area" style="background: #f8fafc; padding: 60px 0 80px; width: 100%;">
    <div class="container">
        
        <!-- Header Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 col-md-10 text-center">
                <h2 style="color: #0f172a; font-size: 2.5rem; font-weight: 800; margin-bottom: 0.8rem; font-family: 'Playfair Display', serif;">
                    Priority Projects
                </h2>
                <p class="text-muted" style="font-size: 1.1rem; line-height: 1.6; max-width: 600px; margin: 0 auto;">
                    Explore our high-impact university projects and support initiatives shaping the future of learning and innovation.
                </p>
            </div>
        </div>

        <!-- Vertical Stack of Project Cards -->
        <div class="project-cards-stack" style="display: flex; flex-direction: column; gap: 36px; width: 100%;">
            @forelse($projects as $index => $project)
                @php
                    $raised = floatval($project->raised ?? 0);
                    $target = floatval($project->target ?? 0);
                    $percentage = ($target > 0) ? min(round(($raised / $target) * 100, 1), 100) : 0;
                    $isRightImage = ($index % 2 !== 0); // index 0 (1st): Image Left, index 1 (2nd): Image Right
                    
                    $clipPath = !$isRightImage 
                        ? 'polygon(0 0, 100% 0, 85% 100%, 0 100%)' 
                        : 'polygon(15% 0, 100% 0, 100% 100%, 0 100%)';
                @endphp

                <div class="project-split-card" style="background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0; width: 100%;">
                    <div class="project-card-body" style="display: flex; flex-direction: {{ $isRightImage ? 'row-reverse' : 'row' }}; align-items: stretch; min-height: 380px; width: 100%;">
                        
                        <!-- IMAGE COLUMN (50%) -->
                        <div class="project-card-img-wrap" style="flex: 0 0 50%; width: 50%; max-width: 50%; position: relative; overflow: hidden; background: #0f172a; min-height: 380px; clip-path: {{ $clipPath }}; -webkit-clip-path: {{ $clipPath }}; z-index: 2;">
                            <img src="{{ $project->icon_image ? $project->icon_image_url : asset('img/causes/1.png') }}" 
                                 alt="{{ $project->project_title }}" 
                                 onerror="this.onerror=null;this.src='{{ asset('img/causes/1.png') }}';"
                                 style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        </div>

                        <!-- CONTENT COLUMN (50%) -->
                        <div class="project-card-content" style="flex: 0 0 50%; width: 50%; max-width: 50%; display: flex; flex-direction: column; justify-content: center; padding: 40px 48px; background: #ffffff; z-index: 1;">
                            <h3 style="font-size: 1.55rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; line-height: 1.3; font-family: 'Playfair Display', serif;">
                                <a href="{{ route('project.single', $project->id) }}" style="color: #0f172a; text-decoration: none;">
                                    {{ $project->project_title }}
                                </a>
                            </h3>

                            <p style="color: #475569; font-size: 0.98rem; line-height: 1.65; margin-bottom: 24px;">
                                {{ Str::limit($project->project_description, 180) }}
                            </p>

                            <!-- Progress Bar with Mint Green track and Percentage Pill -->
                            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px;">
                                <div style="flex: 1; height: 10px; background: #e2e8f0; border-radius: 6px; overflow: hidden;">
                                    <div style="height: 100%; background: #10b981; border-radius: 6px; width: {{ $percentage }}%;"></div>
                                </div>
                                <span style="background: #064e3b; color: #ffffff; font-size: 0.78rem; font-weight: 800; padding: 4px 10px; border-radius: 14px; flex-shrink: 0;">{{ intval($percentage) }}%</span>
                            </div>

                            <!-- Action Buttons Stack -->
                            <div style="display: flex; flex-direction: column; gap: 12px; width: 100%;">
                                <button wire:click="openDonationModal({{ $project->id }})" style="background: #091e42; color: #ffffff; font-weight: 700; font-size: 1rem; padding: 14px 24px; border-radius: 8px; border: none; width: 100%; text-align: center; cursor: pointer; box-shadow: 0 4px 12px rgba(9, 30, 66, 0.2);">
                                    Donate Now
                                </button>

                                <a href="{{ route('project.single', $project->id) }}" style="background: #ffffff; color: #475569; font-weight: 600; font-size: 0.98rem; padding: 13px 24px; border-radius: 8px; border: 1px solid #cbd5e1; width: 100%; text-align: center; text-decoration: none; display: block;">
                                    Read More
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="p-5 bg-white rounded shadow-sm" style="max-width: 500px; margin: 0 auto; border-radius: 16px;">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0" style="font-size: 1.1rem;">No active projects at the moment. Check back soon!</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Slick Modern Pagination -->
        @if($projects->hasPages())
            <div class="projects-pagination-container" style="margin-top: 50px; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
                <nav style="display: inline-flex; align-items: center; background: #ffffff; padding: 10px 20px; border-radius: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; gap: 8px;">
                    
                    {{-- Previous Page Link --}}
                    @if ($projects->onFirstPage())
                        <span style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f1f5f9; color: #cbd5e1; cursor: not-allowed; font-size: 0.9rem; user-select: none;">
                            <i class="fa fa-chevron-left"></i>
                        </span>
                    @else
                        <button wire:click="previousPage" wire:loading.attr="disabled" type="button" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f8fafc; color: #091e42; border: 1px solid #cbd5e1; cursor: pointer; transition: all 0.25s ease; font-size: 0.9rem;" onmouseover="this.style.background='#091e42'; this.style.color='#fff'; this.style.borderColor='#091e42';" onmouseout="this.style.background='#f8fafc'; this.style.color='#091e42'; this.style.borderColor='#cbd5e1';">
                            <i class="fa fa-chevron-left"></i>
                        </button>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach (range(1, $projects->lastPage()) as $page)
                        @if ($page == $projects->currentPage())
                            <span style="min-width: 42px; height: 42px; padding: 0 14px; display: flex; align-items: center; justify-content: center; border-radius: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; font-weight: 800; font-size: 0.95rem; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); user-select: none;">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" type="button" style="min-width: 42px; height: 42px; padding: 0 14px; display: flex; align-items: center; justify-content: center; border-radius: 50px; background: transparent; color: #475569; border: none; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($projects->hasMorePages())
                        <button wire:click="nextPage" wire:loading.attr="disabled" type="button" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f8fafc; color: #091e42; border: 1px solid #cbd5e1; cursor: pointer; transition: all 0.25s ease; font-size: 0.9rem;" onmouseover="this.style.background='#091e42'; this.style.color='#fff'; this.style.borderColor='#091e42';" onmouseout="this.style.background='#f8fafc'; this.style.color='#091e42'; this.style.borderColor='#cbd5e1';">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    @else
                        <span style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f1f5f9; color: #cbd5e1; cursor: not-allowed; font-size: 0.9rem; user-select: none;">
                            <i class="fa fa-chevron-right"></i>
                        </span>
                    @endif

                </nav>
                <div style="margin-top: 12px; font-size: 0.85rem; color: #64748b; font-weight: 500;">
                    Showing page <span style="color: #0f172a; font-weight: 700;">{{ $projects->currentPage() }}</span> of <span style="color: #0f172a; font-weight: 700;">{{ $projects->lastPage() }}</span> (Total {{ $projects->total() }} Projects)
                </div>
            </div>
        @endif

    </div>
    <!-- End container, popular_causes_area root div stays open to encompass modals, style, and script -->
    @if($showModal && $selectedProject)
    <div class="modal fade show d-block" id="donationModal" tabindex="-1" role="dialog" style="background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
            <div class="modal-content" style="border-radius:24px;border:none;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,0.25);">

                <!-- Green header -->
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
                            <div class="pdp-don-iw">
                                <span class="pdp-don-px"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
                                <input type="email" wire:model="email" class="pdp-don-in" placeholder="you@example.com" required>
                            </div>
                            @error('email') <span style="color:#ef4444;font-size:0.74rem;margin-top:3px;display:block;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Amount -->
                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:0.71rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Donation Amount <span style="color:#ef4444;">*</span></label>
                            <div class="pdp-don-iw">
                                <span class="pdp-don-px pdp-don-cur">₦</span>
                                <input type="number" min="100" step="1" wire:model.live="customAmount" class="pdp-don-in pdp-don-in-lg" placeholder="Enter amount">
                            </div>
                            @error('amount') <span style="color:#ef4444;font-size:0.74rem;margin-top:3px;display:block;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Payment method -->
                        @if($paymentReference)
                            <button type="button" wire:click="verifyPayment('{{ $paymentReference }}')" style="width:100%;padding:14px;background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;font-weight:700;border:none;border-radius:14px;font-size:0.95rem;cursor:pointer;box-shadow:0 8px 20px rgba(249,115,22,0.25);display:flex;align-items:center;justify-content:center;gap:8px;">
                                Verify Payment
                                <span wire:loading style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:pdp-spin 0.8s linear infinite;"></span>
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
                                <button type="submit" wire:loading.attr="disabled" wire:target="donate" class="pdp-gw-card pdp-gw-paystack">
                                    <span wire:loading.remove wire:target="donate" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('paystack.png') }}" alt="Paystack" class="pdp-gw-img">
                                        </div>
                                    </span>
                                    <span wire:loading wire:target="donate" class="pdp-gw-loading">
                                        <svg class="pdp-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/></svg>
                                        Processing…
                                    </span>
                                </button>
                                --}}

                                <!-- Squad -->
                                <button type="button" id="proj-page-squad-pay-btn" wire:click="payWithSquad" wire:loading.attr="disabled" wire:target="payWithSquad" class="pdp-gw-card pdp-gw-squad">
                                    <span id="proj-page-squad-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('GTCO-Squad-Hackathon-Program.jpg') }}" alt="Pay via Squad" class="pdp-gw-img">
                                        </div>
                                    </span>
                                    <span id="proj-page-squad-btn-loading" class="pdp-gw-loading" style="display:none;">
                                        <svg class="pdp-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/></svg>
                                        Redirecting…
                                    </span>
                                </button>

                                <!-- Interswitch -->
                                <button type="button" id="proj-page-isw-pay-btn" wire:click="payWithInterswitch" wire:loading.attr="disabled" wire:target="payWithInterswitch" class="pdp-gw-card pdp-gw-interswitch">
                                    <span id="proj-page-isw-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('interswitch2.jpg') }}" alt="Pay via Interswitch" class="pdp-gw-img">
                                        </div>
                                    </span>
                                    <span id="proj-page-isw-btn-loading" class="pdp-gw-loading" style="display:none;">
                                        <svg class="pdp-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/></svg>
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
            .pdp-don-iw { display:flex;align-items:center;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:11px;overflow:hidden;transition:border-color 0.2s,box-shadow 0.2s,background 0.2s; }
            .pdp-don-iw:focus-within { border-color:#227722;background:#fff;box-shadow:0 0 0 3px rgba(34,119,34,0.08); }
            .pdp-don-px { padding:0 11px;display:flex;align-items:center;flex-shrink:0; }
            .pdp-don-cur { font-weight:800;color:#227722;font-size:1rem; }
            .pdp-don-in { border:none;outline:none;background:transparent;height:47px;padding:0 10px 0 2px;font-size:0.9rem;color:#1f2937;font-weight:500;flex:1;min-width:0; }
            .pdp-don-in-lg { font-weight:700;font-size:1.02rem; }
            .pdp-gw-card { background:#fff;border:2px solid #e5e7eb;border-radius:13px;padding:14px 10px;cursor:pointer;transition:all 0.25s cubic-bezier(0.4,0,0.2,1);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:74px;position:relative;overflow:hidden; }
            .pdp-gw-card::after { content:'';position:absolute;inset:0;border-radius:11px;opacity:0;transition:opacity 0.25s ease; }
            .pdp-gw-card:hover:not([disabled]) { transform:translateY(-4px) scale(1.02);box-shadow:0 12px 28px rgba(0,0,0,0.1); }
            .pdp-gw-card:hover:not([disabled])::after { opacity:1; }
            .pdp-gw-card:active:not([disabled]) { transform:translateY(-1px) scale(0.98);box-shadow:0 4px 12px rgba(0,0,0,0.08);transition-duration:0.1s; }
            .pdp-gw-card[disabled] { opacity:0.5;cursor:not-allowed;filter:grayscale(0.4); }
            .pdp-gw-img { height:48px;width:auto;max-width:140px;object-fit:contain;border-radius:6px;transition:transform 0.25s cubic-bezier(0.4,0,0.2,1),filter 0.25s ease; }
            .pdp-gw-card:hover:not([disabled]) .pdp-gw-img { transform:scale(1.06);filter:brightness(1.05); }
            .pdp-gw-card:active:not([disabled]) .pdp-gw-img { transform:scale(0.96); }
            .pdp-gw-squad:hover:not([disabled]) { border-color:#00b8a9;box-shadow:0 0 0 3px rgba(0,184,169,0.12),0 12px 28px rgba(0,184,169,0.1); }
            .pdp-gw-squad::after { background:linear-gradient(135deg,rgba(0,184,169,0.04),rgba(0,184,169,0.01)); }
            .pdp-gw-interswitch:hover:not([disabled]) { border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,0.12),0 12px 28px rgba(37,99,235,0.1); }
            .pdp-gw-interswitch::after { background:linear-gradient(135deg,rgba(37,99,235,0.04),rgba(37,99,235,0.01)); }
            .pdp-gw-loading { display:flex;align-items:center;gap:5px;font-size:0.7rem;color:#6b7280;font-weight:600; }
            @keyframes pdp-spin { to { transform:rotate(360deg); } }
            .pdp-gw-spin { animation:pdp-spin 0.8s linear infinite; }
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
                        <h3 class="mb-0" style="font-weight: 700; font-size: 1.5rem; color: #227722;">{{ $galleryProject->project_title }}</h3>
                        <span class="text-muted" style="font-size: 0.9rem;">{{ count($galleryProject->photos) + 1 }} Photos</span>
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
                        <div class="main-image-area mb-3 position-relative">
                            <template x-if="photos.length > 0">
                                <img :src="photos[activeIndex].url" class="main-image" :alt="photos[activeIndex].title">
                            </template>
                            
                            <!-- Image Counter -->
                            <div class="image-counter-badge">
                                <span x-text="(activeIndex + 1) + ' / ' + photos.length"></span>
                            </div>

                            <!-- Description Overlay -->
                            <div class="image-desc-overlay" x-show="showDesc && (photos[activeIndex].title || photos[activeIndex].description)" x-transition>
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
                        <div class="project-info-sidebar">
                            <!-- Funding Status -->
                            <div class="info-card mb-4">
                                <h5 class="card-title">Funding Status</h5>
                                <div class="progress-wrapper mb-3">
                                    @php
                                        $raised = floatval($galleryProject->raised ?? 0);
                                        $target = floatval($galleryProject->target ?? 0);
                                        $percentage = ($target > 0) ? round(($raised / $target) * 100, 1) : 0;
                                    @endphp
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-success font-weight-bold">Raised</span>
                                        <span class="text-muted">Target</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 align-items-end">
                                        <span class="h4 mb-0 text-success font-weight-bold">₦{{ number_format($raised) }}</span>
                                        <span class="h6 mb-0 text-muted">₦{{ number_format($target) }}</span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 small font-weight-bold">
                                        <span class="text-success">{{ $percentage }}% Funded</span>
                                        <span class="text-muted">₦{{ number_format(max(0, $target - $raised)) }} Remaining</span>
                                    </div>
                                </div>
                                <button wire:click="openDonationModal({{ $galleryProject->id }})" class="btn btn-success btn-block rounded-pill font-weight-bold py-2">
                                    Donate Now <i class="fa fa-heart ml-1"></i>
                                </button>
                            </div>

                            <!-- About Project -->
                            <div class="info-card">
                                <h5 class="card-title">About This Project</h5>
                                <div class="description-text">
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
        /* Vertical Card Stack Split Layout */
        .project-cards-stack {
            display: flex;
            flex-direction: column;
            gap: 36px;
            width: 100%;
        }

        .project-split-card {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .project-split-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.1);
        }

        .project-card-body {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            min-height: 380px;
            width: 100%;
        }

        /* 50% Image Column */
        .project-card-img-wrap {
            flex: 0 0 50% !important;
            width: 50% !important;
            max-width: 50% !important;
            position: relative;
            overflow: hidden;
            background: #0f172a;
            min-height: 380px;
        }

        .project-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }

        .project-split-card:hover .project-card-img {
            transform: scale(1.05);
        }

        /* Diagonal Masking Clip Paths */
        .slant-left-mask {
            clip-path: polygon(0 0, 100% 0, 85% 100%, 0 100%);
            -webkit-clip-path: polygon(0 0, 100% 0, 85% 100%, 0 100%);
            z-index: 2;
        }

        .slant-right-mask {
            clip-path: polygon(15% 0, 100% 0, 100% 100%, 0 100%);
            -webkit-clip-path: polygon(15% 0, 100% 0, 100% 100%, 0 100%);
            z-index: 2;
        }

        /* 50% Content Column */
        .project-card-content {
            flex: 0 0 50% !important;
            width: 50% !important;
            max-width: 50% !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 48px;
            background: #ffffff;
            z-index: 1;
        }

        .project-card-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            line-height: 1.3;
            font-family: 'Playfair Display', serif;
        }

        .project-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .project-card-title a:hover {
            color: #10b981;
        }

        .project-card-desc {
            color: #475569;
            font-size: 0.98rem;
            line-height: 1.65;
            margin-bottom: 24px;
        }

        /* Progress Indicator */
        .project-progress-wrapper {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }

        .progress-track-bg {
            flex: 1;
            height: 10px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill-mint {
            height: 100%;
            background: #10b981;
            border-radius: 6px;
            transition: width 0.8s ease-in-out;
        }

        .progress-pill-badge {
            background: #064e3b;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 14px;
            flex-shrink: 0;
        }

        /* Buttons Stack */
        .project-buttons-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .btn-donate-navy {
            background: #091e42 !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 1rem;
            padding: 14px 24px;
            border-radius: 8px;
            border: none;
            width: 100%;
            text-align: center;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
            box-shadow: 0 4px 12px rgba(9, 30, 66, 0.2);
        }

        .btn-donate-navy:hover {
            background: #0f2b5c !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .btn-read-ghost {
            background: #ffffff !important;
            color: #475569 !important;
            font-weight: 600;
            font-size: 0.98rem;
            padding: 13px 24px;
            border-radius: 8px;
            border: 1px solid #cbd5e1 !important;
            width: 100%;
            text-align: center;
            text-decoration: none !important;
            display: block;
            transition: all 0.2s ease;
        }

        .btn-read-ghost:hover {
            background: #f8fafc !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
            text-decoration: none !important;
        }

        @media (max-width: 991px) {
            .project-card-body {
                flex-direction: column !important;
                flex-wrap: wrap !important;
                min-height: auto;
            }
            .project-card-img-wrap {
                flex: none !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 260px;
                min-height: 260px;
                clip-path: none !important;
                -webkit-clip-path: none !important;
            }
            .project-card-content {
                flex: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 28px 24px;
            }
        }

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
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            cursor: pointer;
        }

        .project-details-container {
            position: relative;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 10001;
            animation: modalSlideUp 0.3s ease-out;
        }

        @keyframes modalSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .project-details-header {
            padding: 20px 30px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .project-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .project-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .close-btn {
            background: #f3f4f6;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #4b5563;
        }

        .close-btn:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .project-details-body {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: #f9fafb;
        }

        /* Image Viewer Styles */
        .main-image-area {
            width: 100%;
            height: 500px;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .image-counter-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        .image-desc-overlay {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
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
            margin-bottom: 5px;
            font-weight: 700;
            color: #1f2937;
        }

        .image-desc-overlay p {
            margin-bottom: 0;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .thumbnails-strip {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .thumbnail-item {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
            opacity: 0.7;
        }

        .thumbnail-item:hover {
            opacity: 1;
        }

        .thumbnail-item.active {
            border-color: #10b981;
            opacity: 1;
            transform: scale(1.05);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Sidebar Styles */
        .info-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            margin-bottom: 20px;
        }

        .card-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecfdf5;
            display: inline-block;
        }

        .description-text {
            color: #4b5563;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        /* Scrollbar for thumbnails */
        .thumbnails-strip::-webkit-scrollbar {
            height: 6px;
        }
        .thumbnails-strip::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .thumbnails-strip::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
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
                const btn     = document.getElementById('proj-page-squad-pay-btn');
                const btnText = document.getElementById('proj-page-squad-btn-text');
                const btnLoad = document.getElementById('proj-page-squad-btn-loading');

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

            // ── Interswitch redirect ─────────────────────────────────────
            Livewire.on('initiate-interswitch', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('proj-page-isw-pay-btn');
                const btnText = document.getElementById('proj-page-isw-btn-text');
                const btnLoad = document.getElementById('proj-page-isw-btn-loading');

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
