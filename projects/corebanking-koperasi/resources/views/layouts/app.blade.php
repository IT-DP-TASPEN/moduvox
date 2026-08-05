<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Core Banking Koperasi | PT Moduvox Tech ID' }}</title>
    

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary-fixed": "#d0e4ff",
                        "on-tertiary": "#ffffff",
                        "surface-dim": "#dad9dc",
                        "on-error-container": "#93000a",
                        "surface-bright": "#faf9fb",
                        "tertiary-fixed-dim": "#e9c349",
                        "on-surface": "#1a1c1e",
                        "secondary-fixed": "#cde5ff",
                        "on-secondary-fixed": "#001d32",
                        "on-secondary-container": "#48647d",
                        "on-primary-fixed-variant": "#2e4964",
                        "primary-fixed-dim": "#aec9ea",
                        "secondary-fixed-dim": "#adcae6",
                        "outline": "#73777e",
                        "surface-tint": "#46607d",
                        "outline-variant": "#c3c6ce",
                        "on-primary-fixed": "#001d35",
                        "on-secondary-fixed-variant": "#2d4961",
                        "surface-container-low": "#f4f3f6",
                        "error-container": "#ffdad6",
                        "error": "#ba1a1a",
                        "on-background": "#1a1c1e",
                        "secondary-container": "#c3e0fe",
                        "tertiary-fixed": "#ffe088",
                        "tertiary-container": "#cca830",
                        "inverse-primary": "#aec9ea",
                        "inverse-surface": "#2f3032",
                        "inverse-on-surface": "#f1f0f3",
                        "surface-container-highest": "#e3e2e4",
                        "primary": "#00162a",
                        "primary-container": "#0d2b45",
                        "surface-container-high": "#e9e8ea",
                        "on-tertiary-container": "#4f3e00",
                        "on-error": "#ffffff",
                        "surface-variant": "#e3e2e4",
                        "surface-container": "#efedf0",
                        "on-secondary": "#ffffff",
                        "on-tertiary-fixed": "#241a00",
                        "on-primary-container": "#7893b2",
                        "on-primary": "#ffffff",
                        "tertiary": "#735c00",
                        "surface": "#faf9fb",
                        "surface-container-lowest": "#ffffff",
                        "background": "#faf9fb",
                        "secondary": "#45617a",
                        "on-surface-variant": "#43474d",
                        "on-tertiary-fixed-variant": "#574500"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .pearl-gradient {
            background: linear-gradient(145deg, #00162a 0%, #062035 45%, #0d2b45 100%);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
        }

        /* Custom Scrollbars */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.2);
        }

        /* Specifically for the Dark Sidebar */
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 3px;
        }

        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Print Styles */
        @media print {

            aside,
            nav,
            header,
            .print-hidden,
            #toast-container,
            button,
            .x-header-actions {
                display: none !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                width: 100% !important;
            }

            .bg-surface {
                background-color: white !important;
            }

            .rounded-[2.5rem] {
                border-radius: 0 !important;
            }

            .shadow-sm,
            .shadow-xl {
                shadow: none !important;
                box-shadow: none !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important;
            }

            th {
                background-color: #f8fafc !important;
                color: #0f172a !important;
                font-weight: 900 !important;
                text-transform: uppercase !important;
                padding: 16px 12px !important;
                border-bottom: 2px solid #0f172a !important;
            }

            td {
                border-bottom: 1px solid #e2e8f0 !important;
                padding: 14px 12px !important;
                color: #1e293b !important;
            }

            .bg-surface\/30 {
                background-color: #f1f5f9 !important;
                font-weight: bold !important;
            }

            .text-right {
                text-align: right !important;
            }

            tfoot {
                background-color: #f8fafc !important;
                border-top: 3px solid #0f172a !important;
            }

            tfoot td {
                padding: 20px 12px !important;
            }

            @page {
                margin: 2cm;
            }
        }
    </style>
    @livewireStyles
</head>

<body
    class="bg-surface font-body text-on-background min-h-screen {{ session()->has('original_impersonator_id') ? 'pt-14' : '' }}">
    @if(session()->has('original_impersonator_id'))
        <div
            class="bg-amber-400 text-amber-900 px-8 h-14 flex items-center justify-between shadow-md z-[100] fixed top-0 left-0 right-0">
            <div class="flex items-center space-x-3">
                <span class="material-symbols-outlined font-bold animate-pulse">visibility</span>
                <span class="text-xs font-black uppercase tracking-widest">
                    Mode Menyamar (Impersonate): Anda bertindak sebagai <span
                        class="bg-amber-900/10 px-2 py-0.5 rounded font-bold">{{ auth()->user()->name }}
                        ({{ auth()->user()->getRoleNames()->first() ?? 'Murni' }})</span>
                </span>
            </div>
            <a href="{{ route('impersonate.leave') }}"
                class="bg-amber-900 text-amber-400 hover:bg-amber-800 transition-all px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg flex items-center space-x-2">
                <span class="material-symbols-outlined text-sm">logout</span>
                <span>Kembali ke Admin</span>
            </a>
        </div>
    @endif

    @auth
        <div class="flex min-h-screen bg-slate-50">
            <!-- Sidebar Component -->
            <div class="w-72 shrink-0 z-20">
                <div
                    class="fixed {{ session()->has('original_impersonator_id') ? 'top-14 h-[calc(100vh-56px)]' : 'top-0 h-screen' }} left-0 w-72">
                    <x-sidebar />
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-grow min-w-0">
                {{ $slot }}
            </main>
        </div>
    @else
        {{ $slot }}
    @endauth

    <div id="toast-container" class="fixed bottom-10 right-10 z-[100] space-y-4"></div>

    <template id="toast-template">
        <div
            class="bg-slate-900 border border-white/10 text-white px-6 py-4 rounded-3xl shadow-2xl flex items-center space-x-4 animate-slide-up backdrop-blur-xl bg-opacity-90 min-w-[300px]">
            <div class="w-10 h-10 rounded-2xl bg-tertiary-fixed text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined font-icon">notifications_active</span>
            </div>
            <div class="flex-grow">
                <p class="text-[10px] uppercase tracking-widest font-black text-tertiary-fixed mb-0.5">Notifikasi Sistem
                </p>
                <p class="text-xs font-bold leading-tight opacity-90 message-slot"></p>
            </div>
            <button class="opacity-50 hover:opacity-100 transition-opacity close-toast">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    </template>

    <script>
        function showToast(message) {
            const container = document.getElementById('toast-container');
            const template = document.getElementById('toast-template').content.cloneNode(true);
            const toast = template.firstElementChild;

            toast.querySelector('.message-slot').textContent = message;

            toast.querySelector('.close-toast').addEventListener('click', () => {
                toast.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => toast.remove(), 300);
            });

            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('opacity-0', 'translate-y-4');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        window.addEventListener('approval-created', event => {
            showToast('Ada permohonan baru yang memerlukan persetujuan Anda.');
        });
    </script>

    @livewireScripts
</body>

</html>
