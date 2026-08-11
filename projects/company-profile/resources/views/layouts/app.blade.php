<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    @php
        $metaTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: 'PT Moduvox Tech ID | Kontraktor & Jasa Konstruksi Infrastruktur';
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: 'PT Moduvox Tech ID - Solusi Konstruksi dan Jasa Terpercaya di Indonesia';
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
    @endphp

    <title>{{ $metaTitle }} | Industrial EPC & Engineering</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="description" content="{{ $metaDescription }}"/>
    <meta name="robots" content="@yield('meta_robots', 'index,follow')"/>
    <link rel="canonical" href="{{ $canonicalUrl }}"/>

    <meta property="og:locale" content="id_ID"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content="{{ $metaTitle }}"/>
    <meta property="og:description" content="{{ $metaDescription }}"/>
    <meta property="og:url" content="{{ $canonicalUrl }}"/>
    <meta property="og:site_name" content="PT Moduvox Tech ID"/>

    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:title" content="{{ $metaTitle }}"/>
    <meta name="twitter:description" content="{{ $metaDescription }}"/>
    @php
        $siteImage = trim($__env->yieldContent('og_image'))
            ?: (file_exists(public_path('og.png')) ? asset('og.png') : asset('dap.png'));
    @endphp
    <meta property="og:image" content="{{ $siteImage }}"/>
    <meta name="twitter:image" content="{{ $siteImage }}"/>

    <link rel="icon" type="image/png" href="{{ asset('dap.png') }}"/>
    <link rel="apple-touch-icon" href="{{ asset('dap.png') }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- Fallback Assets --}}
    @if (file_exists(public_path('mix-manifest.json')))
        <link rel="stylesheet" href="{{ mix('css/app.css') }}?v={{ time() }}"/>
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}"/>
    @endif

    <style>
        /* INDUSTRIAL DESIGN SYSTEM OVERRIDE */
        :root {
            --primary: #B91C1C;
            --primary-dark: #991B1B;
            --surface: #0F172A;
            --background: #F1F5F9;
            --on-background: #0F172A;
            --on-surface-variant: rgba(15, 23, 42, 0.6);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--on-background);
            margin: 0;
        }

        .font-headline { font-family: 'Manrope', sans-serif; }

        .material-symbols-outlined { 
            font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; 
            vertical-align:middle; 
        }
        
        .industrial-grid {
            background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .transition-industrial {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Essential Grid Fix for Hostinger */
        @media (min-width: 1024px) {
            .lg\:grid-cols-12 { grid-template-columns: repeat(12, minmax(0, 1fr)); }
            .lg\:col-span-8 { grid-column: span 8 / span 8; }
            .lg\:col-span-4 { grid-column: span 4 / span 4; }
        }

        @keyframes scroll { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }
        
        [x-cloak] { display: none !important; }
    </style>
    @yield('head')
</head>
<body class="font-body selection:bg-primary selection:text-on-primary">

    @include('partials.navbar')
    <main>@yield('content')</main>
    @include('partials.footer')

    @php
        $waLink = setting('contact', 'contact_whatsapp', '');
    @endphp
    @if (filled($waLink))
        <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" aria-label="Chat via WhatsApp"
           class="fixed bottom-8 right-8 z-[100] bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:scale-110 transition-transform flex items-center justify-center" style="width: 60px; height: 60px; text-decoration: none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.94 3.659 1.437 5.634 1.437h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
    @endif

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    @if (file_exists(public_path('mix-manifest.json')))
        <script src="{{ mix('js/app.js') }}" defer></script>
    @else
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif
    @yield('scripts')
</body>
</html>

