<div class="print-container hidden print:block">
    <img src="{{ asset('logo.png') }}" class="header-logo" alt="Logo">
    <p class="nomor_bilyet">Nomor : {{ $record->bilyet_number }}</p>

    <div class="container">
        <div>Nama</div>
        <div>:</div>
        <div>{{ $record->member->name }}</div>
        
        <div>Alamat</div>
        <div>:</div>
        <div class="alamat">{{ $record->member->address }}</div>
        
        <div>Uang Sejumlah</div>
        <div>:</div>
        <div>Rp {{ number_format($record->balance, 2, ',', '.') }}</div>
        
        <div>Terbilang</div>
        <div>:</div>
        <div>{{ ucwords(Helper::terbilang($record->balance)) }} Rupiah</div>
    </div>

    <div class="container-isi">
        <div class="head-isi">Jangka Waktu</div>
        <div class="head-isi">Realisasi</div>
        <div class="head-isi">Jatuh Tempo</div>
        <div class="head-isi">Rate</div>
        <div class="head-isi">Jasa Perbulan</div>
        
        <div class="head-isi">{{ $record->loan_term_months ?? '-' }} Bulan</div>
        <div class="head-isi">{{ $record->opened_at ? $record->opened_at->format('d-m-Y') : '-' }}</div>
        <div class="head-isi">{{ $record->maturity_date ? $record->maturity_date->format('d-m-Y') : '-' }}</div>
        <div class="head-isi">{{ $record->interest_rate }} %</div>
        
        @php
            // Calculate estimated monthly interest if not stored
            $monthlyInterest = 0;
            if($record->balance && $record->interest_rate) {
                 $monthlyInterest = ($record->balance * ($record->interest_rate / 100)) / 12;
            }
        @endphp
        <div class="head-isi">{{ number_format($monthlyInterest, 2, ',', '.') }}</div>
    </div>

    <div class="container-ttd">
        <div class="head-isi-ttd-1" style="margin-top:8px">
            Bekasi, {{ $record->opened_at ? Carbon::parse($record->opened_at)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
        </div>
        <div class="signature-group">
            <div class="signature-block">
                <div class="head-isi-ttd-3 signature-name">Vivian Dyah S</div>
                <div class="head-isi-ttd-3 signature-position">Bendahara</div>
            </div>
            <div class="signature-block" style="margin-left: 40px">
                <div class="head-isi-ttd-1 signature-name">Samuel Timothy</div>
                <div class="head-isi-ttd-1 signature-position">Ketua</div>
            </div>
        </div>
    </div>
</div>
