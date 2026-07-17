@extends('admin.layout')

@section('header', 'Pengaturan Payroll')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-10">
            <h3 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-4">
                <span class="w-10 h-10 rounded-2xl bg-brand-blue/5 text-brand-blue flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                Parameter Lembur & Insentif
            </h3>

            <form action="{{ route('admin.payroll-settings.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Rate Lembur per Jam (Tetap)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                            <input type="text" name="overtime_rate_permanent" value="{{ $settings->overtime_rate_permanent }}" class="rupiah-input w-full pl-14 pr-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Rate Lembur per Jam (Kontrak)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                            <input type="text" name="overtime_rate_contract" value="{{ $settings->overtime_rate_contract }}" class="rupiah-input w-full pl-14 pr-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Uang Makan Lembur (per hari lembur)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                            <input type="text" name="overtime_meal_allowance" value="{{ $settings->overtime_meal_allowance }}" class="rupiah-input w-full pl-14 pr-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Maksimal Jam Lembur (Kontrak)</label>
                        <div class="relative">
                            <input type="number" name="max_overtime_hours_contract" value="{{ $settings->max_overtime_hours_contract }}" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Jam</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Tanggal Penggajian Rutin</label>
                        <div class="relative">
                            <input type="number" name="payroll_day" value="{{ $settings->payroll_day }}" min="1" max="31" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">Tanggal</span>
                        </div>
                    </div>
                </div>

                <div class="pt-10 border-t border-slate-50">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-emerald-700">Komponen BPJS & Potongan Lainnya</h4>
                            <p class="text-sm text-emerald-600 mt-1 leading-relaxed">
                                Seluruh komponen BPJS, Moduvox Save, Premi Pensiun, dan tunjangan/potongan lainnya kini dikelola secara dinamis melalui menu 
                                <a href="{{ route('admin.global-allowances.index') }}" class="font-bold underline hover:text-emerald-800">Master Tunjangan Global</a>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-50 flex justify-end">
                    <button type="submit" class="bg-brand-blue text-white px-10 py-4 rounded-2xl font-bold hover:opacity-90 shadow-lg shadow-blue-100 transition-all flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-10">
            <h3 class="text-xl font-bold text-indigo-600 mb-8 flex items-center gap-4">
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
                Kenaikan Gaji Massal (Tahunan)
            </h3>

            <p class="text-sm text-slate-500 mb-6">Gunakan fitur ini untuk menaikkan seluruh nominal pada <b>Master Gaji Pokok</b> dan <b>Master Honorarium</b> secara persentase.</p>

            <form action="{{ route('admin.payroll-settings.bulk-increment') }}" method="POST" class="flex items-end gap-6" onsubmit="return confirm('Apakah Anda yakin ingin menaikkan seluruh Master Gaji? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                <div class="flex-1 space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Persentase Kenaikan (%)</label>
                    <div class="relative">
                        <input type="number" name="percentage" value="10" class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-indigo-500 transition-all font-bold text-slate-700" required>
                        <span class="absolute right-6 top-1/2 -translate-y-1/2 font-bold text-slate-400">%</span>
                    </div>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold hover:opacity-90 shadow-lg shadow-indigo-100 transition-all">
                    Terapkan Kenaikan
                </button>
            </form>
        </div>
    </div>

    <div class="mt-8 bg-brand-orange/10 border border-brand-orange/20 rounded-[2rem] p-8">
        <div class="flex gap-4">
            <div class="w-12 h-12 rounded-2xl bg-brand-orange text-white flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-brand-orange">Informasi Perhitungan</h4>
                <p class="text-sm text-brand-orange/80 mt-1 leading-relaxed">
                    Pengaturan ini akan digunakan sebagai dasar perhitungan otomatis pada menu <b>Tambah Gaji (Payroll)</b>. 
                    Untuk karyawan kontrak, sistem akan membatasi jam lembur yang dibayarkan sesuai dengan <b>Maksimal Jam Lembur</b> yang Anda tentukan di sini.
                </p>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        // Format angka ke Rupiah saat diketik
        $('.rupiah-input').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            $(this).val(formatRupiah(val));
        });

        // Format awal saat halaman dimuat
        $('.rupiah-input').each(function() {
            let val = $(this).val();
            // Jika ada desimal .00, hapus dulu agar tidak jadi jutaan
            if (val.includes('.')) {
                val = Math.floor(parseFloat(val)).toString();
            }
            $(this).val(formatRupiah(val));
        });

        // Bersihkan titik sebelum form dikirim
        $('form').on('submit', function() {
            $('.rupiah-input').each(function() {
                let val = $(this).val().replace(/\D/g, '');
                $(this).val(val); // Kirim angka murni
            });
        });

        function formatRupiah(angka) {
            if (!angka) return '';
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    });
</script>
@endpush
@endsection
