<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ABU Endowment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }
        .skew-image-container {
            clip-path: polygon(0 0, 100% 0, 90% 100%, 0% 100%);
        }
        @media (max-width: 1024px) {
            .skew-image-container {
                clip-path: none;
                height: 40vh;
                clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-white flex flex-col lg:flex-row">
    
    <!-- Left Side: Image (Covers half/part of the page) -->
    <div class="lg:w-7/12 w-full relative skew-image-container h-64 lg:h-screen bg-gray-900 overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/ABU SKY IMAGE.jpg') }}');"></div>
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#064e3b]/90 to-[#10b981]/80 mix-blend-multiply"></div>
        
        <!-- Content over image -->
        <div class="absolute inset-0 flex flex-col justify-center px-12 text-white z-10">
            <div class="max-w-xl">
                <img src="{{ asset('abu_logo.png') }}" alt="ABU Logo" class="h-20 mb-8 brightness-0 invert opacity-90">
                <h1 class="text-4xl lg:text-6xl font-bold font-playfair mb-6 leading-tight">
                    Legacy & <br>Impact
                </h1>
                <p class="text-lg lg:text-xl text-gray-100 font-light leading-relaxed">
                    Empowering the future through the Ahmadu Bello University Endowment Foundation. 
                    Manage contributions, projects, and donor relations efficiently.
                </p>
            </div>
        </div>

        <!-- Decorative Circles -->
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/4 right-10 w-32 h-32 bg-[#10b981]/20 rounded-full blur-2xl"></div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="lg:w-5/12 w-full flex items-center justify-center p-8 lg:p-16 bg-white">
        <div class="w-full max-w-md">
            <div class="text-center lg:text-left mb-10">
                <h2 class="text-3xl font-bold text-[#064e3b] font-playfair mb-2">Welcome Back</h2>
                <p class="text-gray-500">Please enter your details to sign in.</p>
            </div>

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm" role="alert">
                        <p class="font-bold text-sm">Authentication Error</p>
                        <ul class="list-disc list-inside text-sm mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Email Field -->
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-gray-700 block">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 group-focus-within:text-[#10b981] transition-colors"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#10b981]/20 focus:border-[#10b981] transition-all duration-200"
                               placeholder="admin@abu.edu.ng">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-sm font-medium text-gray-700 block">Password</label>
                        <a href="#" class="text-xs font-medium text-[#10b981] hover:text-[#064e3b]">Forgot password?</a>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400 group-focus-within:text-[#10b981] transition-colors"></i>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#10b981]/20 focus:border-[#10b981] transition-all duration-200"
                               placeholder="••••••••">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-[#064e3b] focus:ring-[#10b981] border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-600">
                        Remember me for 30 days
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full flex justify-center items-center py-3.5 px-4 bg-[#064e3b] hover:bg-[#04382b] text-white font-semibold rounded-xl shadow-lg shadow-[#064e3b]/30 hover:shadow-[#064e3b]/50 transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#064e3b]">
                    Sign In
                    <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </button>

                <!-- Back to Home -->
                <div class="text-center mt-8">
                    <a href="/" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#064e3b] transition-colors">
                        <i class="fas fa-long-arrow-alt-left mr-2"></i>
                        Back to Website
                    </a>
                </div>
            </form>
            
            <div class="mt-10 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} ABU Endowment Foundation. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>