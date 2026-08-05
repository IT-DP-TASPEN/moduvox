<div class="p-0">
    <x-header title="Registrasi CIF Baru" subtitle="Buka rekening master CIF nasabah baru." :user="$user" :role="$role">
        <x-slot:actions>
            <a href="{{ route('cifs.inquiry') }}" wire:navigate class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Kembali ke Daftar</span>
            </a>
        </x-slot:actions>
    </x-header>

    <div class="p-10">
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden flex flex-col">


            <form wire:submit.prevent="save" class="flex flex-col">
                <div class="p-8 bg-white space-y-12">
                    <!-- SECTION 1: Demographics -->
                    <div>
                        <div class="border-b border-slate-200 pb-2 mb-6">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest"><span class="material-symbols-outlined text-sm align-middle mr-1 text-slate-400">person</span> Data Demografi</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="nik" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm" placeholder="16 Digit NIK KTP">
                            @error('nik') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2 md:col-span-1 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor NPWP</label>
                            <input type="text" wire:model="npwp" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm" placeholder="Optional">
                            @error('npwp') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2 space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm uppercase">
                            @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tempat Lahir <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="birth_place" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm">
                            @error('birth_place') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="birth_date" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-600">
                            @error('birth_date') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                            <select wire:model="gender" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Gender</option>
                                <option value="MALE">Laki-laki</option>
                                <option value="FEMALE">Perempuan</option>
                            </select>
                            @error('gender') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-span-2 md:col-span-1 grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Agama <span class="text-rose-500">*</span></label>
                                <select wire:model="religion" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    <option value="">Pilih Agama</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Khonghucu">Khonghucu</option>
                                    <option value="Lainnya">Lainnya...</option>
                                </select>
                                @error('religion') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Deskripsi Agama Lainnya <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="religion_other" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm disabled:bg-slate-50 disabled:text-slate-300" {{ $religion !== 'Lainnya' ? 'disabled' : '' }}>
                            </div>
                            <div class="space-y-2 col-span-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Gol. Darah</label>
                                <select wire:model="blood_type" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                    <option value="">N/A</option>
                                    <option value="A">A</option><option value="B">B</option><option value="AB">AB</option><option value="O">O</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Gadis Ibu Kandung (KYC) <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="mother_maiden_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm">
                            @error('mother_maiden_name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Status Pernikahan</label>
                            <select wire:model="marital_status" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="SINGLE">Belum Kawin</option>
                                <option value="MARRIED">Kawin</option>
                                <option value="DIVORCED">Cerai Hidup</option>
                                <option value="WIDOWED">Cerai Mati</option>
                            </select>
                            @error('marital_status') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
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
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alamat KTP Lengkap <span class="text-rose-500">*</span></label>
                            <textarea wire:model="address" rows="3" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 resize-none" placeholder="Cth: Jl. Jend. Sudirman Blok A No.12"></textarea>
                            @error('address') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-span-2 grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">RT</label>
                                <input type="text" wire:model="rt" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">RW</label>
                                <input type="text" wire:model="rw" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Provinsi <span class="text-rose-500">*</span></label>
                            <select wire:model.live="province_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $p) <option value="{{ $p->id }}">{{ $p->nama }}</option> @endforeach
                            </select>
                            @error('province_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kota / Kabupaten <span class="text-rose-500">*</span></label>
                            <select wire:model.live="city_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 disabled:bg-slate-100 disabled:opacity-50" {{ empty($cities) ? 'disabled' : '' }}>
                                <option value="">Pilih Kota/Kab</option>
                                @foreach($cities as $c) <option value="{{ $c->id }}">{{ $c->nama }}</option> @endforeach
                            </select>
                            @error('city_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kecamatan <span class="text-rose-500">*</span></label>
                            <select wire:model.live="district_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 disabled:bg-slate-100 disabled:opacity-50" {{ empty($districts) ? 'disabled' : '' }}>
                                <option value="">Pilih Kecamatan</option>
                                @foreach($districts as $d) <option value="{{ $d->id }}">{{ $d->nama }}</option> @endforeach
                            </select>
                            @error('district_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kelurahan / Desa <span class="text-rose-500">*</span></label>
                            <select wire:model.live="subdistrict_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 disabled:bg-slate-100 disabled:opacity-50" {{ empty($subdistricts) ? 'disabled' : '' }}>
                                <option value="">Pilih Kelurahan</option>
                                @foreach($subdistricts as $s) <option value="{{ $s->id }}">{{ $s->nama }}</option> @endforeach
                            </select>
                            @error('subdistrict_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Kode Pos</label>
                            <input type="text" wire:model="postal_code" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                            @error('postal_code') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-span-2 space-y-2 mt-4">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Alamat Domisili (Kosongkan jika sama dengan KTP)</label>
                            <textarea wire:model="domicile_address" rows="2" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700 resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Handphone <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="phone" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm">
                            @error('phone') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Email <span class="text-[9px] opacity-70">(Optional)</span></label>
                            <input type="email" wire:model="email" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm">
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
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Profesi / Jenis Pekerjaan <span class="text-rose-500">*</span></label>
                            <select wire:model.live="occupation" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Profesi</option>
                                <option value="PNS">Pegawai Negeri Sipil (PNS)</option>
                                <option value="BUMN">Pegawai BUMN / BUMD</option>
                                <option value="Pegawai Swasta">Pegawai Swasta</option>
                                <option value="Wiraswasta">Wiraswasta / Pengusaha</option>
                            </select>
                            @error('occupation') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Induk Pegawai (NIP)</label>
                            <input type="text" wire:model="occupation_nip" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Perusahaan / Instansi</label>
                            <input type="text" wire:model="company_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                        </div>
                        <div class="col-span-2 space-y-2 mb-4">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Perkiraan Pendapatan per Bulan</label>
                            <select wire:model="income_range" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Range</option>
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
                            <input type="text" wire:model="spouse_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">NIK Pasangan</label>
                            <input type="text" wire:model="spouse_nik" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nama Kontak Darurat</label>
                            <input type="text" wire:model="emergency_name" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Nomor Kontak Darurat</label>
                            <input type="text" wire:model="emergency_phone" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                        </div>

                        <div class="col-span-2 border-b border-slate-200 pb-2 mt-4">
                            <p class="text-xs font-bold text-slate-900 uppercase tracking-widest">Informasi Penempatan (Wajib)</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Cabang Unit <span class="text-rose-500">*</span></label>
                            <select wire:model="branch_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Cabang</option>
                                @foreach($branches as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                            </select>
                            @error('branch_id') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 ml-1">Tenaga Marketing Pembawa CIF (Optional)</label>
                            <select wire:model="marketing_id" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-sm text-slate-700">
                                <option value="">Pilih Marketing</option>
                                @foreach($marketings as $m) <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->branch->name ?? '-' }})</option> @endforeach
                            </select>
                        </div>
                        
                    </div>
                </div>

                <div class="px-8 py-5 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 mt-auto">
                    <div>
                        @if($errors->any())
                        <div class="flex items-center text-rose-500 animate-fade-in">
                            <span class="material-symbols-outlined text-sm mr-2">error</span>
                            <span class="text-xs font-bold">Terdapat isian yang masih kosong atau tidak valid ({{ count($errors) }} Error).</span>
                        </div>
                        @endif
                    </div>
                    <button type="submit" class="px-8 py-3 bg-slate-900 text-white hover:shadow-lg hover:shadow-slate-900/20 font-bold text-xs rounded-xl transition-all active:scale-95 flex items-center">
                        <span class="material-symbols-outlined text-sm mr-2">send</span>
                        <span>Submit Data Registrasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
