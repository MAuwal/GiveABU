<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $project->project_title }} - ABU Endowment</title>
    <meta name="description" content="{{ Str::limit($project->project_description, 160) }}">
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
    
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    @livewireStyles
</head>

<body>
    <!-- Header -->
    @livewire('home.header-pages')
    
    @livewire('home.auth-modal')
    
    <!-- Project Content -->
    <div class="pt-5 mt-5">
        @livewire('home.project-contents', ['project' => $project])
    </div>

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
    
    <style>
        /* Mobile Menu Customization */
        .slicknav_menu {
            background: transparent;
            padding: 0;
            margin-top: 10px;
        }
        .slicknav_btn {
            background-color: transparent;
            border: 1px solid #fff;
            border-radius: 5px;
            margin-top: 5px;
        }
        .slicknav_icon-bar {
            background-color: #fff !important;
            height: 2px !important;
            margin: 5px auto !important;
        }
        .slicknav_nav {
            background: #fff;
            margin-top: 15px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .slicknav_nav a {
            color: #333 !important;
            margin: 0;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .slicknav_nav a:hover {
            color: #3CC78F !important;
            background: #f9f9f9;
        }
        .slicknav_nav .slicknav_row:hover {
            background: #f9f9f9;
            color: #3CC78F;
        }
        .slicknav_nav .slicknav_arrow {
            color: #999;
        }
    </style>
    <!-- Toast Container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.style.cssText = `
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                margin-bottom: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 10px;
                min-width: 300px;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            
            toast.innerHTML = `
                <i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span style="font-weight: 500;">${message}</span>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            });
            
            // Remove after 5 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-toast', (data) => {
                const toastData = Array.isArray(data) ? data[0] : data;
                showToast(toastData.message, toastData.type);
            });
        });

        // Check for session flash messages
        @if(session('message'))
            document.addEventListener('DOMContentLoaded', () => {
                showToast("{{ session('message') }}", 'success');
            });
        @endif
        
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', () => {
                showToast("{{ session('error') }}", 'error');
            });
        @endif
    </script>
</body>
</html>
