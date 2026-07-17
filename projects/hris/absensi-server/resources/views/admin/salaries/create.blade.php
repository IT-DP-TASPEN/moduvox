@extends('admin.layout')

@section('header', 'Tambah Data Gaji')

@section('content')
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden max-w-5xl">
    <form action="{{ route('admin.salaries.store') }}" method="POST" id="salaryForm" class="p-8">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Kolom Input -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-slate-50 p-6 rounded-3xl space-y-4">
                    <h4 class="font-bold text-slate-800 text-sm">Variabel Bulanan</h4>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Karyawan</label>
                        <select name="user_id" id="user_id" class="w-full bg-white border-none rounded-xl text-sm font-semibold px-4 py-3 focus:ring-2 focus:ring-brand-blue" required>
                            <option value="">Pilih Karyawan</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bulan</label>
                            <select name="month" id="month" class="w-full bg-white border-none rounded-xl text-sm font-semibold px-4 py-3 focus:ring-2 focus:ring-brand-blue" required>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tahun</label>
                            <select name="year" id="year" class="w-full bg-white border-none rounded-xl text-sm font-semibold px-4 py-3 focus:ring-2 focus:ring-brand-blue" required>
                                @foreach(range(now()->year - 1, now()->year + 1) as $y)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Manual Pajak (Opsional)</label>
                        <input type="number" name="income_tax" id="income_tax" class="w-full bg-white border-none rounded-xl text-sm font-semibold px-4 py-3 focus:ring-2 focus:ring-brand-blue" placeholder="Auto" value="0">
                    </div>
                </div>
                
                <button type="button" id="btnCalculate" class="w-full bg-brand-orange text-white py-4 rounded-2xl font-bold shadow-lg shadow-orange-100 hover:opacity-90 transition-all">
                    Hitung Otomatis
                </button>
            </div>

            <!-- Kolom Preview Perhitungan -->
            <div class="lg:col-span-2">
                <div id="previewContainer" class="hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pendapatan -->
                        <div class="border border-slate-100 rounded-3xl p-6">
                            <h4 class="font-bold text-brand-blue border-b pb-2 mb-4">Rincian Pendapatan</h4>
                            <table class="w-full text-sm">
                                <tr class="border-b border-slate-50"><td class="py-2 text-slate-500">Gaji Pokok</td><td class="py-2 text-right font-bold" id="p_basic_salary">0</td></tr>
                                <tr class="border-b border-slate-50"><td class="py-2 text-slate-500">Tunjangan Jabatan</td><td class="py-2 text-right font-bold" id="p_position_allowance">0</td></tr>
                                <tr class="border-b border-slate-50"><td class="py-2 text-slate-500">Tunjangan Kinerja</td><td class="py-2 text-right font-bold" id="p_performance_allowance">0</td></tr>
                                <tr class="border-b border-slate-50"><td class="py-2 text-slate-500">Lembur & Makan</td><td class="py-2 text-right font-bold" id="p_overtime_total">0</td></tr>
                                <tr class="border-b border-slate-50"><td class="py-2 text-slate-500">Tunjangan Pajak</td><td class="py-2 text-right font-bold" id="p_tax_allowance">0</td></tr>
                                <tbody id="dynamic_earnings_rows"></tbody>
                                <tr class="bg-slate-50 font-black"><td class="py-3 px-2">TOTAL PENDAPATAN</td><td class="py-3 px-2 text-right text-brand-blue" id="p_total_earnings">0</td></tr>
                            </table>
                        </div>

                        <!-- Potongan -->
                        <div class="border border-slate-100 rounded-3xl p-6">
                            <h4 class="font-bold text-red-600 border-b pb-2 mb-4">Rincian Potongan</h4>
                            <table class="w-full text-sm">
                                <tr class="border-b border-slate-50"><td class="py-2 text-slate-500">Pajak (PPh 21)</td><td class="py-2 text-right font-bold" id="p_income_tax">0</td></tr>
                                <tbody id="dynamic_deduction_rows"></tbody>
                                <tr class="bg-slate-50 font-black"><td class="py-3 px-2">TOTAL POTONGAN</td><td class="py-3 px-2 text-right text-red-600" id="p_total_deductions">0</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="bg-brand-blue text-white p-8 rounded-3xl flex justify-between items-center shadow-xl shadow-blue-100">
                        <div>
                            <p class="text-xs font-bold opacity-70 uppercase tracking-widest">Penghasilan Bersih (THP)</p>
                            <h2 class="text-4xl font-black mt-1" id="p_net_salary">Rp 0</h2>
                        </div>
                        <button type="submit" class="bg-white text-brand-blue px-10 py-4 rounded-2xl font-black hover:bg-slate-50 transition-all">
                            Simpan & Publish
                        </button>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 text-center">Ditanggung Perusahaan (Non-THP)</p>
                        <div id="dynamic_company_rows" class="grid grid-cols-3 gap-4 text-center"></div>
                    </div>
                </div>

                <div id="emptyState" class="h-full border-2 border-dashed border-slate-100 rounded-3xl flex flex-col items-center justify-center p-20 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-400">Pilih Karyawan & Klik Hitung</h3>
                    <p class="text-sm text-slate-300 mt-1 max-w-xs">Sistem akan menghitung gaji secara otomatis berdasarkan data master & rumus slip gaji standar.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('btnCalculate').addEventListener('click', function() {
        const userId = document.getElementById('user_id').value;
        if (!userId) {
            alert('Pilih karyawan terlebih dahulu');
            return;
        }

        const formData = {
            user_id: userId,
            month: document.getElementById('month').value,
            year: document.getElementById('year').value,
            income_tax: document.getElementById('income_tax').value,
            _token: '{{ csrf_token() }}'
        };

        fetch('{{ route("admin.salaries.calculate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('previewContainer').classList.remove('hidden');

            const format = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);

            document.getElementById('p_basic_salary').innerText = format(data.basic_salary);
            document.getElementById('p_position_allowance').innerText = format(data.position_allowance);
            document.getElementById('p_performance_allowance').innerText = format(data.performance_allowance);
            document.getElementById('p_overtime_total').innerText = format(parseFloat(data.overtime_pay) + parseFloat(data.overtime_meal_pay));
            document.getElementById('p_tax_allowance').innerText = format(data.tax_allowance);
            document.getElementById('p_total_earnings').innerText = format(data.total_earnings);

            document.getElementById('p_income_tax').innerText = format(data.income_tax);
            document.getElementById('p_total_deductions').innerText = format(data.total_deductions);

            document.getElementById('p_net_salary').innerText = format(data.net_salary);

            // Parse dynamic_components JSON
            let components = [];
            try {
                components = JSON.parse(data.dynamic_components || '[]');
            } catch(e) { components = []; }

            // Render dynamic earning rows
            const earningsContainer = document.getElementById('dynamic_earnings_rows');
            earningsContainer.innerHTML = '';
            components.filter(c => c.category === 'earning').forEach(c => {
                earningsContainer.innerHTML += `<tr class="border-b border-slate-50"><td class="py-2 text-slate-500">${c.name}</td><td class="py-2 text-right font-bold">${format(c.amount)}</td></tr>`;
            });

            // Render dynamic deduction rows
            const deductionsContainer = document.getElementById('dynamic_deduction_rows');
            deductionsContainer.innerHTML = '';
            components.filter(c => c.category === 'deduction').forEach(c => {
                deductionsContainer.innerHTML += `<tr class="border-b border-slate-50"><td class="py-2 text-slate-500">${c.name}</td><td class="py-2 text-right font-bold">${format(c.amount)}</td></tr>`;
            });

            // Render dynamic company paid rows
            const companyContainer = document.getElementById('dynamic_company_rows');
            companyContainer.innerHTML = '';
            components.filter(c => c.category === 'company_paid').forEach(c => {
                companyContainer.innerHTML += `<div><p class="text-[10px] text-slate-400">${c.name}</p><p class="text-xs font-bold text-slate-600">${format(c.amount)}</p></div>`;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghitung gaji.');
        });
    });
</script>
@endsection
