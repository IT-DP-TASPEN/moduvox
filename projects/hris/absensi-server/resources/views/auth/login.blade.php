<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HRIS Moduvox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <img src="https://ui-avatars.com/api/?name=Moduvox&background=1e3a8a&color=fff&rounded=true&bold=true&size=128" alt="Logo" class="h-14 mx-auto mb-4 drop-shadow-lg" onerror="this.style.display='none'">
            <h1 class="text-2xl font-bold text-white">HRIS Admin Panel</h1>
            <p class="text-blue-300 text-sm mt-1">Moduvox</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/10">
            @if($errors->any())
            <div class="bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-2xl mb-6 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-2xl mb-6 text-sm">
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="text-xs font-bold text-blue-200 uppercase tracking-wider ml-1 mb-2 block">Email / Username</label>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                            class="w-full px-5 py-4 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-blue-300/50 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all text-sm font-medium"
                            placeholder="Masukkan email atau username">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-blue-200 uppercase tracking-wider ml-1 mb-2 block">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-5 py-4 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-blue-300/50 focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all text-sm font-medium"
                            placeholder="Masukkan password">
                    </div>
                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-2xl font-bold text-sm hover:from-blue-500 hover:to-blue-400 transition-all shadow-lg shadow-blue-500/30 mt-2">
                        Masuk
                    </button>
                </div>
            </form>

            <!-- DEMO BYPASS BUTTON -->
            <form method="POST" action="{{ route('demo.login') }}" class="mt-4">
                @csrf
                <button type="submit"
                    class="w-full py-3 bg-white/20 border border-white/30 text-white rounded-2xl font-bold text-sm hover:bg-white/30 transition-all flex justify-center items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L5.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 014 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L8 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L5 10.274zm10 0l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L15 10.274z" clip-rule="evenodd" />
                    </svg>
                    Bypass Login (Demo Admin)
                </button>
            </form>
        </div>

        <p class="text-center text-blue-400/50 text-xs mt-6">&copy; {{ date('Y') }} Moduvox. All rights reserved.</p>
    </div>

</body>
</html>
