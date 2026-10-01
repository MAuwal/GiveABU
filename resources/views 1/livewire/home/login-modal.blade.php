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
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95vw; width: 1400px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; height: 90vh;">
                <div class="row h-100 m-0">
                    <!-- Left Side - Branding -->
                    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center p-0 text-white position-relative overflow-hidden">
                        <!-- Background Image with Overlay -->
                        <!-- Background Image -->
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                                    background: url('{{ asset('img/ABU SKY IMAGE.jpg') }}') center/cover no-repeat;">
                        </div>

                        <!-- Overlay -->
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
                                    background: linear-gradient(to bottom right, rgba(6, 78, 59, 0.9), rgba(16, 185, 129, 0.8));
                                    mix-blend-mode: multiply;">
                        </div>

                        <!-- Slanted Divider (CSS Shape) -->
                        <div style="position: absolute; top: 0; right: -1px; width: 100px; height: 100%; background: #fff; 
                                    clip-path: polygon(100% 0, 0 0, 100% 100%); z-index: 2;"></div>

                        <!-- Content -->
                        <div class="position-relative w-100 p-5" style="z-index: 3; padding-left: 80px !important;">
                            <div class="mb-5">
                                <img src="{{ asset('abu_logo.png') }}" alt="ABU Logo" style="height: 100px; width: auto; filter: brightness(0) invert(1);">
                            </div>
                            <h1 class="font-weight-bold mb-4" style="font-family: 'Playfair Display', serif; font-size: 4.5rem; line-height: 1.1; color: #ffffff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                Legacy &<br>Impact
                            </h1>
                            <p class="lead mb-0" style="font-family: 'Inter', sans-serif; font-size: 1.25rem; line-height: 1.6; max-width: 500px; color: #ffffff !important; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                Empowering the future through the Ahmadu Bello University Endowment Foundation. 
                                Manage contributions, projects, and donor relations efficiently.
                            </p>
                        </div>
                    </div>

                    <!-- Right Side - Form -->
                    <div class="col-lg-6 bg-white h-100 position-relative">
                        <!-- Close Button -->
                        <button type="button" 
                                class="close position-absolute p-3" 
                                wire:click="close"
                                aria-label="Close"
                                style="right: 20px; top: 20px; z-index: 10; opacity: 0.4; transition: all 0.3s; outline: none !important; border: none !important; background: transparent !important; box-shadow: none !important;">
                            <span aria-hidden="true" style="font-size: 2.5rem; font-weight: 300;">&times;</span>
                        </button>

                        <div class="h-100 overflow-auto custom-scrollbar d-flex flex-column justify-content-center" style="padding: 6rem 6rem 4rem 6rem;">
                            <div class="mb-5">
                                <h2 class="font-weight-bold mb-2" style="color: #064e3b; font-family: 'Playfair Display', serif; font-size: 2.5rem;">Welcome Back</h2>
                                <p class="text-muted" style="font-size: 1.1rem; font-family: 'Inter', sans-serif;">Please enter your details to sign in.</p>
                            </div>

                            @if(config('services.google.client_id'))
                            <!-- Google Login at Top -->
                            <div id="googleBtnLogin-login" class="mb-4 d-flex justify-content-start" wire:ignore.self></div>

                            <div class="text-center mb-4 position-relative">
                                <hr class="my-0">
                                <span class="position-absolute bg-white px-3 text-muted" style="top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.9rem;">OR</span>
                            </div>
                            @endif

                            <form wire:submit.prevent="login">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark small text-uppercase mb-2" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.875rem;">Email Address</label>
                                    <input type="text" 
                                           wire:model="username"
                                           class="form-control form-control-lg border-0"
                                           placeholder="admin@abu.edu.ng"
                                           style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.75rem; font-size: 0.95rem; height: 50px; padding-left: 20px; color: #374151;"
                                           required>
                                </div>

                                <div class="form-group mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="font-weight-bold text-dark small text-uppercase mb-0" style="font-family: 'Inter', sans-serif; color: #374151; font-size: 0.875rem;">Password</label>
                                        <a href="#" class="small font-weight-bold text-decoration-none" style="color: #10b981;">Forgot password?</a>
                                    </div>
                                    <input type="password" 
                                           wire:model="password"
                                           class="form-control form-control-lg border-0"
                                           placeholder="••••••••"
                                           style="background-color: #f9fafb; border: 1px solid #e5e7eb !important; border-radius: 0.75rem; font-size: 0.95rem; height: 50px; padding-left: 20px; color: #374151;"
                                           required>
                                </div>

                                <div class="form-group mb-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="rememberMe">
                                        <label class="custom-control-label text-muted" for="rememberMe" style="font-size: 0.95rem;">Remember me for 30 days</label>
                                    </div>
                                </div>

                                @if($error)
                                <div class="alert alert-danger rounded-lg border-0 mb-4">
                                    {{ $error }}
                                </div>
                                @endif

                                <button type="submit"
                                        class="btn btn-primary btn-lg btn-block font-weight-bold shadow-sm mb-4"
                                        style="background-color: #064e3b; color: #ffffff; border: none; border-radius: 0.75rem; padding: 14px; height: auto; font-family: 'Inter', sans-serif; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(6, 78, 59, 0.3);"
                                        @if($loading) disabled @endif>
                                    @if(!$loading)
                                        Sign In <i class="fas fa-arrow-right ml-2"></i>
                                    @else
                                        <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                                        Logging in...
                                    @endif
                                </button>

                                <p class="text-center mt-2 mb-0 text-muted">
                                    <a href="#"
                                       class="font-weight-bold text-decoration-none text-muted"
                                       wire:click.prevent="close">
                                        <i class="fas fa-arrow-left mr-2"></i> Back to Website
                                    </a>
                                </p>

                                <div class="d-flex align-items-center justify-content-center mt-3" style="gap: 10px;">
                                    <span class="text-muted small">Don't have an account?</span>
                                    <a href="#"
                                       wire:click.prevent="close"
                                       onclick="setTimeout(() => Livewire.dispatch('openRegistrationModal'), 100);"
                                       class="btn btn-sm font-weight-bold"
                                       style="background-color: #064e3b; color: #fff; border-radius: 20px; padding: 6px 18px; font-size: 0.85rem; font-family: 'Inter', sans-serif;">
                                        Register here
                                    </a>
                                </div>
                            </form>
                            
                            <div class="mt-auto text-center">
                                <p class="text-muted small mb-0">&copy; {{ date('Y') }} ABU Endowment Foundation. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    @script
    <script>
        // Make functions globally accessible
        window.initGoogleLoginButton = function() {
            const buttonId = 'googleBtnLogin-login';
            const buttonElement = document.getElementById(buttonId);
            
            if (!buttonElement) {
                console.warn('Google button element not found:', buttonId);
                return;
            }

            // Wait for Google script to load
            let retries = 0;
            function tryInit() {
                if (window.google && window.google.accounts && window.google.accounts.id) {
                    console.log('Initializing Google Sign-In button');
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
                            text: "signin_with" 
                        }
                    );
                    console.log('Google Sign-In button rendered');
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

        // Handle Google credential response - make it global
        window.handleGoogleCredentialResponse = function(response) {
            console.log('Google credential received, processing...');
            
            // Use fetch to avoid server-side deadlock
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

        // Watch for modal show state and initialize button
        $wire.watch('show', (value) => {
            if (value) {
                console.log('Login modal opened, initializing Google button');
                setTimeout(() => window.initGoogleLoginButton(), 300);
            }
        });

        // Also listen for the event
        Livewire.on('initGoogleLogin', () => {
            console.log('Received initGoogleLogin event');
            setTimeout(() => window.initGoogleLoginButton(), 100);
        });

        // Initialize on component mount if modal is already shown
        if ($wire.show) {
            setTimeout(() => window.initGoogleLoginButton(), 500);
        }
    </script>
    @endscript
</div>
