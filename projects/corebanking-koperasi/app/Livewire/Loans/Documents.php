<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LoanAccount;
use App\Models\LoanDocument;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Documents extends Component
{
    use WithPagination, LogsActivity, WithFileUploads;

    // Search & Filter
    public $search = '';
    public $documentTypeFilter = '';
    public $statusFilter = '';

    // Selected loan
    public $selectedLoan = null;
    public $viewMode = 'list'; // list | upload | detail

    // Upload form
    public $uploadLoanId;
    public $document_type = '';
    public $document_name = '';
    public $file;
    public $notes = '';

    // Verify
    public $verifyDocumentId = null;
    public $verifyStatus = 'VERIFIED';
    public $verifyNotes = '';
    public $showVerifyModal = false;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Dokumen Pinjaman');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectLoan($id)
    {
        $this->selectedLoan = LoanAccount::with(['cif', 'product', 'documents.uploader'])->findOrFail($id);
        $this->uploadLoanId = $id;
        $this->viewMode = 'detail';
    }

    public function backToList()
    {
        $this->selectedLoan = null;
        $this->viewMode = 'list';
        $this->resetUploadForm();
    }

    public function showUploadForm()
    {
        $this->viewMode = 'upload';
        $this->resetUploadForm();
    }

    public function cancelUpload()
    {
        $this->viewMode = 'detail';
        $this->resetUploadForm();
    }

    protected function resetUploadForm()
    {
        $this->document_type = '';
        $this->document_name = '';
        $this->file = null;
        $this->notes = '';
        $this->resetValidation();
    }

    public function uploadDocument()
    {
        $this->validate([
            'document_type' => 'required|string',
            'document_name' => 'required|string|max:200',
            'file'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ], [
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'document_name.required' => 'Nama/Label dokumen wajib diisi.',
            'file.required'          => 'File dokumen wajib diunggah.',
            'file.max'               => 'Ukuran file maksimal 10MB.',
            'file.mimes'             => 'Format file yang diizinkan: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX.',
        ]);

        $path = $this->file->store('loan-documents/' . $this->uploadLoanId, 'public');

        LoanDocument::create([
            'loan_account_id'   => $this->uploadLoanId,
            'document_type'     => $this->document_type,
            'document_name'     => $this->document_name,
            'file_path'         => $path,
            'file_original_name'=> $this->file->getClientOriginalName(),
            'mime_type'         => $this->file->getMimeType(),
            'file_size'         => $this->file->getSize(),
            'status'            => 'PENDING',
            'notes'             => $this->notes,
            'uploaded_by'       => Auth::id(),
        ]);

        $this->logActivity('UPLOAD_DOCUMENT', "Upload dokumen kredit untuk Loan #{$this->uploadLoanId}: {$this->document_name}");

        $this->resetUploadForm();
        $this->viewMode = 'detail';

        // Refresh selected loan
        $this->selectedLoan = LoanAccount::with(['cif', 'product', 'documents.uploader'])->findOrFail($this->uploadLoanId);

        session()->flash('doc_success', 'Dokumen berhasil diunggah.');
    }

    public function openVerifyModal($documentId)
    {
        $this->verifyDocumentId = $documentId;
        $this->verifyStatus = 'VERIFIED';
        $this->verifyNotes = '';
        $this->showVerifyModal = true;
    }

    public function closeVerifyModal()
    {
        $this->showVerifyModal = false;
        $this->verifyDocumentId = null;
    }

    public function verifyDocument()
    {
        $this->validate([
            'verifyStatus' => 'required|in:VERIFIED,REJECTED',
            'verifyNotes'  => 'nullable|string|max:500',
        ]);

        $doc = LoanDocument::findOrFail($this->verifyDocumentId);
        $doc->update([
            'status'      => $this->verifyStatus,
            'notes'       => $this->verifyNotes,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $this->logActivity('VERIFY_DOCUMENT', "Verifikasi dokumen #{$this->verifyDocumentId} menjadi {$this->verifyStatus}");

        $this->closeVerifyModal();

        // Refresh
        $this->selectedLoan = LoanAccount::with(['cif', 'product', 'documents.uploader', 'documents.verifier'])->findOrFail($this->uploadLoanId);

        session()->flash('doc_success', 'Status dokumen berhasil diperbarui.');
    }

    public function deleteDocument($documentId)
    {
        $doc = LoanDocument::findOrFail($documentId);
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        $this->logActivity('DELETE_DOCUMENT', "Hapus dokumen #{$documentId}");

        // Refresh
        $this->selectedLoan = LoanAccount::with(['cif', 'product', 'documents.uploader'])->findOrFail($this->uploadLoanId);

        session()->flash('doc_success', 'Dokumen berhasil dihapus.');
    }

    public function render()
    {
        $query = LoanAccount::with(['cif', 'product', 'branch'])
            ->withCount('documents');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('account_no', 'like', '%' . $this->search . '%')
                    ->orWhere('pk_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cif', function ($qCif) {
                        $qCif->where('cif_no', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $loans = filled(trim($this->search))
            ? $query->orderBy('created_at', 'desc')->paginate(12)
            : LoanAccount::whereRaw('1 = 0')->paginate(12);

        return view('livewire.loans.documents', [
            'loans'         => $loans,
            'documentTypes' => LoanDocument::documentTypes(),
        ]);
    }
}
