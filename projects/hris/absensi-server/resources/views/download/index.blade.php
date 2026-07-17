<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download HRIS Mobile - Moduvox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-brand { background: linear-gradient(135deg, #004A99 0%, #003366 100%); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col md:flex-row items-center justify-center p-6 gap-12">
    <!-- Mobile Mockup Section -->
    <div class="hidden md:flex flex-col items-center justify-center w-full max-w-sm">
        <img src="{{ asset('assets/images/mobile_mockup.png') }}" alt="HRIS Mobile App Mockup" class="w-full drop-shadow-2xl rounded-3xl transform hover:scale-105 transition-transform duration-500">
        <div class="mt-8 text-center space-y-2">
            <h3 class="text-2xl font-bold text-slate-800">Moduvox Mobile</h3>
            <p class="text-slate-500">Kelola absensi dan pengajuan lebih mudah dari genggaman Anda.</p>
        </div>
    </div>

    <!-- Download Card -->
    <div class="max-w-md w-full bg-white rounded-[40px] shadow-2xl shadow-blue-100 overflow-hidden border border-slate-100">
        <div class="bg-brand p-12 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10 flex flex-col items-center">
                <!-- Using UI Avatars for generic logo if local logo is missing, or generic app icon -->
                <img src="https://ui-avatars.com/api/?name=Moduvox&background=fff&color=1e3a8a&rounded=true&bold=true&size=128" alt="Logo" class="h-20 w-20 mx-auto mb-4 drop-shadow-lg rounded-2xl">
                <h1 class="text-3xl font-bold text-white mb-2">HRIS Mobile Apps</h1>
            </div>
        </div>

        <div class="p-10 space-y-8">
            <div class="space-y-4">
                <h2 class="font-bold text-slate-800 text-lg">Fitur Utama:</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">✓</div>
                        <p class="text-slate-600 text-sm">Absensi Selfie & Lokasi (Presisi GPS)</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">✓</div>
                        <p class="text-slate-600 text-sm">Pengajuan Cuti, Izin, & Lembur Digital</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">✓</div>
                        <p class="text-slate-600 text-sm">Penilaian KPI Staf & Monitoring Hasil</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600 shrink-0">✓</div>
                        <p class="text-slate-600 text-sm">Riwayat Kehadiran & Slip Gaji Online</p>
                    </li>
                </ul>
            </div>

            <div class="pt-4 space-y-4">
                <!-- Tombol Android -->
                <a href="{{ $settings['android_download_url'] ?? '#' }}" target="_blank"
                   class="flex items-center justify-center gap-3 w-full bg-brand text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-blue-200 hover:scale-[1.02] transition-transform active:scale-95">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/d/d7/Android_robot.svg" class="w-6 h-6" alt="Android">
                    Download for Android
                </a>

                <!-- Tombol iOS (Placeholder) -->
                <a href="{{ $settings['ios_download_url'] ?? '#' }}" target="_blank"
                   class="flex items-center justify-center gap-3 w-full bg-slate-800 text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-slate-200 hover:scale-[1.02] transition-transform active:scale-95">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" class="w-6 h-6 invert" alt="iOS">
                    Download for iOS
                </a>

                <p class="text-center text-slate-400 text-[10px] mt-4 italic">
                    *Versi Terupdate: {{ $settings['app_version'] ?? 'v1.0.0' }}
                </p>
            </div>
        </div>
        
        <div class="bg-slate-50 p-6 text-center border-t border-slate-100">
            <p class="text-slate-500 text-[10px] font-semibold">© {{ date('Y') }} IT - Moduvox</p>
        </div>
    </div>
</body>
</html>
