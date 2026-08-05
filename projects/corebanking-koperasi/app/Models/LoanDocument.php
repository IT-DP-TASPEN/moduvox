<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanDocument extends Model
{
    protected $fillable = [
        'loan_account_id',
        'document_type',
        'document_name',
        'file_path',
        'file_original_name',
        'mime_type',
        'file_size',
        'status',
        'notes',
        'uploaded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size'   => 'integer',
    ];

    // Document type labels
    public static function documentTypes(): array
    {
        return [
            'KTP'           => 'KTP Pemohon',
            'KTP_PASANGAN'  => 'KTP Pasangan',
            'KK'            => 'Kartu Keluarga',
            'SLIP_GAJI'     => 'Slip Gaji / Bukti Penghasilan',
            'SURAT_USAHA'   => 'Surat Keterangan Usaha',
            'SERTIFIKAT'    => 'Sertifikat Tanah (SHM/SHGB)',
            'BPKB'          => 'BPKB Kendaraan',
            'NPWP'          => 'NPWP',
            'REKENING_KORAN'=> 'Rekening Koran (3 bulan)',
            'SURAT_NIKAH'   => 'Surat Nikah',
            'FOTO_AGUNAN'   => 'Foto Agunan',
            'SPK'           => 'Surat Perjanjian Kredit (SPK)',
            'LAINNYA'       => 'Dokumen Lainnya',
        ];
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return static::documentTypes()[$this->document_type] ?? $this->document_type;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function loanAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LoanAccount::class);
    }

    public function uploader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
