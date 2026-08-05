<?php

namespace App\Http\Controllers;

use App\Models\LoanDocument;
use Illuminate\Support\Facades\Storage;

class LoanDocumentController extends Controller
{
    public function view(int $documentId)
    {
        $this->authorizeDocumentAccess();
        $document = LoanDocument::findOrFail($documentId);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File dokumen fisik tidak ditemukan di server. Path: ' . $document->file_path);
        }

        return response()->file(
            Storage::disk('public')->path($document->file_path),
            [
                'Content-Type' => $document->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($document->file_original_name ?: basename($document->file_path)) . '"',
            ]
        );
    }

    public function download(int $documentId)
    {
        $this->authorizeDocumentAccess();
        $document = LoanDocument::findOrFail($documentId);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File dokumen fisik tidak ditemukan di server. Path: ' . $document->file_path);
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->file_original_name ?: basename($document->file_path)
        );
    }

    private function authorizeDocumentAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('loans.documents') || $user->can('loans.inquiry')), 403);
    }
}
