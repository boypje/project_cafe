<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\ProdukKomponen;
use Illuminate\Database\Seeder;

/**
 * Mengisi resep awal paket Warung Altari berdasarkan nama produk.
 * Aman dijalankan berulang: php artisan db:seed --class=ResepPaketSeeder
 * Paket yang komposisinya tidak pasti (mis. Paket Nasi 3T/4T) diatur manual di menu Produk.
 */
class ResepPaketSeeder extends Seeder
{
    public function run()
    {
        $resep = [
            'Paket Nasi+Ayam Endul'  => ['Nasi' => 1, 'Ayam Endul' => 1],
            'Paket Nasi+Bebek Endul' => ['Nasi' => 1, 'Bebek Endul' => 1],
            'Bebek Endul + nasi'     => ['Nasi' => 1, 'Bebek Endul' => 1],
            'Paket Nasi+Lele'        => ['Nasi' => 1, 'Lele goreng' => 1],
        ];

        foreach ($resep as $namaPaket => $komponen) {
            $paket = Produk::where('nama_produk', $namaPaket)->first();
            if (! $paket) {
                continue;
            }

            foreach ($komponen as $namaKomponen => $jumlah) {
                $item = Produk::where('nama_produk', $namaKomponen)->first();
                if (! $item) {
                    continue;
                }

                ProdukKomponen::updateOrCreate(
                    ['id_produk' => $paket->id_produk, 'id_komponen' => $item->id_produk],
                    ['jumlah' => $jumlah]
                );
            }
        }
    }
}
