<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inventaris extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'inventaris';

    protected $fillable = [
        'rekening',
        'nama_aset',
        'kantor_id',
        'golongan_id',
        'jenis_id',
        'ruangan_id',
        'lokasi_id',
        'sumber_id',
        'tgl_perolehan',
        'harga_perolehan',
        'nilai_buku',
        'akumulasi_penyusutan',
        'umur_bulan',
        'status',
        'merk',
        'no_seri',
        'keterangan',
        'alasan_hapus',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssetStatus::class,
            'tgl_perolehan' => 'date',
            'harga_perolehan' => 'decimal:2',
            'nilai_buku' => 'decimal:2',
            'akumulasi_penyusutan' => 'decimal:2',
            'umur_bulan' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(MstKantor::class, 'kantor_id');
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(MstGolongan::class, 'golongan_id');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(MstJenis::class, 'jenis_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(MstRuangan::class, 'ruangan_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(MstLokasi::class, 'lokasi_id');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(MstSumberDana::class, 'sumber_id');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(InvMutasi::class, 'inventaris_id');
    }

    public function improvement(): HasMany
    {
        return $this->hasMany(InvImprovement::class, 'inventaris_id');
    }

    public function motor(): HasOne
    {
        return $this->hasOne(InvMotor::class, 'inventaris_id');
    }

    public function tanah(): HasOne
    {
        return $this->hasOne(InvTanah::class, 'inventaris_id');
    }

    public function penyusutanDetail(): HasMany
    {
        return $this->hasMany(PenyusutanDetail::class, 'inventaris_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah aset adalah Tanah (tidak boleh disusutkan).
     */
    public function isTanah(): bool
    {
        return $this->golongan?->isTanah() ?? false;
    }

    /**
     * Cek apakah aset masih layak disusutkan.
     */
    public function isDepreciable(): bool
    {
        return !$this->isTanah()
            && $this->status === AssetStatus::AKTIF
            && $this->nilai_buku > 1
            && $this->umur_bulan > 0;
    }

    /**
     * Hitung beban penyusutan bulanan (Straight-Line).
     */
    public function getMonthlyDepreciation(): float
    {
        if (!$this->isDepreciable()) {
            return 0;
        }

        $beban = $this->harga_perolehan / $this->umur_bulan;

        // Jika sisa nilai buku - beban <= 1, sesuaikan agar sisa = 1
        if (($this->nilai_buku - $beban) <= 1) {
            $beban = $this->nilai_buku - 1;
        }

        return max(0, round($beban, 2));
    }

    /**
     * Generate Nomor Inventaris secara otomatis.
     * Format: [Kode Kantor].[Kode Golongan].[Kode Jenis].YYYY.MM.[Increment 4 digit]
     */
    public static function generateNomorInventaris(array $data): string
    {
        $kantor = MstKantor::find($data['kantor_id']);
        $golongan = MstGolongan::find($data['golongan_id']);
        $jenis = MstJenis::find($data['jenis_id']);
        $tglPerolehan = \Carbon\Carbon::parse($data['tgl_perolehan']);

        $prefix = sprintf('%s.%s.%s.%s.%s.',
            $kantor ? $kantor->kode : '00',
            $golongan ? $golongan->kode : '00',
            $jenis ? $jenis->kode : '00',
            $tglPerolehan->format('Y'),
            $tglPerolehan->format('m')
        );

        // Cari nomor terakhir dengan prefix yang sama
        $lastAsset = self::where('rekening', 'like', $prefix . '%')
            ->orderBy('rekening', 'desc')
            ->first();

        if ($lastAsset) {
            $lastNumber = intval(substr($lastAsset->rekening, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
