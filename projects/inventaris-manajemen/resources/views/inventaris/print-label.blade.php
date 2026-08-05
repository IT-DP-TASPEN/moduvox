<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Inventaris</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $t = $templateConfig ?? config('label.templates.A4_STANDARD');
        $cols = $t['columns'] ?? 2;
        $rows = $t['rows'] ?? 4;
        $perPage = $cols * $rows;
        $pages = $assets->chunk($perPage);
    @endphp
    <style>
        @media print {
            @page {
                size: {{ $t['paper_width'] }}mm {{ $t['paper_height'] }}mm;
                margin: 0; /* Let CSS handle margins to ensure precision */
            }
            body {
                background-color: white !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
        
        body {
            background-color: #f3f4f6;
            color: #000;
            font-family: Arial, sans-serif;
        }

        .sheet {
            width: {{ $t['paper_width'] }}mm;
            height: {{ $t['paper_height'] }}mm;
            padding-top: {{ $t['margin_top'] }}mm;
            padding-left: {{ $t['margin_left'] }}mm;
            margin: 0 auto;
            background: white;
            box-sizing: border-box;
            position: relative;
            page-break-after: always;
            /* Flex layout for grid */
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: {{ $t['gap_y'] ?? 0 }}mm {{ $t['gap_x'] ?? 0 }}mm;
        }
        
        .sheet:last-child {
            page-break-after: auto;
        }

        /* Preview styles in browser */
        @media screen {
            .sheet {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                margin-bottom: 2rem;
                border-radius: 4px;
            }
        }

        .label-container {
            width: {{ $t['label_width'] }}mm;
            height: {{ $t['label_height'] }}mm;
            border: 1px dashed #cbd5e1;
            padding: 4mm;
            box-sizing: border-box;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden; /* Prevent content overflowing the fixed size */
        }
        
        /* For real stickers, we might not want the dashed border printed */
        @media print {
            .label-container {
                border-color: transparent; /* Disable border when actually printing */
            }
        }

        .label-header {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #000;
            padding-bottom: 1.5mm;
            margin-bottom: 1.5mm;
        }

        .label-body {
            display: flex;
            gap: 2mm;
            align-items: flex-start;
            height: 100%;
        }

        .label-info {
            flex: 1;
            font-size: 7.5pt;
            line-height: 1.3;
            min-width: 0; /* Fix flexbox shrink issue */
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .label-qr {
            width: 17mm; /* Fixed size to ensure scanability */
            height: 17mm;
            flex-shrink: 0;
            align-self: center; /* Vertically center the QR code */
        }
        
        .label-qr svg {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body class="min-h-screen">
    
    <div class="no-print max-w-4xl mx-auto my-8 bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex justify-between items-center">
        <div>
            <h1 class="font-bold text-lg text-gray-800">Preview Cetak Label ({{ $assets->count() }} Aset)</h1>
            <p class="text-sm text-gray-500 mt-1">Template: <strong>{{ $t['name'] }}</strong> (Ukuran: {{ $t['description'] }})</p>
            <p class="text-xs text-red-500 mt-1"><i class="fa-solid fa-triangle-exclamation"></i> Matikan opsi "Headers and Footers" dan atur "Scale: 100% / Default" pada jendela cetak browser.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.close()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">Tutup</button>
            <button onclick="window.print()" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Sekarang
            </button>
        </div>
    </div>

    <!-- Render Pages -->
    @foreach($pages as $pageAssets)
    <div class="sheet">
        @foreach($pageAssets as $asset)
        <div class="label-container">
            <div class="label-header">
                <div class="font-bold text-[8pt] text-gray-900 w-full text-center tracking-tight border border-black px-1 uppercase">PT Moduvox Tech ID</div>
            </div>
            
            <div class="label-body">
                <div class="label-info">
                    <div class="font-bold text-[8.5pt] mb-1 truncate">{{ $asset->nama_aset }}</div>
                    <div class="truncate"><span class="font-semibold">Kode:</span> <span class="font-mono">{{ $asset->rekening }}</span></div>
                    <div class="truncate"><span class="font-semibold">Beli:</span> {{ $asset->tgl_perolehan ? $asset->tgl_perolehan->format('m/Y') : '-' }}</div>
                    <div class="truncate"><span class="font-semibold">Cbg:</span> {{ $asset->kantor->kode ?? '-' }} ({{ $asset->ruangan->nama ?? '-' }})</div>
                </div>
                
                <div class="label-qr">
                    <!-- Ukuran QR fix 65px untuk menjaga resolusi tetap tajam -->
                    {!! QrCode::size(65)->margin(0)->generate(route('inventaris.scan', $asset->id)) !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

</body>
</html>
