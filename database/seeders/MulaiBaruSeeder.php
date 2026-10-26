<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MEMULAI DARI NOL: menghapus SEMUA data transaksi lalu menyusun ulang menu.
 * Jalankan: php artisan db:seed --class=MulaiBaruSeeder
 *
 * Dihapus  : penjualan, pengeluaran, modal, mutasi stok, pembelian.
 * Disusun  : kategori & produk persis sesuai Menu Altari V2 (MenuAltariSeeder),
 *            produk di luar menu dihapus, stok semua produk kembali 0.
 * Tetap    : akun pengguna dan pengaturan toko.
 */
class MulaiBaruSeeder extends Seeder
{
    private const TABEL_TRANSAKSI = [
        'penjualandetail', 'penjualan', 'pengeluaran', 'modal',
        'stok_mutasi', 'pembeliandetail', 'pembelian', 'pesanan_tahan',
    ];

    public function run()
    {
        Schema::disableForeignKeyConstraints();
        foreach (self::TABEL_TRANSAKSI as $tabel) {
            if (Schema::hasTable($tabel)) {
                // TRUNCATE juga mengembalikan nomor urut, jadi nota mulai dari TRX-000001.
                DB::table($tabel)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        $this->call(MenuAltariSeeder::class);

        Produk::query()->update(['stok' => 0]);
    }
}
