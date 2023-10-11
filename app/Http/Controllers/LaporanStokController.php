<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\User;
use App\Models\Produk;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporan_stok.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        
        $trx = DB::table('penjualan')
            ->select(
                DB::raw('DATE(penjualan.created_at) AS Tanggal'),
                'users.name AS kasir',
                DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.id_penjualan ELSE 0 END) AS total_transaksi'),
                DB::raw('SUM(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.pengunjung ELSE 0 END) AS total_pengunjung'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 27 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_23_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 28 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_24_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 29 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_25_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 30 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_26_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 31 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_27_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 33 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_28_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 34 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_29_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 1 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_1_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 4 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_2_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 9 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_3_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 14 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_4_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 13 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_5_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 16 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_6_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 15 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_7_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 3 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_8_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 5 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_9_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 22 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_10_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 2 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_11_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 17 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_12_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 18 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_13_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 12 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_14_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 19 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_15_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 20 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_16_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 21 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_17_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 23 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_18_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 24 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_19_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 11 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_20_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 8 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_21_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 26 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_22_terjual'),
                DB::raw('SUM(penjualandetail.jumlah) AS total_semua_produk_terjual'),
            )
            ->join('users', 'users.id', '=', 'penjualan.id_user')
            ->join('penjualandetail', 'penjualandetail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereDate("penjualan.created_at",  ">=", $awal)
            ->whereDate("penjualan.created_at",  "<=", $akhir)
            ->groupBy('kasir',DB::raw('DATE(penjualan.created_at)'))
            ->get();


            $total_pengunjung = $trx->sum("total_pengunjung");
            $total_transaksi = $trx->sum("total_transaksi");
            $payam = $trx->sum("total_produk_23_terjual");
            $pbebek = $trx->sum("total_produk_24_terjual");
            $tigat = $trx->sum("total_produk_25_terjual");
            $empat = $trx->sum("total_produk_26_terjual");
            $nasilele = $trx->sum("total_produk_27_terjual");
            $lele = $trx->sum("total_produk_28_terjual");
            $mie = $trx->sum("total_produk_29_terjual");
            $bebek = $trx->sum("total_produk_1_terjual");
            $ayam = $trx->sum("total_produk_2_terjual");
            $nasi = $trx->sum("total_produk_3_terjual");
            $tahu = $trx->sum("total_produk_4_terjual");
            $tempe = $trx->sum("total_produk_5_terjual");
            $terong = $trx->sum("total_produk_6_terjual");
            $telor = $trx->sum("total_produk_7_terjual");
            $mendoan = $trx->sum("total_produk_8_terjual");
            $teh = $trx->sum("total_produk_9_terjual");
            $tehtawar = $trx->sum("total_produk_10_terjual");
            $jeruk = $trx->sum("total_produk_11_terjual");
            $kopilanang = $trx->sum("total_produk_12_terjual");
            $kopispesial = $trx->sum("total_produk_13_terjual");
            $snack = $trx->sum("total_produk_14_terjual");
            $mineral = $trx->sum("total_produk_15_terjual");
            $aquab = $trx->sum("total_produk_16_terjual");
            $aquag = $trx->sum("total_produk_17_terjual");
            $tehpucuk = $trx->sum("total_produk_18_terjual");
            $tehbotol = $trx->sum("total_produk_19_terjual");
            $dimsum = $trx->sum("total_produk_20_terjual");
            $box = $trx->sum("total_produk_21_terjual");
            $kepala = $trx->sum("total_produk_22_terjual");
            $total = $trx->sum("total_semua_produk_terjual");
        
        $trx->map(function($nilai){
            $nilai->Tanggal = tanggal_indonesia($nilai->Tanggal, false);
            return $nilai;
        });


        $trx[] = [
            'Tanggal'=>'',
            'kasir'=> 'Total',
            'total_transaksi'=>$total_transaksi,
            'total_pengunjung'=>$total_pengunjung,
            'total_produk_23_terjual'=>$payam,
            'total_produk_24_terjual'=>$pbebek,
            'total_produk_25_terjual'=>$tigat,
            'total_produk_26_terjual'=>$empat,
            'total_produk_27_terjual'=>$nasilele,
            'total_produk_28_terjual'=>$lele,
            'total_produk_29_terjual'=>$mie,
            'total_produk_1_terjual'=>$bebek,
            'total_produk_2_terjual'=>$ayam,
            'total_produk_3_terjual'=>$nasi,
            'total_produk_4_terjual'=>$tahu,
            'total_produk_5_terjual'=>$tempe,
            'total_produk_6_terjual'=>$terong,
            'total_produk_7_terjual'=>$telor,
            'total_produk_8_terjual'=>$mendoan,
            'total_produk_9_terjual'=>$teh,
            'total_produk_10_terjual'=>$tehtawar,
            'total_produk_11_terjual'=>$jeruk,
            'total_produk_12_terjual'=>$kopilanang,
            'total_produk_13_terjual'=>$kopispesial,
            'total_produk_14_terjual'=>$snack,
            'total_produk_15_terjual'=>$mineral,
            'total_produk_16_terjual'=>$aquab,
            'total_produk_17_terjual'=>$aquag,
            'total_produk_18_terjual'=>$tehpucuk,
            'total_produk_19_terjual'=>$tehbotol,
            'total_produk_20_terjual'=>$dimsum,
            'total_produk_21_terjual'=>$box,
            'total_produk_22_terjual'=>$kepala,
            'total_semua_produk_terjual'=>$total,
        ];

        return $trx;
    }

    public function data($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($awal, $akhir)
{
    $data = $this->getData($awal, $akhir);
    $pdf  = PDF::loadView('laporan_stok.pdf', compact('awal', 'akhir', 'data'));
    $pdf->setPaper('a4', 'landscape');

    // Mengirim PDF ke browser untuk pencetakan langsung
    return $pdf->stream('Laporan-Kasir-' . date('Y-m-d-his') . '.pdf', ['Attachment' => false]);
}

}