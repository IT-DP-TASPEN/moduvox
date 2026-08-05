<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventaris PT Moduvox Tech ID</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .login-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
        }
        .btn-login {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.35);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">

    {{-- Decorative Elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl float-animation"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-amber-600/5 rounded-full blur-3xl float-animation" style="animation-delay: 3s;"></div>
    </div>

    <div class="relative w-full max-w-md">
        {{-- Logo Section --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-500/20 text-amber-400 mb-4 drop-shadow-md">
                <i class="fa-solid fa-boxes-stacked text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">
                Inventaris <span class="text-amber-400">v2</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Sistem Manajemen & Penyusutan Aset</p>
        </div>

        {{-- Login Card --}}
        <div class="glass-card rounded-2xl shadow-2xl p-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Masuk ke Sistem</h2>
                <p class="text-sm text-gray-500 mt-1">Silakan masukkan kredensial Anda</p>
            </div>

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-envelope text-gray-400 text-sm"></i>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="admin@moduvox.id"
                            class="input-focus w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-amber-500 transition"
                            placeholder="nama@email.com"
                            required
                            autofocus
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fa-solid fa-lock text-gray-400 text-sm"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            value="password"
                            class="input-focus w-full pl-10 pr-12 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-amber-500 transition"
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i id="eye-icon" class="fa-solid fa-eye text-gray-400 text-sm hover:text-gray-600 transition"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login w-full py-2.5 text-white font-semibold rounded-xl text-sm">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>
                    Masuk
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-slate-500 text-xs mt-6">
            &copy; {{ date('Y') }} PT Moduvox Tech ID. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
