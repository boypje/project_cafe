<?php

namespace App\Services;

use App\Models\Modal;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh angka laporan & dashboard dihitung di sini.
 *
 * Definisi:
 * - Penjualan kotor  = total harga item sebelum diskon.
 * - Penjualan bersih = kotor - diskon (tanpa pajak). Pajak dicatat terpisah.
 * - HPP              = harga pokok item terjual (snapshot saat transaksi; transaksi
 *                      lama yang belum punya snapshot memakai HPP produk saat ini).
 * - Laba kotor       = penjualan bersih - HPP.  Laba bersih = laba kotor - pengeluaran.
 */
class LaporanService
{
    private $hppProduk;

    public function rentang($awal, $akhir)
    {
        return [Carbon::parse($awal)->startOfDay(), Carbon::parse($akhir)->endOfDay()];
    }

    private function penjualan($awal, $akhir)
    {
        return Penjualan::valid()->whereBetween('penjualan.created_at', $this->rentang($awal, $akhir));
    }

    private function hppProduk()
    {
        if ($this->hppProduk === null) {
            $this->hppProduk = Produk::with('komponen')->get()->mapWithKeys(function ($p) {
                return [$p->id_produk => $p->hppEfektif()];
            });
        }

        return $this->hppProduk;
    }

    /**
     * Agregasi item terjual per produk, opsional dikelompokkan lagi per $grup (ekspresi SQL).
     */
    private function itemTerjual($awal, $akhir, $grup = null)
    {
        $q = DB::table('penjualandetail as d')
            ->join('penjualan as j', 'j.id_penjualan', '=', 'd.id_penjualan')
            ->whereNotNull('j.metode')->where('j.metode', '<>', '')
            ->whereBetween('j.created_at', $this->rentang($awal, $akhir))
            ->select(
                'd.id_produk',
                DB::raw('SUM(d.jumlah) as qty'),
                DB::raw('SUM(d.subtotal) as omzet'),
                DB::raw('SUM(d.harga_pokok * d.jumlah) as hpp_snap'),
                DB::raw('SUM(CASE WHEN d.harga_pokok = 0 THEN d.jumlah ELSE 0 END) as qty_tanpa_hpp')
            )
            ->groupBy('d.id_produk');

        if ($grup) {
            $q->addSelect(DB::raw("$grup as grup"))->groupBy(DB::raw($grup));
        }

        $hpp = $this->hppProduk();

        return $q->get()->map(function ($r) use ($hpp) {
            $r->qty = (int) $r->qty;
            $r->omzet = (int) $r->omzet;
            $r->hpp = (int) $r->hpp_snap + (int) $r->qty_tanpa_hpp * (int) ($hpp[$r->id_produk] ?? 0);
            return $r;
        });
    }

    public function ringkasan($awal, $akhir)
    {
        $j = $this->penjualan($awal, $akhir)->selectRaw('
            COUNT(*) as transaksi,
            COALESCE(SUM(total_harga),0) as kotor,
            COALESCE(SUM(diskon),0) as diskon,
            COALESCE(SUM(pajak),0) as pajak,
            COALESCE(SUM(bayar),0) as diterima,
            COALESCE(SUM(pengunjung),0) as pengunjung,
            COALESCE(SUM(total_item),0) as item
        ')->first();

        $items = $this->itemTerjual($awal, $akhir);
        $hpp = $items->sum('hpp');
        // Porsi omzet yang HPP-nya diketahui; laba hanya akurat bila mendekati 100%.
        $cakupanHpp = $items->isEmpty() ? 100
            : round($items->where('hpp', '>', 0)->sum('omzet') / max($items->sum('omzet'), 1) * 100);
        $pengeluaran = (int) Pengeluaran::whereBetween('created_at', $this->rentang($awal, $akhir))->sum('nominal');

        $bersih = (int) $j->kotor - (int) $j->diskon;
        $labaKotor = $bersih - $hpp;
        $transaksi = (int) $j->transaksi;
        $pengunjung = (int) $j->pengunjung;

        return [
            'transaksi' => $transaksi,
            'pengunjung' => $pengunjung,
            'item' => (int) $j->item,
            'kotor' => (int) $j->kotor,
            'diskon' => (int) $j->diskon,
            'pajak' => (int) $j->pajak,
            'bersih' => $bersih,
            'hpp' => (int) $hpp,
            'laba_kotor' => $labaKotor,
            'margin' => $bersih > 0 ? round($labaKotor / $bersih * 100, 1) : 0,
            'cakupan_hpp' => $cakupanHpp,
            'pengeluaran' => $pengeluaran,
            'laba_bersih' => $labaKotor - $pengeluaran,
            'aov' => $transaksi ? (int) round($bersih / $transaksi) : 0,
            'per_pengunjung' => $pengunjung ? (int) round($bersih / $pengunjung) : 0,
            'item_per_trx' => $transaksi ? round($j->item / $transaksi, 1) : 0,
        ];
    }

    public function harian($awal, $akhir)
    {
        $jual = $this->penjualan($awal, $akhir)
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as transaksi, SUM(total_harga - diskon) as bersih, SUM(pengunjung) as pengunjung')
            ->groupByRaw('DATE(created_at)')->get()->keyBy('tgl');

        $hpp = $this->itemTerjual($awal, $akhir, 'DATE(j.created_at)')->groupBy('grup')->map->sum('hpp');

        $keluar = Pengeluaran::whereBetween('created_at', $this->rentang($awal, $akhir))
            ->selectRaw('DATE(created_at) as tgl, SUM(nominal) as total')
            ->groupByRaw('DATE(created_at)')->pluck('total', 'tgl');

        $hasil = [];
        for ($d = Carbon::parse($awal); $d->lte(Carbon::parse($akhir)); $d->addDay()) {
            $t = $d->toDateString();
            $bersih = (int) optional($jual->get($t))->bersih;
            $h = (int) ($hpp[$t] ?? 0);
            $k = (int) ($keluar[$t] ?? 0);
            $hasil[] = [
                'tanggal' => $t,
                'transaksi' => (int) optional($jual->get($t))->transaksi,
                'pengunjung' => (int) optional($jual->get($t))->pengunjung,
                'bersih' => $bersih,
                'hpp' => $h,
                'laba_kotor' => $bersih - $h,
                'pengeluaran' => $k,
                'laba_bersih' => $bersih - $h - $k,
            ];
        }

        return $hasil;
    }

    public function bulanan($awal, $akhir)
    {
        $jual = $this->penjualan($awal, $akhir)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bln, COUNT(*) as transaksi, SUM(total_harga - diskon) as bersih, SUM(pengunjung) as pengunjung")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")->get()->keyBy('bln');

        $hpp = $this->itemTerjual($awal, $akhir, "DATE_FORMAT(j.created_at, '%Y-%m')")->groupBy('grup')->map->sum('hpp');

        $keluar = Pengeluaran::whereBetween('created_at', $this->rentang($awal, $akhir))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bln, SUM(nominal) as total")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")->pluck('total', 'bln');

        $hasil = [];
        for ($d = Carbon::parse($awal)->startOfMonth(); $d->lte(Carbon::parse($akhir)); $d->addMonth()) {
            $b = $d->format('Y-m');
            $row = $jual->get($b);
            $bersih = (int) optional($row)->bersih;
            $h = (int) ($hpp[$b] ?? 0);
            $k = (int) ($keluar[$b] ?? 0);
            $trx = (int) optional($row)->transaksi;
            $hasil[] = [
                'bulan' => $b,
                'transaksi' => $trx,
                'pengunjung' => (int) optional($row)->pengunjung,
                'bersih' => $bersih,
                'aov' => $trx ? (int) round($bersih / $trx) : 0,
                'hpp' => $h,
                'laba_kotor' => $bersih - $h,
                'pengeluaran' => $k,
                'laba_bersih' => $bersih - $h - $k,
            ];
        }

        return $hasil;
    }

    /** Pola jam & hari: matriks [hari 0-6 (Minggu=0)][jam 0-23]. */
    public function pola($awal, $akhir)
    {
        $rows = $this->penjualan($awal, $akhir)
            ->selectRaw('DAYOFWEEK(created_at) - 1 as hari, HOUR(created_at) as jam, COUNT(*) as transaksi, SUM(total_harga - diskon) as bersih')
            ->groupByRaw('hari, jam')->get();

        $jam = array_fill(0, 24, ['transaksi' => 0, 'bersih' => 0]);
        $hari = array_fill(0, 7, ['transaksi' => 0, 'bersih' => 0]);
        $matriks = array_fill(0, 7, array_fill(0, 24, 0));

        foreach ($rows as $r) {
            $jam[$r->jam]['transaksi'] += $r->transaksi;
            $jam[$r->jam]['bersih'] += (int) $r->bersih;
            $hari[$r->hari]['transaksi'] += $r->transaksi;
            $hari[$r->hari]['bersih'] += (int) $r->bersih;
            $matriks[$r->hari][$r->jam] = (int) $r->transaksi;
        }

        // Rata-rata per hari kalender: berapa kali tiap hari muncul dalam rentang.
        $jumlahHari = array_fill(0, 7, 0);
        for ($d = Carbon::parse($awal); $d->lte(Carbon::parse($akhir)); $d->addDay()) {
            $jumlahHari[$d->dayOfWeek]++;
        }
        foreach ($hari as $i => $h) {
            $hari[$i]['rata_bersih'] = $jumlahHari[$i] ? (int) round($h['bersih'] / $jumlahHari[$i]) : 0;
        }

        return compact('jam', 'hari', 'matriks');
    }

    public function metode($awal, $akhir)
    {
        return $this->penjualan($awal, $akhir)
            ->selectRaw('metode, COUNT(*) as transaksi, SUM(bayar) as total')
            ->groupBy('metode')->orderByDesc('total')->get()
            ->map(function ($r) {
                return ['metode' => $r->metode, 'transaksi' => (int) $r->transaksi, 'total' => (int) $r->total];
            });
    }

    public function produk($awal, $akhir)
    {
        $items = $this->itemTerjual($awal, $akhir)->keyBy('id_produk');
        $produk = Produk::with('category')->get()->keyBy('id_produk');
        $totalOmzet = max($items->sum('omzet'), 1);

        $rows = $items->map(function ($r) use ($produk) {
            $p = $produk->get($r->id_produk);
            $laba = $r->omzet - $r->hpp;
            return [
                'id_produk' => $r->id_produk,
                'nama' => $p->nama_produk ?? '(produk dihapus)',
                'kategori' => optional(optional($p)->category)->nama_kategori ?? '-',
                'qty' => $r->qty,
                'omzet' => $r->omzet,
                'hpp' => $r->hpp,
                'laba' => $laba,
                'margin' => $r->omzet > 0 ? round($laba / $r->omzet * 100, 1) : 0,
            ];
        })->sortByDesc('omzet')->values();

        // Klasifikasi ABC (Pareto): A = penyumbang 80% omzet pertama, B = 15% berikutnya.
        $kumulatif = 0;
        $rows = $rows->map(function ($r) use (&$kumulatif, $totalOmzet) {
            $r['kontribusi'] = round($r['omzet'] / $totalOmzet * 100, 1);
            $kumulatif += $r['omzet'];
            $persen = $kumulatif / $totalOmzet * 100;
            $r['kelas'] = $persen <= 80 || $r['kontribusi'] >= 80 ? 'A' : ($persen <= 95 ? 'B' : 'C');
            return $r;
        });

        $tidakLaku = $produk->filter(function ($p) use ($items) {
            return $p->tampil_kasir && ! $items->has($p->id_produk);
        })->map(function ($p) {
            return ['nama' => $p->nama_produk, 'kategori' => optional($p->category)->nama_kategori ?? '-'];
        })->values();

        $kategori = $rows->groupBy('kategori')->map(function ($g, $nama) use ($totalOmzet) {
            return [
                'kategori' => $nama,
                'qty' => $g->sum('qty'),
                'omzet' => $g->sum('omzet'),
                'laba' => $g->sum('laba'),
                'kontribusi' => round($g->sum('omzet') / $totalOmzet * 100, 1),
            ];
        })->sortByDesc('omzet')->values();

        return ['produk' => $rows, 'kategori' => $kategori, 'tidak_laku' => $tidakLaku];
    }

    public function kasir($awal, $akhir)
    {
        return $this->penjualan($awal, $akhir)
            ->leftJoin('users', 'users.id', '=', 'penjualan.id_user')
            ->selectRaw('users.name as nama, COUNT(*) as transaksi, SUM(total_harga - diskon) as bersih, SUM(pengunjung) as pengunjung, SUM(diskon) as diskon')
            ->groupBy('penjualan.id_user', 'users.name')->orderByDesc('bersih')->get()
            ->map(function ($r) {
                return [
                    'nama' => $r->nama ?? '(user dihapus)',
                    'transaksi' => (int) $r->transaksi,
                    'bersih' => (int) $r->bersih,
                    'pengunjung' => (int) $r->pengunjung,
                    'diskon' => (int) $r->diskon,
                    'aov' => $r->transaksi ? (int) round($r->bersih / $r->transaksi) : 0,
                ];
            });
    }

    public function pengeluaran($awal, $akhir)
    {
        $q = Pengeluaran::whereBetween('created_at', $this->rentang($awal, $akhir));

        return [
            'kategori' => (clone $q)->selectRaw('kategori, COUNT(*) as jumlah, SUM(nominal) as total')
                ->groupBy('kategori')->orderByDesc('total')->get()
                ->map(function ($r) {
                    return ['kategori' => $r->kategori ?: 'Lainnya', 'jumlah' => (int) $r->jumlah, 'total' => (int) $r->total];
                }),
            'terbesar' => (clone $q)->orderByDesc('nominal')->limit(10)->get()->map(function ($p) {
                return [
                    'deskripsi' => $p->deskripsi,
                    'kategori' => $p->kategori,
                    'nominal' => (int) $p->nominal,
                    'metode' => $p->metode,
                    'created_at' => $p->created_at->format('Y-m-d H:i'),
                ];
            }),
        ];
    }

    public function kas($awal, $akhir)
    {
        $rentang = $this->rentang($awal, $akhir);
        $jual = $this->penjualan($awal, $akhir)->selectRaw('metode, SUM(bayar) as total')->groupBy('metode')->pluck('total', 'metode');
        $keluar = Pengeluaran::whereBetween('created_at', $rentang)->selectRaw('metode, SUM(nominal) as total')->groupBy('metode')->pluck('total', 'metode');
        $modal = (int) Modal::whereBetween('created_at', $rentang)->sum('nominal');

        $jualTunai = (int) ($jual['Tunai'] ?? 0);
        $keluarTunai = (int) ($keluar['Tunai'] ?? 0);

        return [
            'modal' => $modal,
            'penjualan_tunai' => $jualTunai,
            'penjualan_non_tunai' => (int) $jual->sum() - $jualTunai,
            'pengeluaran_tunai' => $keluarTunai,
            'pengeluaran_non_tunai' => (int) $keluar->sum() - $keluarTunai,
            'kas_tunai' => $modal + $jualTunai - $keluarTunai,
            'arus_bersih' => $modal + (int) $jual->sum() - (int) $keluar->sum(),
        ];
    }

    /** Posisi stok, nilai persediaan, pemakaian & perkiraan hari stok habis. */
    public function stok($awal, $akhir)
    {
        $hari = max(Carbon::parse($awal)->diffInDays(Carbon::parse($akhir)) + 1, 1);
        $terjual = $this->itemTerjual($awal, $akhir)->pluck('qty', 'id_produk')->all();
        $pakai = app(StokService::class)->kebutuhan($terjual);

        $rows = Produk::with(['category', 'komponen'])->where('kelola_stok', true)->get()
            ->reject->isPaket()
            ->map(function ($p) use ($pakai, $hari) {
                $guna = (int) ($pakai[$p->id_produk] ?? 0);
                $perHari = $guna / $hari;
                return [
                    'id_produk' => $p->id_produk,
                    'nama' => $p->nama_produk,
                    'kategori' => optional($p->category)->nama_kategori ?? '-',
                    'satuan' => $p->satuan,
                    'stok' => (int) $p->stok,
                    'minimum' => (int) $p->stok_minimum,
                    'nilai' => max($p->stok, 0) * $p->harga_pokok,
                    'terpakai' => $guna,
                    'per_hari' => round($perHari, 1),
                    'hari_tersisa' => $perHari > 0 ? (int) floor(max($p->stok, 0) / $perHari) : null,
                    'status' => $p->stok <= 0 ? 'habis' : ($p->stok <= $p->stok_minimum ? 'menipis' : 'aman'),
                ];
            })->sortBy('nama')->values();

        return [
            'items' => $rows,
            'nilai_persediaan' => $rows->sum('nilai'),
            'habis' => $rows->where('status', 'habis')->count(),
            'menipis' => $rows->where('status', 'menipis')->count(),
        ];
    }

    private function periodeSebelumnya($awal, $akhir)
    {
        $a = Carbon::parse($awal);
        $b = Carbon::parse($akhir);
        $hari = $a->diffInDays($b) + 1;

        return [$a->copy()->subDays($hari)->toDateString(), $a->copy()->subDay()->toDateString()];
    }

    public function lengkap($awal, $akhir)
    {
        [$pa, $pb] = $this->periodeSebelumnya($awal, $akhir);
        $hari = Carbon::parse($awal)->diffInDays(Carbon::parse($akhir)) + 1;

        return [
            'periode' => ['awal' => $awal, 'akhir' => $akhir, 'hari' => $hari],
            'pembanding' => ['awal' => $pa, 'akhir' => $pb],
            'ringkasan' => $this->ringkasan($awal, $akhir),
            'ringkasan_lalu' => $this->ringkasan($pa, $pb),
            'harian' => $this->harian($awal, $akhir),
            'bulanan' => $hari > 31 ? $this->bulanan($awal, $akhir) : [],
            'pola' => $this->pola($awal, $akhir),
            'metode' => $this->metode($awal, $akhir),
            'produk' => $this->produk($awal, $akhir),
            'kasir' => $this->kasir($awal, $akhir),
            'pengeluaran' => $this->pengeluaran($awal, $akhir),
            'kas' => $this->kas($awal, $akhir),
            'stok' => $this->stok($awal, $akhir),
        ];
    }
}
