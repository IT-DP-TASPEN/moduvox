<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Inventaris - PT Moduvox Tech ID</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="antialiased text-gray-800">

    <!-- Header -->
    <div class="bg-blue-600 shadow-md rounded-b-3xl pb-6 pt-8 px-6 mb-[-2rem] relative z-10">
        <div class="flex items-center justify-center mb-6">
            <div class="bg-white p-3 rounded-xl shadow-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-amber-500 text-2xl"></i>
                <span class="font-bold text-slate-800 text-xl tracking-tight">MODUVOX</span>
            </div>
        </div>
        <div class="text-center text-white">
            <h2 class="text-2xl font-bold mb-1">{{ $inventaris->nama_aset }}</h2>
            <p class="text-blue-100 font-mono text-sm">{{ $inventaris->rekening }}</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-md w-full mx-auto relative z-10 p-4">
        <!-- Asset Card -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl overflow-hidden border border-white/50 p-5 mb-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-500">Status Aset</span>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-{{ $inventaris->status->color() }}-100 text-{{ $inventaris->status->color() }}-700">
                {{ $inventaris->status->label() }}
            </span>
        </div>

        <!-- Details Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-5 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800 text-lg flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i> Informasi Dasar
                </h3>
            </div>
            <div class="p-5 space-y-4">
                
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Merk / Tipe</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $inventaris->merk ?: '-' }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Golongan</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $inventaris->golongan->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Jenis</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $inventaris->jenis->nama ?? '-' }}</p>
                    </div>
                </div>

                <hr class="border-gray-50">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Kantor / Cabang</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $inventaris->kantor->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Lokasi Gedung</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $inventaris->lokasi->nama ?? '-' }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Ruangan</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $inventaris->ruangan->nama ?? '-' }}</p>
                </div>
                
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Tahun Perolehan</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $inventaris->tgl_perolehan ? $inventaris->tgl_perolehan->format('Y') : '-' }}</p>
                </div>

            </div>
        </div>
        
        <div class="text-center pb-8">
            <p class="text-xs text-gray-400">&copy; {{ date('Y') }} PT Moduvox Tech ID. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
