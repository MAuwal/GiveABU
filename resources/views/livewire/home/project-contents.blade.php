<div class="project-contents-area">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-muted">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects') }}" class="text-muted">Projects</a></li>
                        <li class="breadcrumb-item active text-success" aria-current="page">{{ $project->project_title }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Banner & Title Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="project-banner position-relative rounded-lg overflow-hidden" style="height: 400px; background-color: #f0f0f0;">
                    <img src="{{ $project->icon_image ? $project->icon_image_url : asset('img/causes/1.png') }}" 
                         alt="{{ $project->project_title }}" 
                         class="w-100 h-100" 
                         style="object-fit: cover;">
                    
                    <div class="banner-overlay position-absolute w-100 h-100 d-flex flex-column justify-content-end p-4 p-md-5" 
                         style="top: 0; left: 0; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);">
                        <span class="badge badge-success mb-3 px-3 py-2" style="width: fit-content; background-color: #064e3b;">EDUCATION & SUPPORT</span>
                        <h1 class="text-white font-weight-bold mb-2" style="font-size: 2.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            {{ $project->project_title }}
                        </h1>
                        <p class="text-white-50 mb-0" style="font-size: 1.1rem; max-width: 800px;">
                            {{ Str::limit($project->project_description, 150) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Project Comprehensive Details -->
                @if($project->details)
                <style>
                    .rich-text-content ul { list-style-type: disc; padding-left: 20px; margin-bottom: 1rem; }
                    .rich-text-content ol { list-style-type: decimal; padding-left: 20px; margin-bottom: 1rem; }
                    .rich-text-content p:last-child { margin-bottom: 0; }
                </style>
                <div class="project-comprehensive-details mb-5">
                    
                    @if(!empty(strip_tags($project->details->background)))
                    <div class="mb-5">
                        <h3 class="font-weight-bold mb-3 d-flex align-items-center" style="color: #1f2937;">
                            <i class="fa fa-bookmark text-success mr-2" style="font-size: 1.2rem;"></i> Background
                        </h3>
                        <div class="text-muted p-4 rounded rich-text-content" style="font-size: 1.05rem; line-height: 1.8; background: #f9fafb; border-left: 4px solid #064e3b;">
                            {!! $project->details->background !!}
                        </div>
                    </div>
                    @endif

                    @if(!empty(strip_tags($project->details->challenges)))
                    <div class="mb-5">
                        <h3 class="font-weight-bold mb-3 d-flex align-items-center" style="color: #1f2937;">
                            <i class="fa fa-exclamation-circle text-warning mr-2" style="font-size: 1.2rem;"></i> Key Challenges
                        </h3>
                        <div class="text-muted rich-text-content" style="font-size: 1.05rem; line-height: 1.8;">
                            {!! $project->details->challenges !!}
                        </div>
                    </div>
                    @endif

                    @if(!empty(strip_tags($project->details->proposed_interventions)))
                    <div class="mb-5 p-4 rounded shadow-sm" style="background-color: white; border: 1px solid #e5e7eb;">
                        <h3 class="font-weight-bold mb-3" style="color: #064e3b;">
                            <i class="fa fa-cogs mr-2"></i> Proposed Interventions
                        </h3>
                        <div class="text-muted rich-text-content" style="font-size: 1.05rem; line-height: 1.8;">
                            {!! $project->details->proposed_interventions !!}
                        </div>
                    </div>
                    @endif

                    @if(!empty(strip_tags($project->details->expected_outcomes)))
                    <div class="mb-5">
                        <h3 class="font-weight-bold mb-3 d-flex align-items-center" style="color: #1f2937;">
                            <i class="fa fa-line-chart text-primary mr-2" style="font-size: 1.2rem;"></i> Expected Outcomes
                        </h3>
                        <div class="text-muted p-4 rounded rich-text-content" style="font-size: 1.05rem; line-height: 1.8; background: #f0fdf4; border: 1px solid #dcfce7;">
                            {!! $project->details->expected_outcomes !!}
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        @if(!empty(strip_tags($project->details->beneficiaries)))
                        <div class="col-md-6 mb-4">
                            <div class="h-100 p-4 rounded shadow-sm" style="background: white; border: 1px solid #e5e7eb; border-top: 4px solid #3b82f6;">
                                <h4 class="font-weight-bold mb-3" style="color: #1f2937;">
                                    <i class="fa fa-users text-primary mr-2"></i> Target Beneficiaries
                                </h4>
                                <div class="text-muted rich-text-content" style="font-size: 0.95rem; line-height: 1.7;">
                                    {!! $project->details->beneficiaries !!}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(!empty(strip_tags($project->details->budget_estimates)))
                        <div class="col-md-6 mb-4">
                            <div class="h-100 p-4 rounded shadow-sm" style="background: white; border: 1px solid #e5e7eb; border-top: 4px solid #10b981;">
                                <h4 class="font-weight-bold mb-3" style="color: #1f2937;">
                                    <i class="fa fa-money text-success mr-2"></i> Budget Estimates
                                </h4>
                                <div class="text-muted rich-text-content" style="font-size: 0.95rem; line-height: 1.7;">
                                    {!! $project->details->budget_estimates !!}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <hr class="my-5" style="border-top: 2px dashed #e5e7eb;">
                </div>
                @endif

                <!-- The Challenge / Description -->
                <div class="mb-5">
                    <h3 class="font-weight-bold mb-3" style="color: #1f2937;">The Challenge</h3>
                    <div class="text-muted" style="font-size: 1.05rem; line-height: 1.8;">
                        <p>{{ $project->project_description }}</p>
                    </div>
                </div>

                <!-- How Your Donation Helps (Static for now, could be dynamic) -->
                <div class="mb-5">
                    <h4 class="font-weight-bold mb-4" style="color: #1f2937;">How Your Donation Helps</h4>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start p-3 rounded" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                <div class="icon-box mr-3 text-success">
                                    <i class="fa fa-graduation-cap fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1">Full Tuition Coverage</h6>
                                    <p class="small text-muted mb-0">Covering 100% of academic fees for top-performing indigent students.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start p-3 rounded" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                <div class="icon-box mr-3 text-success">
                                    <i class="fa fa-home fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1">Living Stipends</h6>
                                    <p class="small text-muted mb-0">Monthly allowances for food, accommodation, and study materials.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start p-3 rounded" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                <div class="icon-box mr-3 text-success">
                                    <i class="fa fa-users fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1">Mentorship Programs</h6>
                                    <p class="small text-muted mb-0">Connecting scholars with alumni for career guidance and networking.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start p-3 rounded" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                <div class="icon-box mr-3 text-success">
                                    <i class="fa fa-laptop fa-2x"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-1">Tech Grants</h6>
                                    <p class="small text-muted mb-0">Providing laptops and internet access for modern learning needs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Green Highlight Section -->
                <div class="green-highlight p-4 p-md-5 rounded-lg mb-5 position-relative overflow-hidden" 
                     style="background-color: #064e3b; color: white;">
                    <div class="row align-items-center position-relative" style="z-index: 2;">
                        <div class="col-md-3 mb-3 mb-md-0 text-center">
                            <div class="rounded-circle overflow-hidden mx-auto" style="width: 100px; height: 100px; border: 3px solid rgba(255,255,255,0.2);">
                                <img src="{{ $project->icon_image ? $project->icon_image_url : asset('img/causes/1.png') }}" 
                                     alt="Icon" class="w-100 h-100" style="object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <i class="fa fa-quote-left fa-2x mb-3" style="opacity: 0.3;"></i>
                            <p class="font-italic mb-3" style="font-size: 1.1rem; line-height: 1.6; color: #e5e7eb;">
                                "The ABU Endowment Scholarship didn't just pay my fees; it gave me the peace of mind to focus on my studies. Today, I'm graduating with First Class Honors because someone believed in me."
                            </p>
                            <div>
                                <h6 class="font-weight-bold mb-0">Amina Yusuf</h6>
                                <small style="opacity: 0.7;">Computer Science, Class of 2023</small>
                            </div>
                            <i class="fa fa-quote-right fa-2x position-absolute" style="bottom: 0; right: 15px; opacity: 0.3;"></i>
                        </div>
                    </div>
                    <!-- Decorative background element -->
                    <div class="position-absolute" style="top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                </div>

                <!-- Impact Gallery -->
                @if($project->photos && count($project->photos) > 0)
                <div class="mb-5">
                    <h4 class="font-weight-bold mb-4" style="color: #1f2937;">Impact Gallery</h4>
                    <div class="row">
                        @foreach($project->photos as $photo)
                        <div class="col-md-6 mb-4">
                            <div class="gallery-item rounded overflow-hidden shadow-sm position-relative group" style="height: 200px;">
                                <img src="{{ $photo->image_url }}" 
                                     alt="{{ $photo->title ?? 'Gallery Image' }}" 
                                     class="w-100 h-100" 
                                     style="object-fit: cover; transition: transform 0.5s ease;">
                                <div class="overlay position-absolute w-100 h-100 d-flex align-items-end p-3" 
                                     style="top: 0; left: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); opacity: 0; transition: opacity 0.3s ease;">
                                    <p class="text-white mb-0 small font-weight-bold">{{ $photo->title ?? 'Project Photo' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-widget p-4 bg-white rounded shadow-sm border mb-4" style="position: sticky; top: 100px;">
                    @php
                        $raised = floatval($project->raised ?? 0);
                        $target = floatval($project->target ?? 0);
                        $percentage = ($target > 0) ? round(($raised / $target) * 100, 1) : 0;
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small font-weight-bold">Fundraising Progress</span>
                        <span class="text-muted small">of ₦{{ number_format($target/1000000, 1) }}M goal</span>
                    </div>

                    <h2 class="font-weight-bold text-success mb-1">₦{{ number_format($raised/1000000, 1) }}M</h2>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-success font-weight-bold">{{ $percentage }}% Funded</span>
                        <span class="text-muted">1,280 Donors</span>
                    </div>

                    <div class="progress mb-4" style="height: 8px; border-radius: 4px; background-color: #e5e7eb;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ min($percentage, 100) }}%; border-radius: 4px;"></div>
                    </div>

                    <button wire:click="openDonationModal" class="btn btn-block btn-lg mb-3" 
                            style="background-color: #064e3b; color: white; font-weight: 600; border-radius: 50px;">
                        Donate Now <i class="fa fa-arrow-right ml-2"></i>
                    </button>

                    <button class="btn btn-block btn-outline-secondary btn-sm mb-3" style="border-radius: 50px;">
                        <i class="fa fa-share-alt mr-2"></i> Share this Cause
                    </button>

                    <div class="text-center">
                        <small class="text-muted"><i class="fa fa-lock mr-1"></i> Secure SSL Encrypted Transaction</small>
                    </div>

                    <hr class="my-4">

                    <h6 class="font-weight-bold mb-3">Project Details</h6>
                    <ul class="list-unstyled text-muted small" style="line-height: 2;">
                        <li class="d-flex justify-content-between">
                            <span>Beneficiaries:</span>
                            <span class="text-dark">Undergraduate Students</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Duration:</span>
                            <span class="text-dark">Ongoing (Annual)</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Location:</span>
                            <span class="text-dark">Zaria Campus</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Category:</span>
                            <span class="text-dark">Education</span>
                        </li>
                    </ul>
                    
                    <div class="mt-4 pt-3 border-top">
                        <p class="small text-muted mb-0">
                            Have questions about this project? <a href="#" class="text-success font-weight-bold">Contact the Endowment Office</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Ways to Support / Other Projects -->
        <div class="mt-5 pt-5 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="font-weight-bold mb-0" style="color: #1f2937;">Other Ways to Support</h4>
                <a href="{{ route('projects') }}" class="text-success font-weight-bold small">View All Projects <i class="fa fa-arrow-right ml-1"></i></a>
            </div>
            
            <div class="row">
                @foreach($otherProjects as $other)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="position-relative" style="height: 180px;">
                            <img src="{{ $other->icon_image_url ?? asset('img/causes/1.png') }}" 
                                 class="card-img-top w-100 h-100" 
                                 style="object-fit: cover;" 
                                 alt="{{ $other->project_title }}">
                        </div>
                        <div class="card-body p-4">
                            <span class="text-uppercase text-muted small font-weight-bold mb-2 d-block">PROJECT</span>
                            <h5 class="card-title font-weight-bold mb-2">
                                <a href="{{ route('project.single', $other->id) }}" class="text-dark text-decoration-none">
                                    {{ Str::limit($other->project_title, 40) }}
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($other->project_description, 80) }}
                            </p>
                            
                            @php
                                $oRaised = floatval($other->raised ?? 0);
                                $oTarget = floatval($other->target ?? 0);
                                $oPercent = ($oTarget > 0) ? round(($oRaised / $oTarget) * 100) : 0;
                            @endphp
                            
                            <div class="mb-3">
                                <span class="text-success font-weight-bold small">{{ $oPercent }}% Funded</span>
                            </div>
                            
                            <a href="{{ route('project.single', $other->id) }}" class="font-weight-bold text-success small text-decoration-none">
                                Donate <i class="fa fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Donation Modal -->
    @if($showModal)
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
                    <h5 style="color:#fff;font-size:1.15rem;font-weight:700;margin:0 0 3px;font-family:'Playfair Display',serif;">Donate to {{ $project->project_title }}</h5>
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
                            <div class="pc-don-iw">
                                <span class="pc-don-px"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
                                <input type="email" wire:model="email" class="pc-don-in" placeholder="you@example.com" required>
                            </div>
                            @error('email') <span style="color:#ef4444;font-size:0.74rem;margin-top:3px;display:block;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Amount -->
                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:0.71rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Donation Amount <span style="color:#ef4444;">*</span></label>
                            <div class="pc-don-iw">
                                <span class="pc-don-px pc-don-cur">₦</span>
                                <input type="number" min="100" step="500" wire:model.live="customAmount" class="pc-don-in pc-don-in-lg" placeholder="Enter amount">
                            @error('amount') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Payment method -->
                        @if($paymentReference)
                            <button type="button" wire:click="verifyPayment('{{ $paymentReference }}')" style="width:100%;padding:14px;background:linear-gradient(135deg,#f97316,#ea580c);color:#fff;font-weight:700;border:none;border-radius:14px;font-size:0.95rem;cursor:pointer;box-shadow:0 8px 20px rgba(249,115,22,0.25);display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:8px;">
                                Verify Payment
                                <span wire:loading style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:pc-spin 0.8s linear infinite;"></span>
                            </button>
                            <p style="text-align:center;font-size:0.73rem;color:#9ca3af;">Click this if the payment window closed but this modal didn't.</p>
                        @else
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="flex:1;height:1px;background:#f3f4f6;"></div>
                                <span style="font-size:0.62rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1.3px;white-space:nowrap;">Choose payment method</span>
                                <div style="flex:1;height:1px;background:#f3f4f6;"></div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                                {{-- Paystack - Commented out (testing mode only)
                                <button type="submit" wire:loading.attr="disabled" wire:target="donate" class="pc-gw-card pc-gw-paystack">
                                    <span wire:loading.remove wire:target="donate" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('paystack.png') }}" alt="Paystack" class="pc-gw-img">
                                        </div>
                                    </span>
                                    <span wire:loading wire:target="donate" class="pc-gw-loading">
                                        <svg class="pc-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/></svg>
                                        Processing…
                                    </span>
                                </button>
                                --}}

                                <!-- Squad -->
                                <button type="button" id="pc-squad-pay-btn" wire:click="payWithSquad" wire:loading.attr="disabled" wire:target="payWithSquad" class="pc-gw-card pc-gw-squad">
                                    <span id="pc-squad-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('GTCO-Squad-Hackathon-Program.jpg') }}" alt="Pay via Squad" class="pc-gw-img">
                                        </div>
                                    </span>
                                    <span id="pc-squad-btn-loading" class="pc-gw-loading" style="display:none;">
                                        <svg class="pc-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#374151" stroke-width="3" stroke-linecap="round"/></svg>
                                        Redirecting…
                                    </span>
                                </button>

                                <!-- Interswitch -->
                                <button type="button" id="pc-isw-pay-btn" wire:click="payWithInterswitch" wire:loading.attr="disabled" wire:target="payWithInterswitch" class="pc-gw-card pc-gw-interswitch">
                                    <span id="pc-isw-btn-text" style="display:flex;flex-direction:column;align-items:center;gap:4px;width:100%;">
                                        <div style="display:flex;align-items:center;justify-content:center;">
                                            <img src="{{ asset('interswitch2.jpg') }}" alt="Pay via Interswitch" class="pc-gw-img">
                                        </div>
                                    </span>
                                    <span id="pc-isw-btn-loading" class="pc-gw-loading" style="display:none;">
                                        <svg class="pc-gw-spin" width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#d1d5db" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/></svg>
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
            .pc-don-iw { display:flex;align-items:center;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:11px;overflow:hidden;transition:border-color 0.2s,box-shadow 0.2s,background 0.2s; }
            .pc-don-iw:focus-within { border-color:#227722;background:#fff;box-shadow:0 0 0 3px rgba(34,119,34,0.08); }
            .pc-don-px { padding:0 11px;display:flex;align-items:center;flex-shrink:0; }
            .pc-don-cur { font-weight:800;color:#227722;font-size:1rem; }
            .pc-don-in { border:none;outline:none;background:transparent;height:47px;padding:0 10px 0 2px;font-size:0.9rem;color:#1f2937;font-weight:500;flex:1;min-width:0; }
            .pc-don-in-lg { font-weight:700;font-size:1.02rem; }
            .pc-gw-card { background:#fff;border:2px solid #e5e7eb;border-radius:13px;padding:14px 10px;cursor:pointer;transition:all 0.25s cubic-bezier(0.4,0,0.2,1);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:74px;position:relative;overflow:hidden; }
            .pc-gw-card::after { content:'';position:absolute;inset:0;border-radius:11px;opacity:0;transition:opacity 0.25s ease; }
            .pc-gw-card:hover:not([disabled]) { transform:translateY(-4px) scale(1.02);box-shadow:0 12px 28px rgba(0,0,0,0.1); }
            .pc-gw-card:hover:not([disabled])::after { opacity:1; }
            .pc-gw-card:active:not([disabled]) { transform:translateY(-1px) scale(0.98);box-shadow:0 4px 12px rgba(0,0,0,0.08);transition-duration:0.1s; }
            .pc-gw-card[disabled] { opacity:0.5;cursor:not-allowed;filter:grayscale(0.4); }
            .pc-gw-img { height:48px;width:auto;max-width:140px;object-fit:contain;border-radius:6px;transition:transform 0.25s cubic-bezier(0.4,0,0.2,1),filter 0.25s ease; }
            .pc-gw-card:hover:not([disabled]) .pc-gw-img { transform:scale(1.06);filter:brightness(1.05); }
            .pc-gw-card:active:not([disabled]) .pc-gw-img { transform:scale(0.96); }
            .pc-gw-squad:hover:not([disabled]) { border-color:#00b8a9;box-shadow:0 0 0 3px rgba(0,184,169,0.12),0 12px 28px rgba(0,184,169,0.1); }
            .pc-gw-squad::after { background:linear-gradient(135deg,rgba(0,184,169,0.04),rgba(0,184,169,0.01)); }
            .pc-gw-interswitch:hover:not([disabled]) { border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,0.12),0 12px 28px rgba(37,99,235,0.1); }
            .pc-gw-interswitch::after { background:linear-gradient(135deg,rgba(37,99,235,0.04),rgba(37,99,235,0.01)); }
            .pc-gw-loading { display:flex;align-items:center;gap:5px;font-size:0.7rem;color:#6b7280;font-weight:600; }
            @keyframes pc-spin { to { transform:rotate(360deg); } }
            .pc-gw-spin { animation:pc-spin 0.8s linear infinite; }
        </style>
    </div>
    @endif

    <style>
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        .gallery-item:hover .overlay {
            opacity: 1 !important;
        }
    </style>

    <!-- Paystack Integration Script -->
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
                        alert('Payment successful! Verifying transaction ' + response.reference);
                        console.log('Paystack success, calling verifyPayment with reference:', response.reference);
                        // Try direct component call using ID
                        let component = Livewire.find('{{ $this->getId() }}');
                        if (component) {
                            component.call('verifyPayment', response.reference);
                        } else {
                            console.error('Livewire component not found');
                            // Fallback to dispatch
                            Livewire.dispatch('project-payment-success', { reference: response.reference });
                        }
                    }
                });
                
                handler.openIframe();
            });

            // ── Squad redirect ──────────────────────────────────────────
            Livewire.on('initiate-squad', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('pc-squad-pay-btn');
                const btnText = document.getElementById('pc-squad-btn-text');
                const btnLoad = document.getElementById('pc-squad-btn-loading');
                const showLoading = () => { if(btn) btn.disabled=true; if(btnText) btnText.style.display='none'; if(btnLoad) btnLoad.style.display='flex'; };
                const hideLoading = () => { if(btn) btn.disabled=false; if(btnText) btnText.style.display='flex'; if(btnLoad) btnLoad.style.display='none'; };
                showLoading();
                try {
                    const res = await fetch('/api/squad/pay', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                        body: JSON.stringify({ amount:p.amount, email:p.email, customer_name:p.customer_name||'', project_id:p.project_id||null }),
                    });
                    const result = await res.json();
                    if (result.checkout_url) { window.location.href = result.checkout_url; }
                    else { alert(result.message || 'Unable to initiate Squad payment. Please try again.'); hideLoading(); }
                } catch (err) {
                    alert('A network error occurred. Please try again.');
                    hideLoading();
                }
            });

            // ── Interswitch redirect ─────────────────────────────────────
            Livewire.on('initiate-interswitch', async (data) => {
                const p       = Array.isArray(data) ? data[0] : data;
                const btn     = document.getElementById('pc-isw-pay-btn');
                const btnText = document.getElementById('pc-isw-btn-text');
                const btnLoad = document.getElementById('pc-isw-btn-loading');
                const showLoading = () => { if(btn) btn.disabled=true; if(btnText) btnText.style.display='none'; if(btnLoad) btnLoad.style.display='flex'; };
                const hideLoading = () => { if(btn) btn.disabled=false; if(btnText) btnText.style.display='flex'; if(btnLoad) btnLoad.style.display='none'; };
                showLoading();
                try {
                    const res = await fetch('/api/interswitch/pay', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''},
                        body: JSON.stringify({ amount:p.amount, email:p.email, customer_name:p.customer_name||'', project_id:p.project_id||null, callback_url: window.location.origin + '/api/interswitch/redirect' }),
                    });
                    const result = await res.json();
                    if (!res.ok) throw new Error(result.message || 'Unable to initialize Interswitch payment.');
                    if (!result.checkout_url || !result.payload) throw new Error('Interswitch did not return a valid payment URL.');
                    const form = document.createElement('form');
                    form.method = 'POST'; form.action = result.checkout_url; form.style.display = 'none';
                    const allowedFields = ['merchant_code','pay_item_id','txn_ref','amount','currency','site_redirect_url','cust_name','cust_email','cust_id','pay_item_name','mode'];
                    Object.entries(result.payload).forEach(([key, value]) => {
                        if (allowedFields.includes(key) && value !== undefined && value !== null) {
                            const input = document.createElement('input'); input.type = 'hidden'; input.name = key; input.value = value; form.appendChild(input);
                        }
                    });
                    document.body.appendChild(form); form.submit();
                } catch (err) {
                    alert(err.message || 'Failed to initiate Interswitch payment.'); hideLoading();
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
        });
    </script>
</div>
