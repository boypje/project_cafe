<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Produk;
use App\Models\ProdukKomponen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menyesuaikan kategori, produk, harga, dan resep dengan "Menu Altari V2".
 * Jalankan: php artisan db:seed --class=MenuAltariSeeder
 *
 * - Produk lama dicocokkan lewat nama (nama baru atau nama lama), lalu diganti
 *   nama & harganya. Stok dan riwayat penjualannya tetap.
 * - Produk baru dibuat dengan stok 0.
 * - Produk yang tidak ada di menu TIDAK dihapus (laporan lama tetap utuh),
 *   hanya disembunyikan dari kasir. Kategori Packaging (mis. Box) tetap tampil.
 * - Aman dijalankan berulang.
 */
class MenuAltariSeeder extends Seeder
{
    /** [kategori baru => nama kategori lama yang diganti namanya] */
    private const KATEGORI = [
        'Menu Sambelan' => ['Makanan'],
        'Menu Kuah' => [],
        'Nasi Goreng & Mie' => [],
        'Camilan / Snack' => ['Snack'],
        'Minuman' => [],
        'Additional' => ['Tambahan'],
    ];

    /**
     * Urutan penting: bahan/additional dulu, karena dipakai sebagai komponen paket.
     * [nama, kategori, harga, nama lama[], stok dihitung (produk baru), komponen [nama => jumlah], tampil di kasir]
     */
    private function menu()
    {
        return [
            // Additional — sekaligus bahan untuk resep paket
            ['Nasi Putih', 'Additional', 5000, ['Nasi']],
            ['Ayam Goreng', 'Additional', 14000, ['Ayam Endul']],
            ['Bebek Goreng', 'Additional', 21000, ['Bebek Endul']],
            ['Lele', 'Additional', 10000, ['Lele goreng']],
            ['Mujaer', 'Additional', 12000, [], true],
            ['Wader', 'Additional', 9000, [], true],
            ['Telur Ceplok / Dadar', 'Additional', 4000, ['Telor']],
            ['Tempe Goreng', 'Additional', 2000, ['Tempe']],
            ['Tahu Goreng', 'Additional', 2000, ['Tahu']],
            // Bahan resep Nasi 3T/4T yang tidak dijual satuan
            ['Terong', 'Additional', 1000, [], true, [], false],

            // Menu Sambelan
            ['Nasi Ayam Endul', 'Menu Sambelan', 17000, ['Paket Nasi+Ayam Endul'], true, ['Nasi Putih' => 1, 'Ayam Goreng' => 1]],
            ['Nasi Bebek Endul', 'Menu Sambelan', 25000, ['Paket Nasi+Bebek Endul'], true, ['Nasi Putih' => 1, 'Bebek Goreng' => 1]],
            ['Nasi 3T', 'Menu Sambelan', 8000, ['Paket Nasi 3T'], true, ['Nasi Putih' => 1, 'Tahu Goreng' => 1, 'Tempe Goreng' => 1, 'Terong' => 1]],
            ['Nasi 4T', 'Menu Sambelan', 12000, ['Paket Nasi 4T'], true, ['Nasi Putih' => 1, 'Tahu Goreng' => 1, 'Tempe Goreng' => 1, 'Terong' => 1, 'Telur Ceplok / Dadar' => 1]],
            ['Nasi Lele', 'Menu Sambelan', 14000, ['Paket Nasi+Lele'], true, ['Nasi Putih' => 1, 'Lele' => 1]],
            ['Nasi Wader', 'Menu Sambelan', 13000, [], true, ['Nasi Putih' => 1, 'Wader' => 1]],
            ['Nasi Mujaer', 'Menu Sambelan', 13000, [], true, ['Nasi Putih' => 1, 'Mujaer' => 1]],

            // Menu Kuah (kuah dibuat per panci, yang dihitung hanya nasinya)
            ['Nasi Soto Kecambah', 'Menu Kuah', 14000, [], true, ['Nasi Putih' => 1]],
            ['Nasi + Sop Ayam', 'Menu Kuah', 15000, [], true, ['Nasi Putih' => 1]],

            // Nasi Goreng & Mie
            ['Nasi Goreng Ayam Pedas', 'Nasi Goreng & Mie', 12000, [], false],
            ['Nasi Goreng Ayam Pedas Jumbo', 'Nasi Goreng & Mie', 20000, [], false],
            ['Mie Goreng / Kuah', 'Nasi Goreng & Mie', 6000, ['Mie Instan']],

            // Camilan / Snack
            ['Pohong Ketumbar', 'Camilan / Snack', 5000, [], false],
            ['Tempe Mendoan', 'Camilan / Snack', 10000, ['Mendoan']],
            ['Tahu Petis', 'Camilan / Snack', 10000, [], false],
            ['Dimsum', 'Camilan / Snack', 12000, []],

            // Minuman (racikan per gelas: stok tidak dihitung)
            ['Kopi Hitam Kasar', 'Minuman', 5000, [], false],
            ['Kopi Hitam Halus', 'Minuman', 5000, [], false],
            ['Es Teh', 'Minuman', 4000, ['Teh']],
            ['Es Teh Tawar', 'Minuman', 3000, ['Teh Tawar']],
            ['Es Teh Jumbo', 'Minuman', 6000, ['Teh jumbo']],
            ['Teh Hangat', 'Minuman', 4000, [], false],
            ['Es Jeruk', 'Minuman', 5000, ['Jeruk']],
            ['Es Jeruk Jumbo', 'Minuman', 7000, [], false],
            ['Jeruk Hangat', 'Minuman', 5000, [], false],
            ['Air Mineral', 'Minuman', 3500, ['Aqua Botol']],
            ['Es Batu', 'Minuman', 1000, [], false],
        ];
    }

    public function run()
    {
        DB::transaction(function () {
            $kategori = $this->kategori();
            $dipakai = [];

            foreach ($this->menu() as $baris) {
                [$nama, $kat, $harga, $lama] = $baris;
                $kelolaStokBaru = $baris[4] ?? true;
                $komponen = $baris[5] ?? [];
                $tampil = $baris[6] ?? true;

                $produk = $this->cari($nama, $lama) ?? new Produk([
                    'stok' => 0,
                    'kelola_stok' => $kelolaStokBaru,
                    'satuan' => 'porsi',
                    'stok_minimum' => $kelolaStokBaru ? 5 : 0,
                ]);

                $produk->fill([
                    'nama_produk' => $nama,
                    'id_category' => $kategori[$kat],
                    'harga_jual' => $harga,
                    'tampil_kasir' => $tampil,
                ])->save();
                $dipakai[] = $produk->id_produk;

                if ($komponen) {
                    $this->resep($produk, $komponen);
                }
            }

            // Sembunyikan produk di luar menu, kecuali kategori Packaging (biaya kemasan).
            $packaging = Category::where('nama_kategori', 'Packaging')->value('id_category');
            Produk::whereNotIn('id_produk', $dipakai)
                ->when($packaging, function ($q) use ($packaging) {
                    $q->where('id_category', '<>', $packaging);
                })
                ->update(['tampil_kasir' => false]);
        });
    }

    private function kategori()
    {
        $hasil = [];
        foreach (self::KATEGORI as $nama => $lama) {
            $k = Category::where('nama_kategori', $nama)->first()
                ?? Category::whereIn('nama_kategori', $lama)->first()
                ?? new Category();
            $k->nama_kategori = $nama;
            $k->save();
            $hasil[$nama] = $k->id_category;
        }

        return $hasil;
    }

    private function cari($nama, array $lama)
    {
        return Produk::where('nama_produk', $nama)->first()
            ?? ($lama ? Produk::whereIn('nama_produk', $lama)->orderBy('id_produk')->first() : null);
    }

    private function resep(Produk $paket, array $komponen)
    {
        ProdukKomponen::where('id_produk', $paket->id_produk)->delete();

        foreach ($komponen as $nama => $jumlah) {
            $id = Produk::where('nama_produk', $nama)->value('id_produk');
            if ($id) {
                ProdukKomponen::create(['id_produk' => $paket->id_produk, 'id_komponen' => $id, 'jumlah' => $jumlah]);
            }
        }
    }
}
