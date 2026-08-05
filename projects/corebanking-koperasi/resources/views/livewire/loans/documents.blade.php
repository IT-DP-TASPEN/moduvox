<div class="p-0">
    <x-header title="Dokumen Pinjaman" subtitle="Manajemen dokumen pendukung pengajuan dan akad pinjaman" :user="auth()->user()" :role="auth()->user()->getRoleNames()->first() ?? 'No Role'">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                @if($viewMode == 'list')
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari No Rekening, PK, atau Nama..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all font-bold text-slate-700 w-80 shadow-sm">
                    </div>
                @endif
                @if($viewMode != 'list')
                    <button wire:click="backToList" class="flex items-center space-x-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">arrow_back</span>
                        <span>Kembali ke Daftar</span>
                    </button>
                @endif
            </div>
        </x-slot>
    </x-header>

    <div class="p-10">
        @if (session()->has('doc_success'))
            <div class="mb-8 bg-emerald-50 text-emerald-700 p-6 border border-emerald-100 rounded-[2rem] flex items-center gap-4 animate-in fade-in duration-500">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                </div>
                <span class="font-bold text-sm tracking-tight">{{ session('doc_success') }}</span>
            </div>
        @endif

        @if($viewMode == 'list')
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Nasabah & Rekening</th>
                                <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Produk</th>
                                <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Plafon</th>
                                <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Dokumen</th>
                                <th class="py-5 px-8 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($loans as $loan)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="py-6 px-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs">
                                                {{ substr($loan->cif->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-black text-sm text-slate-900">{{ $loan->cif->name }}</p>
                                                <p class="text-[10px] text-slate-500 font-bold tracking-widest">{{ $loan->account_no }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-6 px-8">
                                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                                            {{ $loan->product->name }}
                                        </span>
                                    </td>
                                    <td class="py-6 px-8 text-right font-black text-slate-900 text-sm">
                                        Rp{{ number_format($loan->principal_amount, 2, ',', '.') }}
                                    </td>
                                    <td class="py-6 px-8 text-center">
                                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-600 uppercase tracking-widest">
                                            <span class="material-symbols-outlined text-xs">folder</span>
                                            {{ $loan->documents_count }} File
                                        </div>
                                    </td>
                                    <td class="py-6 px-8 text-center">
                                        <button wire:click="selectLoan({{ $loan->id }})" class="p-2 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all shadow-sm">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-40 text-center">
                                        <div class="opacity-20 flex flex-col items-center">
                                            @if(!filled(trim($search)))
                                                <span class="material-symbols-outlined text-6xl mb-4">search</span>
                                                <p class="text-xs font-black uppercase tracking-widest">Silakan cari nomor rekening, PK, atau nama anggota</p>
                                            @else
                                                <span class="material-symbols-outlined text-6xl mb-4">folder_off</span>
                                                <p class="text-xs font-black uppercase tracking-widest">Data dokumen kredit tidak ditemukan</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-8 border-t border-slate-100 bg-slate-50/50">
                    {{ $loans->links(data: ['scrollTo' => false]) }}
                </div>
            </div>
        @endif

        @if($viewMode == 'detail' || $viewMode == 'upload')
            <div class="grid grid-cols-12 gap-8 items-start">
                <div class="col-span-12 lg:col-span-4">
                    <!-- Loan Info Card -->
                    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden sticky top-10">
                            <div class="p-8 bg-slate-900 text-white relative">
                            <div class="absolute top-0 right-0 p-8 opacity-10">
                                <span class="material-symbols-outlined text-7xl">description</span>
                            </div>
                            <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Informasi Pengajuan</p>
                            <h3 class="text-xl font-black mb-4">{{ $selectedLoan->cif->name }}</h3>
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-white/60 tracking-tight flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">account_balance_wallet</span>
                                    {{ $selectedLoan->account_no }}
                                </p>
                                <p class="text-xs font-bold text-white/60 tracking-tight flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">history_edu</span>
                                    PK: {{ $selectedLoan->pk_number }}
                                </p>
                            </div>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status Loan</span>
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                    {{ $selectedLoan->status }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Plafon</span>
                                <span class="text-sm font-black text-slate-900">Rp{{ number_format($selectedLoan->principal_amount, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tenor</span>
                                <span class="text-sm font-black text-slate-900">{{ $selectedLoan->tenor }} Bulan</span>
                            </div>

                            <button wire:click="showUploadForm" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-sm">upload_file</span>
                                Unggah Dokumen Baru
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-8">
                    @if($viewMode == 'upload')
                        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden animate-in fade-in zoom-in-95 duration-500">
                            <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-outlined text-indigo-600">add_circle</span>
                                    Unggah Dokumen Pendukung
                                </h3>
                                <button wire:click="cancelUpload" class="text-slate-400 hover:text-rose-500 transition-colors">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <form wire:submit="uploadDocument" class="p-8 space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-3">
                                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-500 ml-1">Jenis Dokumen <span class="text-rose-500">*</span></label>
                                        <select wire:model="document_type" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                            <option value="">Pilih Jenis...</option>
                                            @foreach($documentTypes as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('document_type') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-3">
                                        <label class="text-[10px] uppercase tracking-widest font-black text-slate-500 ml-1">Label Dokumen <span class="text-rose-500">*</span></label>
                                        <input wire:model="document_name" type="text" placeholder="Contoh: KTP Pemohon" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all">
                                        @error('document_name') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-500 ml-1">Pilih File <span class="text-rose-500">*</span></label>
                                    <div class="relative group">
                                        <input type="file" wire:model="file" class="hidden" id="doc_file">
                                        <label for="doc_file" class="w-full flex flex-col items-center justify-center py-10 border-2 border-dashed border-slate-200 rounded-3xl hover:border-indigo-500 hover:bg-indigo-50/50 cursor-pointer transition-all group">
                                            <span class="material-symbols-outlined text-4xl text-slate-300 group-hover:text-indigo-500 transition-colors mb-3">cloud_upload</span>
                                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest">
                                                {{ $file ? $file->getClientOriginalName() : 'Klik untuk pilih file (PDF/JPG/PNG)' }}
                                            </p>
                                        </label>
                                    </div>
                                    @error('file') <span class="text-[9px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-3">
                                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-500 ml-1">Catatan Tambahan</label>
                                    <textarea wire:model="notes" rows="3" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="Informasi tambahan terkait dokumen..."></textarea>
                                </div>

                                <div class="flex justify-end gap-4 pt-4">
                                    <button type="button" wire:click="cancelUpload" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
                                    <button type="submit" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-xl transition-all flex items-center gap-2">
                                        <div wire:loading wire:target="file" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                                        <span>Simpan Dokumen</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 overflow-hidden min-h-[500px]">
                            <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Daftar Dokumen Pendukung</h3>
                                <span class="px-3 py-1 bg-slate-100 rounded-lg text-[10px] font-black text-slate-500">{{ $selectedLoan->documents->count() }} File</span>
                            </div>

                            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                @forelse($selectedLoan->documents as $doc)
                                    <div class="p-6 bg-white border border-slate-200 rounded-3xl hover:border-indigo-500 hover:shadow-md transition-all group relative">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:text-slate-900 transition-colors">
                                                <span class="material-symbols-outlined text-2xl">
                                                    {{ in_array($doc->mime_type, ['image/jpeg', 'image/png']) ? 'image' : 'description' }}
                                                </span>
                                            </div>
                                            <div class="flex gap-2">
                                                @if($doc->status == 'PENDING')
                                                    <button wire:click="openVerifyModal({{ $doc->id }})" class="p-2 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition-colors" title="Verifikasi">
                                                        <span class="material-symbols-outlined text-lg">fact_check</span>
                                                    </button>
                                                @endif
                                                <a href="{{ route('loans.documents.view', $doc->id) }}" target="_blank" class="p-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors" title="Lihat">
                                                    <span class="material-symbols-outlined text-lg">open_in_new</span>
                                                </a>
                                                <a href="{{ route('loans.documents.download', $doc->id) }}" target="_blank" class="p-2 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition-colors" title="Download">
                                                    <span class="material-symbols-outlined text-lg">download</span>
                                                </a>
                                                <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Hapus dokumen ini?" class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-100 transition-colors" title="Hapus">
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">{{ $doc->document_type_label }}</p>
                                            <h4 class="font-bold text-sm text-slate-900 mb-2 truncate">{{ $doc->document_name }}</h4>
                                            
                                            <div class="flex items-center gap-4 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[10px]">person</span>
                                                    {{ $doc->uploader->name }}
                                                </span>
                                                <span>•</span>
                                                <span>{{ $doc->file_size_formatted }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                                            <span @class([
                                                'px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border',
                                                'bg-amber-50 text-amber-600 border-amber-100' => $doc->status == 'PENDING',
                                                'bg-emerald-50 text-emerald-600 border-emerald-100' => $doc->status == 'VERIFIED',
                                                'bg-rose-50 text-rose-600 border-rose-100' => $doc->status == 'REJECTED',
                                            ])>
                                                {{ $doc->status }}
                                            </span>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $doc->created_at->translatedFormat('d M Y') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-2 py-20 text-center opacity-20">
                                        <span class="material-symbols-outlined text-5xl mb-4">inventory_2</span>
                                        <p class="text-xs font-black uppercase tracking-widest">Belum ada dokumen diunggah</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Verify Modal -->
    <div @if($showVerifyModal) class="fixed inset-0 z-[60] flex items-center justify-center p-6" @else class="hidden" @endif x-data x-transition>
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" wire:click="closeVerifyModal"></div>
        <div class="relative w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Verifikasi Dokumen</h3>
                <button wire:click="closeVerifyModal" class="text-slate-400 hover:text-rose-500">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <div class="space-y-3">
                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-500 ml-1">Status Verifikasi</label>
                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="$set('verifyStatus', 'VERIFIED')" type="button" @class([
                            'px-4 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest border transition-all flex items-center justify-center gap-2',
                            'bg-emerald-600 text-white border-emerald-600' => $verifyStatus == 'VERIFIED',
                            'bg-white text-slate-400 border-slate-200' => $verifyStatus != 'VERIFIED',
                        ])>
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Diterima
                        </button>
                        <button wire:click="$set('verifyStatus', 'REJECTED')" type="button" @class([
                            'px-4 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest border transition-all flex items-center justify-center gap-2',
                            'bg-rose-600 text-white border-rose-600' => $verifyStatus == 'REJECTED',
                            'bg-white text-slate-400 border-slate-200' => $verifyStatus != 'REJECTED',
                        ])>
                            <span class="material-symbols-outlined text-sm">cancel</span>
                            Ditolak
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[10px] uppercase tracking-widest font-black text-slate-500 ml-1">Catatan / Alasan</label>
                    <textarea wire:model="verifyNotes" rows="3" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm text-slate-900 focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 transition-all" placeholder="Berikan alasan jika dokumen ditolak..."></textarea>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button wire:click="verifyDocument" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-xl transition-all">
                        Simpan Verifikasi
                    </button>
                    <button wire:click="closeVerifyModal" class="w-full py-4 bg-white text-slate-500 font-black text-xs uppercase tracking-widest">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
