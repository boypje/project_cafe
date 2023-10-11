<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Pengeluaran;
use App\Models\User;
use App\Models\Setting;
use App\Models\PenjualanDetail;
use Illuminate\Support\Facades\DB;



class KasirController extends Controller
{
    public function index(Request $request)
    {
        return view('kasir.dashboard');
    }

    public function notaStatus()
    {
        $setting = Setting::first();
        $now = date('Y-m-d');
        $user = auth()->id();
        $trx = DB::table('penjualan')
            ->select(
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
            ->where("users.id", '=', $user)
            ->whereDate("penjualan.created_at",  "=", $now)
            ->groupBy(DB::raw('DATE(penjualan.created_at)'))
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
            $tunai = Penjualan::where('metode', "=", "Tunai")
            ->where('created_at', 'LIKE', "%$now%")->sum('bayar');
            $debit = Penjualan::where('metode', "=", "Debit")
            ->where('created_at', 'LIKE', "%$now%")->sum('bayar');
            $jual = Penjualan::where('created_at', 'LIKE', "%$now%")->sum('bayar');
            $pengeluaran_tunai = Pengeluaran::where('metode', "=", "Tunai")
            ->where('created_at', 'LIKE', "%$now%")->sum('nominal');
            $pengeluaran_debit = Pengeluaran::where('metode', "=", "Debit")
            ->where('created_at', 'LIKE', "%$now%")->sum('nominal');
            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$now%")->sum('nominal');
            $setoran = $jual - $total_pengeluaran;

        return view('kasir.nota_status', compact('setting','total_pengunjung','total_transaksi', 'payam', 'pbebek', 'tigat','empat', 'nasilele', 'lele', 'mie','bebek', 'ayam', 'nasi', 'tahu', 'tempe', 'terong', 'telor', 'mendoan', 'teh', 'tehtawar', 'jeruk', 'kopilanang', 'kopispesial', 'snack', 'mineral', 'aquab', 'aquag', 'tehpucuk', 'tehbotol', 'dimsum', 'box', 'kepala', 'total','tunai', 'debit', 'jual', 'pengeluaran_tunai', 'pengeluaran_debit', 'total_pengeluaran', 'setoran'));

    }
}
