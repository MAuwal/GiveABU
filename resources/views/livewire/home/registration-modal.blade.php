<div>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @if($show)
    <!-- Modal Backdrop -->
    <div class="modal-backdrop fade show" 
         wire:click="close"
         style="z-index: 9998; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(5px);"></div>

    <!-- Modal Container -->
    <div class="modal fade show d-block" 
         tabindex="-1" 
         role="dialog" 
         style="z-index: 9999; overflow-y: hidden;">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" 
        style="max-width: 95vw; width: 1400px;">
            <div class="modal-content border-0 shadow-lg" 
            style="border-radius: 20px; overflow: hidden; height: 90vh;">
                <div class="row h-100 m-0">
                    <!-- Left Side - Branding -->
                    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center p-0 text-white position-relative overflow-hidden">
                        <!-- Background Image with Overlay -->
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                                    background: linear-gradient(to bottom right, rgba(6, 78, 59, 0.9), rgba(16, 185, 129, 0.8)), url('{{ asset('img/ABU SKY IMAGE.jpg') }}') center/cover no-repeat;
                                    mix-blend-mode: multiply;
                                    transform: scale(1.05);">
                        </div>

                        <!-- Slanted Divider (CSS Shape) -->
                        <div style="position: absolute; top: 0; right: -1px; width: 100px; height: 100%; background: #fff; 
                                    clip-path: polygon(100% 0, 0 0, 100% 100%); z-index: 2;"></div>

                        <!-- Content -->
                        <div class="position-relative w-100 p-5" 
                        style="z-index: 3; padding-left: 80px !important;">
                            <div class="mb-5">
                                <img src="{{ asset('abu_logo.png') }}" alt="ABU Logo" 
                                style="height: 100px; width: auto; filter: brightness(0) invert(1);">
                            </div>
                            <h1 class="font-weight-bold mb-4" 
                            style="font-family: 'Playfair Display', serif; font-size: 4.5rem; line-height: 1.1; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                Legacy &<br>Impact
                            </h1>
                            <p class="lead mb-0" style="font-family: 'Inter', sans-serif; font-size: 1.25rem; line-height: 1.6; max-width: 500px; color: #ffffff !important; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                Empowering the future through the Ahmadu Bello University Endowment Foundation. 
                                Manage contributions, projects, and donor relations efficiently.
                            </p>
                        </div>
                    </div>

                    <!-- Right Side - Form or Success Panel -->
                    <div class="col-lg-6 bg-white h-100 position-relative">
                        <!-- Close Button -->
                        <button type="button"
                                class="close position-absolute p-3"
                                wire:click="close"
                                aria-label="Close"
                                style="right: 20px; top: 20px; z-index: 10; opacity: 0.4; transition: all 0.3s; outline: none !important; border: none !important; background: transparent !important; box-shadow: none !important;">
                            <span aria-hidden="true" style="font-size: 2.5rem; font-weight: 300;">&times;</span>
                        </button>

                        {{-- ─── SUCCESS / VERIFICATION PANEL ─────────────────────────── --}}
                        @if($registrationComplete)
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center" style="padding: 3rem 3rem; text-align: center;">
                            {{-- Checkmark icon --}}
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: #d1fae5; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="12" fill="#059669"/>
                                    <path d="M7 12.5l3.5 3.5 6.5-7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>

                            <h2 class="font-weight-bold mb-2" style="color: #064e3b; font-family: 'Playfair Display', serif; font-size: 1.75rem;">
                                Registration Successful!
                            </h2>

                            <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; max-width: 380px; margin-bottom: 0.5rem;">
                                Welcome to <strong>GIVE ABU</strong>. You are now logged in.
                            </p>

                            @if($verificationSent)
                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 1rem 1.25rem; margin: 1.25rem 0; max-width: 400px; width: 100%; text-align: left;">
                                <p style="color: #065f46; font-size: 0.88rem; margin: 0; line-height: 1.5;">
                                    <strong>&#x2709;&#xfe0f; Verification email sent!</strong><br>
                                    Please check your inbox at <strong>{{ $email }}</strong> and click the link to verify your account.
                                </p>
                            </div>
                            @else
                            <p style="color: #6b7280; font-size: 0.88rem; line-height: 1.5; max-width: 380px; margin: 0.75rem 0 1.5rem;">
                                A verification email has been sent to <strong>{{ $email }}</strong>.
                                Verifying your email unlocks your full donor profile.
                            </p>
                            @endif

                            <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%; max-width: 360px; margin-top: 1rem;">
                                {{-- Resend / Verify Email button --}}
                                <button wire:click="resendVerificationEmail"
                                        @if($resendLoading) disabled @endif
                                        style="background: #064e3b; color: #fff; border: none; border-radius: 8px; padding: 13px 20px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background 0.2s; width: 100%;">
                                    @if($resendLoading)
                                        <span class="spinner-border spinner-border-sm mr-2" role="status"></span> Sending...
                                    @elseif($verificationSent)
                                        &#x21bb; Resend Verification Email
                                    @else
                                        &#x2709; Send Verification Email
                                    @endif
                                </button>

                                {{-- Skip for Now --}}
                                <button wire:click="skipVerification"
                                        style="background: transparent; color: #6b7280; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 20px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.2s; width: 100%;">
                                    Skip for Now &rarr;
                                </button>
                            </div>

                            <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 1.5rem;">
                                Email verification is optional and will not affect your ability to donate or browse.
                            </p>
                        </div>
                        @else
                        {{-- ─── REGISTRATION FORM ─────────────────────────────────────── --}}
                        <div class="h-100 overflow-auto custom-scrollbar d-flex flex-column" style="padding: 3rem 4rem;">
                            <div class="mb-4">
                                <h2 class="font-weight-bold mb-1" style="color: #064e3b; font-family: 'Playfair Display', serif; font-size: 2rem;">Create Account</h2>
                                <p class="text-muted" style="font-family: 'Inter', sans-serif; font-size: 1rem;">Please enter your details to register.</p>
                            </div>

                            @if(config('services.google.client_id'))
                            <!-- Google Login at Top -->
                            <div id="googleBtnReg-register" class="mb-3 d-flex justify-content-start" wire:ignore.self></div>

                            <div class="text-center mb-3 position-relative">
                                <hr class="my-0">
                                <span class="position-absolute bg-white px-3 text-muted" style="top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.8rem;">OR</span>
                            </div>
                            @endif

                            <style>
                                .reg-field.is-invalid { border-color: #dc3545 !important; }
                                .reg-field.is-invalid:focus { box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25) !important; }
                                .reg-error-msg { color: #dc3545; font-size: 0.78rem; margin-top: 4px; }
                            </style>

                            <form wire:submit.prevent="register">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">I am a: *</label>
                                    <select wire:model.live="donorType"
                                            class="form-control form-control-lg border-0 reg-field @error('donorType') is-invalid @enderror"
                                            style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;"
                                            required>
                                        <option value="">Select your type</option>
                                        <option value="addressable_alumni">Alumni</option>
                                        <option value="supporter">Supporter</option>
                                        <option value="corporate">Corporate</option>
                                        <option value="non_addressable_alumni">Others</option>
                                    </select>
                                    @error('donorType') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                </div>

                                @php
                                $countries = ["Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burundi","Cabo Verde","Cambodia","Cameroon","Canada","Central African Republic","Chad","Chile","China","Colombia","Comoros","Congo (Congo-Brazzaville)","Costa Rica","Croatia","Cuba","Cyprus","Czechia","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway","Oman","Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russia","Rwanda","Saint Kitts and Nevis","Saint Lucia","Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Sweden","Switzerland","Syria","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"];
                                @endphp

                                @if($showCorporateFields)
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Organisation Name *</label>
                                    <input type="text"
                                           wire:model="organisationName"
                                           placeholder="e.g. Dangote Group"
                                           class="form-control form-control-lg border-0 reg-field @error('organisationName') is-invalid @enderror"
                                           style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                    @error('organisationName') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                </div>
                                @endif

                                @if($showAlumniFields)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Entry Year</label>
                                            <input type="number"
                                                   wire:model="entryYear"
                                                   min="1950"
                                                   max="{{ date('Y') }}"
                                                   placeholder="e.g., 2016"
                                                   class="form-control form-control-lg border-0 reg-field @error('entryYear') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('entryYear') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Graduation Year</label>
                                            <input type="number"
                                                   wire:model="graduationYear"
                                                   min="1950"
                                                   max="{{ date('Y') + 6 }}"
                                                   placeholder="e.g., 2020"
                                                   class="form-control form-control-lg border-0 reg-field @error('graduationYear') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('graduationYear') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Donor Surname *</label>
                                            <input type="text"
                                                   wire:model="surname"
                                                   class="form-control form-control-lg border-0 reg-field @error('surname') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('surname') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Donor Name *</label>
                                            <input type="text"
                                                   wire:model="name"
                                                   class="form-control form-control-lg border-0 reg-field @error('name') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('name') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">
                                                @if($showCorporateFields) Email (Organization's) * @else Email * @endif
                                            </label>
                                            <input type="email"
                                                   wire:model="email"
                                                   class="form-control form-control-lg border-0 reg-field @error('email') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('email') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Phone Number *</label>
                                            <input type="tel"
                                                   wire:model="phone"
                                                   placeholder="e.g. 08012345678"
                                                   class="form-control form-control-lg border-0 reg-field @error('phone') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('phone') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">State *</label>
                                            <input type="text"
                                                   wire:model="state"
                                                   class="form-control form-control-lg border-0 reg-field @error('state') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('state') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">LGA *</label>
                                            <input type="text"
                                                   wire:model="lga"
                                                   class="form-control form-control-lg border-0 reg-field @error('lga') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('lga') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Nationality *</label>
                                            <select wire:model="nationality"
                                                    class="form-control form-control-lg border-0 reg-field @error('nationality') is-invalid @enderror"
                                                    style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                                <option value="Nigerian" selected>Nigerian</option>
                                                @foreach($countries as $country)
                                                    @if($country !== 'Nigeria')
                                                        <option value="{{ $country }}">{{ $country }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('nationality') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Password *</label>
                                            <input type="password"
                                                   wire:model="password"
                                                   minlength="6"
                                                   class="form-control form-control-lg border-0 reg-field @error('password') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('password') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase mb-1" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.75rem;">Confirm Password *</label>
                                            <input type="password"
                                                   wire:model="passwordConfirm"
                                                   class="form-control form-control-lg border-0 reg-field @error('passwordConfirm') is-invalid @enderror"
                                                   style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.5rem; font-size: 0.9rem; height: 45px; color: #374151;">
                                            @error('passwordConfirm') <div class="reg-error-msg">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                @if($error)
                                <div class="alert alert-danger rounded-lg border-0 mb-4" style="font-size: 0.875rem;">
                                    {{ $error }}
                                </div>
                                @endif

                                @if($success)
                                <div class="alert alert-success rounded-lg border-0 mb-4">
                                    {{ $success }}
                                </div>
                                @endif

                                <button type="submit" 
                                        class="btn btn-primary btn-lg btn-block font-weight-bold shadow-sm mb-3"
                                        style="background-color: #064e3b; color: #ffffff; border: none; border-radius: 0.5rem; padding: 12px; height: auto; font-family: 'Inter', sans-serif; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(251, 255, 254, 0.97);"
                                        @if($loading) disabled @endif>
                                    @if(!$loading)
                                        Register
                                    @else
                                        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                                        Registering...
                                    @endif
                                </button>

                                <p class="text-center mt-4 mb-0 text-muted">
                                    Already have an account?
                                    <a href="#"
                                       class="font-weight-bold text-decoration-none"
                                       style="color: #064e3b;"
                                       wire:click.prevent="close"
                                       onclick="setTimeout(() => Livewire.dispatch('openLoginModal'), 100);">
                                        Login here
                                    </a>
                                </p>
                            </form>
                        </div>
                        @endif
                        {{-- end registrationComplete --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        // Handle Google credential response - make it global
        window.handleGoogleCredentialResponse = function(response) {
            console.log('Google credential received, processing...');
            
            fetch('/api/donor-sessions/google-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ 
                    token: response.credential,
                    device_session_id: null 
                })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(data => {
                        throw new Error(data.message || 'Google login failed');
                    });
                }
                return res.json();
            })
            .then(data => {
                console.log('Google Login API Response:', data);
                
                // Handle different response structures
                let token = data.token;
                if (!token && data.data && data.data.token) {
                    token = data.data.token;
                }
                
                if (token) {
                    console.log('Google login successful, saving session...');
                    Livewire.dispatch('save-auth-token', { token: token });
                } else {
                    console.error('Token missing in response:', data);
                    throw new Error('No token received from server');
                }
            })
            .catch(error => {
                console.error('Google login error:', error);
                alert(error.message || 'Google login failed');
            });
        };
        
        // Make functions globally accessible
        window.initGoogleRegistrationButton = function() {
            const buttonId = 'googleBtnReg-register';
            const buttonElement = document.getElementById(buttonId);
            
            if (!buttonElement) {
                console.warn('Google button element not found:', buttonId);
                return;
            }

            // Wait for Google script to load
            let retries = 0;
            function tryInit() {
                if (window.google && window.google.accounts && window.google.accounts.id) {
                    console.log('Initializing Google Sign-In button for registration');
                    // Clear any existing button
                    buttonElement.innerHTML = '';
                    
                    // Get button width from parent
                    const parentWidth = buttonElement.parentElement.offsetWidth || 400;
                    
                    // Initialize Google Sign-In
                    var _googleClientId = "{{ config('services.google.client_id') }}";
                    if (!_googleClientId) {
                        buttonElement.innerHTML = '<p class="text-xs text-slate-400 text-center">Google Sign-In not configured.</p>';
                        return;
                    }
                    window.google.accounts.id.initialize({
                        client_id: _googleClientId,
                        callback: window.handleGoogleCredentialResponse
                    });
                    
                    // Render Google Sign-In button with pixel width
                    window.google.accounts.id.renderButton(
                        buttonElement,
                        { 
                            theme: "outline", 
                            size: "large", 
                            width: parentWidth - 20, // Use pixel width instead of percentage
                            text: "signup_with" 
                        }
                    );
                    console.log('Google Sign-In button rendered for registration');
                } else {
                    retries++;
                    if (retries < 50) { // Try for up to 5 seconds
                        setTimeout(tryInit, 100);
                    } else {
                        console.error('Google Sign-In script not loaded after 5 seconds');
                    }
                }
            }
            
            tryInit();
        };

        // Watch for modal show state and initialize button
        $wire.watch('show', (value) => {
            if (value) {
                console.log('Registration modal opened, initializing Google button');
                setTimeout(() => window.initGoogleRegistrationButton(), 300);
            }
        });

        // Also listen for the event
        Livewire.on('initGoogleRegistration', () => {
            console.log('Received initGoogleRegistration event');
            setTimeout(() => window.initGoogleRegistrationButton(), 100);
        });

        // Initialize on component mount if modal is already shown
        if ($wire.show) {
            setTimeout(() => window.initGoogleRegistrationButton(), 500);
        }
    </script>
    @endscript
</div>

