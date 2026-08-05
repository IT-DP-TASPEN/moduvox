<div class="p-0">
    <x-header title="Data & Manajemen CIF" subtitle="Inquiry data anggota dan manajemen profil nasabah." :user="$user" :role="$role">
        <x-slot:actions>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <select wire:model.live="filter_branch" 
                        class="pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-bold text-slate-600 appearance-none">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </div>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                    <input wire:model.live="search" type="text" placeholder="Cari CIF, Nama, NIK..."
                        class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900 transition-all font-medium w-48">
                </div>
                @can('cifs.create')
                <a href="{{ route('cifs.create') }}" wire:navigate
                    class="flex items-center space-x-2 bg-slate-900 text-white px-4 py-2 rounded-xl font-bold text-xs hover:shadow-lg hover:shadow-slate-900/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-sm">person_add</span>
                    <span>Tambah Anggota</span>
                </a>
                @endcan
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        @if (session()->has('success'))
        <div class="bg-emerald-50 text-emerald-700 px-6 py-4 rounded-[2rem] border border-emerald-100 flex items-center mb-10 animate-fade-in shadow-sm">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center mr-4 shrink-0">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            </div>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
        @endif

        @if($viewMode === 'grid')
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase text-center w-20">Aksi</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">No. CIF</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Nama CIF</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Identitas / KTP</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Informasi Cabang</th>
                            <th class="py-5 px-6 text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center space-x-1">
                                    <button wire:click="viewCif({{ $item->id }})" class="p-2 bg-white text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm border border-slate-200 transition-all hover:text-slate-900" title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900 font-mono">{{ $item->cif_no }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-sm text-slate-900">{{ $item->name }}</p>
                                <p class="text-[11px] text-slate-500 font-medium">{{ $item->gender == 'MALE' ? 'Laki-laki' : 'Perempuan' }} • {{ $item->phone }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-xs text-slate-900">{{ $item->nik }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide">NPWP: {{ $item->npwp ?: '-' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-2">
                                    <span class="material-symbols-outlined text-slate-400 text-sm">store</span>
                                    <div>
                                        <p class="font-bold text-xs text-slate-700">{{ $item->branch ? $item->branch->name : 'N/A' }}</p>
                                        @if($item->marketing)
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">{{ $item->marketing->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg {{ $item->status == 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : ($item->status == 'BLOCKED' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-32 text-center text-slate-400">
                                <span class="material-symbols-outlined text-5xl mb-4 opacity-20">person_search</span>
                                <p class="text-sm font-bold">Lakukan pencarian data anggota...</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $items->links() }}
            </div>
            @endif
        </div>
        
        @elseif($viewMode === 'form')
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">
            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900">Detail CIF Nasabah</h3>
                    <p class="text-xs text-slate-500">{{ $selectedCif->cif_no ?? '-' }}</p>
                </div>
                <button wire:click="closeView" class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Tutup Detail</span>
                </button>
            </div>

            <form class="flex flex-col">
                <fieldset disabled class="m-0 p-0 border-0">
                <div class="p-8 bg-white space-y-12">
                    <!-- SECTION 1: Demographics -->
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">person</span> Data Demografi</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" wire:model="nik" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                        </div>
                        <div class="col-span-2 md:col-span-1 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor NPWP</label>
                            <input type="text" wire:model="npwp" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                        </div>
                        <div class="col-span-2 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap Sesuai KTP</label>
                            <input type="text" wire:model="name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm uppercase">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tempat Lahir</label>
                            <input type="text" wire:model="birth_place" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Lahir</label>
                            <input type="date" wire:model="birth_date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-600">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jenis Kelamin</label>
                            <select wire:model="gender" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                <option value="MALE">Laki-laki</option>
                                <option value="FEMALE">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1 grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Agama</label>
                                <select wire:model="religion" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Khonghucu">Khonghucu</option>
                                    <option value="Lainnya">Lainnya...</option>
                                </select>
                            </div>
                            <div class="space-y-2 col-span-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Gol. Darah</label>
                                <select wire:model="blood_type" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                    <option value="">N/A</option>
                                    <option value="A">A</option><option value="B">B</option><option value="AB">AB</option><option value="O">O</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Gadis Ibu Kandung (KYC)</label>
                            <input type="text" wire:model="mother_maiden_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Pernikahan</label>
                            <select wire:model="marital_status" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                <option value="SINGLE">Belum Kawin</option>
                                <option value="MARRIED">Kawin</option>
                                <option value="DIVORCED">Cerai Hidup</option>
                                <option value="WIDOWED">Cerai Mati</option>
                            </select>
                        </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Address & Cascading Geo -->
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">home_pin</span> Alamat & Wilayah</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alamat KTP Lengkap</label>
                            <textarea wire:model="address" rows="3" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 resize-none"></textarea>
                        </div>
                        
                        <div class="col-span-2 grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">RT</label>
                                <input type="text" wire:model="rt" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">RW</label>
                                <input type="text" wire:model="rw" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Provinsi</label>
                            <select wire:model.live="province_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                @foreach($provinces as $p) <option value="{{ $p->id }}">{{ $p->nama }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kota / Kabupaten</label>
                            <select wire:model.live="city_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                @foreach($cities as $c) <option value="{{ $c->id }}">{{ $c->nama }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kecamatan</label>
                            <select wire:model.live="district_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                @foreach($districts as $d) <option value="{{ $d->id }}">{{ $d->nama }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kelurahan / Desa</label>
                            <select wire:model.live="subdistrict_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                @foreach($subdistricts as $s) <option value="{{ $s->id }}">{{ $s->nama }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kode Pos</label>
                            <input type="text" wire:model="postal_code" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>
                        
                        <div class="col-span-2 space-y-2 mt-4">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alamat Domisili</label>
                            <textarea wire:model="domicile_address" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700 resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Handphone</label>
                            <input type="text" wire:model="phone" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Email</label>
                            <input type="email" wire:model="email" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm">
                        </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Additional -->
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">work</span> Pekerjaan & Relasi</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-2 border-b border-slate-200 pb-2 mt-2">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">Informasi Pekerjaan</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Profesi / Jenis Pekerjaan</label>
                            <select wire:model.live="occupation" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                <option value="PNS">Pegawai Negeri Sipil (PNS)</option>
                                <option value="BUMN">Pegawai BUMN / BUMD</option>
                                <option value="Pegawai Swasta">Pegawai Swasta</option>
                                <option value="Wiraswasta">Wiraswasta / Pengusaha</option>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Induk Pegawai (NIP)</label>
                            <input type="text" wire:model="occupation_nip" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Perusahaan / Instansi</label>
                            <input type="text" wire:model="company_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>
                        <div class="col-span-2 space-y-2 mb-4">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Perkiraan Pendapatan per Bulan</label>
                            <select wire:model="income_range" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                <option value="< 3.000.000">Kurang dari RP 3.000.000</option>
                                <option value="3m - 5m">Rp 3.000.000 - Rp 5.000.000</option>
                                <option value="5m - 10m">Rp 5.000.000 - Rp 10.000.000</option>
                                <option value="> 10m">Diatas Rp 10.000.000</option>
                            </select>
                        </div>

                        <div class="col-span-2 border-b border-slate-200 pb-2 mt-4">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">Informasi Pasangan & Darurat</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Pasangan (Suami/Istri)</label>
                            <input type="text" wire:model="spouse_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">NIK Pasangan</label>
                            <input type="text" wire:model="spouse_nik" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Kontak Darurat</label>
                            <input type="text" wire:model="emergency_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Kontak Darurat</label>
                            <input type="text" wire:model="emergency_phone" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                        </div>

                        <div class="col-span-2 border-b border-slate-200 pb-2 mt-4">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">Informasi Penempatan</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang Unit</label>
                            <select wire:model="branch_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                @foreach($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenaga Marketing Pembawa CIF</label>
                            <select wire:model="marketing_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl font-bold text-sm text-slate-700">
                                @foreach($marketings as $m) <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->branch->name ?? '-' }})</option> @endforeach
                            </select>
                        </div>
                        
                        </div>
                    </div>
                </div>
                </fieldset>
            </form>

            {{-- ═══ PANEL REKENING TERKAIT ═══ --}}
            @if($selectedCif)
            <div class="border-t border-slate-100 bg-slate-50/50 p-8 space-y-8">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">account_tree</span>
                    Rekening Terkait Nasabah — {{ $selectedCif->name }}
                </p>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- SIMPANAN --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-500 text-sm">savings</span>
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Rekening Simpanan</p>
                            <span class="ml-auto bg-indigo-100 text-indigo-700 text-[9px] font-black px-2 py-0.5 rounded-full">{{ $selectedCif->savingAccounts->count() }}</span>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @forelse($selectedCif->savingAccounts as $sa)
                            <div class="px-5 py-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">No. Rekening</p>
                                        <p class="text-[10px] font-black text-slate-800 font-mono">{{ $sa->account_no }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Cabang</p>
                                        <p class="text-[10px] font-bold text-slate-600">{{ $sa->branch->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Status</p>
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $sa->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">{{ $sa->status }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="px-5 py-5 text-[10px] text-slate-300 font-bold italic uppercase tracking-widest text-center">Tidak ada rekening simpanan</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- DEPOSITO --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="px-5 py-3 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500 text-sm">account_balance_wallet</span>
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Rekening Simpanan Berjangka</p>
                            <span class="ml-auto bg-amber-100 text-amber-700 text-[9px] font-black px-2 py-0.5 rounded-full">{{ $selectedCif->depositAccounts->count() }}</span>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @forelse($selectedCif->depositAccounts as $da)
                            <div class="px-5 py-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">No. Rekening</p>
                                        <p class="text-[10px] font-black text-slate-800 font-mono">{{ $da->account_no }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Cabang</p>
                                        <p class="text-[10px] font-bold text-slate-600">{{ $da->branch->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Status</p>
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $da->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">{{ $da->status }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="px-5 py-5 text-[10px] text-slate-300 font-bold italic uppercase tracking-widest text-center">Tidak ada simpanan berjangka</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- PINJAMAN --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="px-5 py-3 bg-rose-50 border-b border-rose-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-rose-500 text-sm">credit_score</span>
                            <p class="text-[10px] font-black uppercase tracking-widest text-rose-600">Fasilitas Kredit</p>
                            <span class="ml-auto bg-rose-100 text-rose-700 text-[9px] font-black px-2 py-0.5 rounded-full">{{ $selectedCif->loanAccounts->count() }}</span>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @forelse($selectedCif->loanAccounts as $la)
                            <div class="px-5 py-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">No. Rekening</p>
                                        <p class="text-[10px] font-black text-slate-800 font-mono">{{ $la->account_no }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Cabang</p>
                                        <p class="text-[10px] font-bold text-slate-600">{{ $la->branch->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest mb-1">Status</p>
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $la->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : ($la->status === 'APPROVED' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">{{ $la->status }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="px-5 py-5 text-[10px] text-slate-300 font-bold italic uppercase tracking-widest text-center">Tidak ada fasilitas kredit</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>
        @endif
    </div>
</div>
