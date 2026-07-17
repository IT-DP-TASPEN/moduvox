@extends('admin.layout')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengaturan Aplikasi Mobile</h1>
            <p class="text-slate-500 text-sm">Kelola link download APK (Google Drive) dan versi aplikasi.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-100 text-green-700 rounded-2xl border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <form action="{{ route('admin.app-settings.update') }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Link Download Android (Google Drive)</label>
                    <input type="text" name="settings[android_download_url]" value="{{ $settings['android_download_url'] ?? '#' }}" 
                           placeholder="https://drive.google.com/..."
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
                    <p class="text-[10px] text-slate-400">Pastikan link Google Drive sudah diatur ke "Anyone with the link".</p>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Link Download iOS (TestFlight/AppStore)</label>
                    <input type="text" name="settings[ios_download_url]" value="{{ $settings['ios_download_url'] ?? '#' }}" 
                           placeholder="https://apps.apple.com/..."
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase">Versi Aplikasi Saat Ini</label>
                    <input type="text" name="settings[app_version]" value="{{ $settings['app_version'] ?? '1.0.0' }}" 
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-brand-blue outline-none transition-all">
                    <p class="text-[10px] text-slate-400">Gunakan format v1.0.0</p>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-10 py-4 bg-brand-blue text-white font-bold rounded-2xl shadow-lg shadow-blue-100 hover:opacity-90 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
        <div class="flex gap-4">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-blue-800">Petunjuk Download</h4>
                <p class="text-blue-700 text-xs mt-1 leading-relaxed">
                    Link yang Anda simpan di sini akan muncul secara otomatis di halaman: <br>
                    <a href="{{ route('download.apk') }}" target="_blank" class="font-bold underline text-blue-900">{{ route('download.apk') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
