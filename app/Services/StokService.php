<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\StokMutasi;
use Illuminate\Support\Collection;

/**
 * Semua perubahan stok lewat sini supaya selalu tercatat di stok_mutasi.
 *
 * Aturan resep: produk yang punya komponen (mis. "Paket Nasi + Bebek")
 * tidak memotong stoknya sendiri, melainkan stok tiap komponennya
 * (Nasi x1, Bebek Endul x1). Produk biasa memotong stoknya sendiri
 * bila "kelola stok" aktif.
 */
class StokService
{
    /**
     * Ubah daftar item terjual menjadi kebutuhan stok per produk yang dikelola.
     *
     * @param  array<int,int>  $items  [id_produk => jumlah terjual]
     * @return array<int,int>  [id_produk stok => jumlah dipotong]
     */
    public function kebutuhan(array $items)
    {
        $produk = Produk::with('komponen')->whereIn('id_produk', array_keys($items))->get()->keyBy('id_produk');
        $butuh = [];

        foreach ($items as $id => $qty) {
            $p = $produk->get($id);
            if (! $p) {
                continue;
            }

            if ($p->isPaket()) {
                foreach ($p->komponen as $k) {
                    if ($k->kelola_stok) {
                        $butuh[$k->id_produk] = ($butuh[$k->id_produk] ?? 0) + $qty * $k->pivot->jumlah;
                    }
                }
            } elseif ($p->kelola_stok) {
                $butuh[$id] = ($butuh[$id] ?? 0) + $qty;
            }
        }

        return $butuh;
    }

    /**
     * Produk yang stoknya tidak mencukupi untuk kebutuhan tersebut.
     */
    public function kekurangan(array $butuh)
    {
        return Produk::whereIn('id_produk', array_keys($butuh))->get()
            ->filter(function ($p) use ($butuh) {
                return $p->stok < $butuh[$p->id_produk];
            })
            ->map(function ($p) use ($butuh) {
                return ['nama' => $p->nama_produk, 'stok' => $p->stok, 'butuh' => $butuh[$p->id_produk]];
            })
            ->values();
    }

    /**
     * Ubah stok satu produk dan catat mutasinya. $jumlah bertanda (+ masuk, - keluar).
     */
    public function mutasi(Produk $produk, int $jumlah, string $tipe, array $extra = [])
    {
        $produk->stok = (int) $produk->stok + $jumlah;
        $produk->save();

        return StokMutasi::create(array_merge([
            'id_produk' => $produk->id_produk,
            'tipe' => $tipe,
            'jumlah' => $jumlah,
            'stok_akhir' => $produk->stok,
            'harga_pokok' => $produk->harga_pokok,
            'id_user' => auth()->id(),
        ], $extra));
    }

    /** Potong stok untuk satu transaksi penjualan. Dipanggil di dalam DB transaction. */
    public function potongPenjualan(int $idPenjualan, array $items)
    {
        $this->terapkan($this->kebutuhan($items), -1, 'penjualan', [
            'id_penjualan' => $idPenjualan,
            'keterangan' => 'TRX-' . str_pad($idPenjualan, 6, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Kembalikan stok transaksi yang dibatalkan. Bila transaksi punya jejak mutasi,
     * kembalikan persis sesuai jejak itu; transaksi lama (sebelum fitur ini) memotong
     * stok produknya sendiri, jadi stok produk itu yang dikembalikan.
     */
    public function kembalikanPenjualan(int $idPenjualan, Collection $detail, bool $lunas = true)
    {
        $jejak = StokMutasi::where('id_penjualan', $idPenjualan)->where('tipe', 'penjualan')->get();

        if ($jejak->isNotEmpty()) {
            $butuh = $jejak->groupBy('id_produk')->map(function ($rows) {
                return -$rows->sum('jumlah');
            })->all();
        } elseif ($lunas) {
            $butuh = $detail->groupBy('id_produk')->map->sum('jumlah')->all();
        } else {
            // Draft lama yang tidak pernah dibayar belum memotong stok.
            $butuh = [];
        }

        $this->terapkan($butuh, 1, 'batal', [
            'id_penjualan' => $idPenjualan,
            'keterangan' => 'Batal TRX-' . str_pad($idPenjualan, 6, '0', STR_PAD_LEFT),
        ]);
    }

    private function terapkan(array $butuh, int $arah, string $tipe, array $extra)
    {
        if (empty($butuh)) {
            return;
        }

        $produk = Produk::whereIn('id_produk', array_keys($butuh))->lockForUpdate()->get();
        foreach ($produk as $p) {
            $this->mutasi($p, $arah * $butuh[$p->id_produk], $tipe, $extra);
        }
    }
}
