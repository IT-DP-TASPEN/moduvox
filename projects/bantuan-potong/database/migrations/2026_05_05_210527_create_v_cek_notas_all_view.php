<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_cek_notas_all");
        DB::statement("CREATE VIEW v_cek_notas_all AS

            -- ============================================================
            -- TABEL INTERNAL (filter by mitra_master_id)
            -- ============================================================

            SELECT
                'chk_i_' || id    AS uid,
                'Cek'                   AS jenis_layanan,
                'internal'              AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                mitra_master_id,
                NULL                    AS created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_checking_internals

            UNION ALL

            SELECT
                'est_i_' || id    AS uid,
                'Estimasi'              AS jenis_layanan,
                'internal'              AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                mitra_master_id,
                NULL                    AS created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_estimasi_internals

            UNION ALL

            SELECT
                'flg_i_' || id    AS uid,
                'Flagging TIF'          AS jenis_layanan,
                'internal'              AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                mitra_master_id,
                NULL                    AS created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_flagging_tif_internals

            UNION ALL

            SELECT
                'fmut_i_' || id   AS uid,
                'Flagging Mutasi'       AS jenis_layanan,
                'internal'              AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                mitra_master_id,
                NULL                    AS created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_flagging_mutasi_tif_internals

            UNION ALL

            SELECT
                'ofg_i_' || id    AS uid,
                'Open Flagging'         AS jenis_layanan,
                'internal'              AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                mitra_master_id,
                NULL                    AS created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_open_flagging_internals

            -- ============================================================
            -- TABEL NON-INTERNAL / MITRA (filter by created_mitra)
            -- ============================================================

            UNION ALL

            SELECT
                'chk_m_' || id    AS uid,
                'Cek'                   AS jenis_layanan,
                'mitra'                 AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                NULL                    AS mitra_master_id,
                created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                NULL                    AS created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_checkings

            UNION ALL

            SELECT
                'est_m_' || id    AS uid,
                'Estimasi'              AS jenis_layanan,
                'mitra'                 AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                NULL                    AS mitra_master_id,
                created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                NULL                    AS created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_estimasis

            UNION ALL

            SELECT
                'flg_m_' || id    AS uid,
                'Flagging TIF'          AS jenis_layanan,
                'mitra'                 AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                NULL                    AS mitra_master_id,
                created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                NULL                    AS created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_flagging_tifs

            UNION ALL

            SELECT
                'fmut_m_' || id   AS uid,
                'Flagging Mutasi'       AS jenis_layanan,
                'mitra'                 AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                NULL                    AS mitra_master_id,
                created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                NULL                    AS created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_flagging_mutasi_tifs

            UNION ALL

            SELECT
                'ofg_m_' || id    AS uid,
                'Open Flagging'         AS jenis_layanan,
                'mitra'                 AS source_type,
                id                      AS original_id,
                notas,
                nama_nasabah,
                wilayah,
                jenis_pensiun_added,
                NULL                    AS mitra_master_id,
                created_mitra,
                status,
                fee,
                keterangan,
                bukti_hasil,
                NULL                    AS created_branch,
                created_by,
                created_at,
                updated_at
            FROM permintaan_open_flagging_tifs
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_cek_notas_all");
    }
};
