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
            )
            ->join('users', 'users.id', '=', 'penjualan.id_user')
            ->join('penjualandetail', 'penjualandetail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereDate("penjualan.created_at",  "=", $now)
            ->groupBy(DB::raw('DATE(penjualan.created_at)'))
            ->where("users.id", '=', $user)
            ->get();
            $total_pengunjung = $trx->sum("total_pengunjung");
            $total_transaksi = $trx->sum("total_transaksi");
            $tunai = Penjualan::where('metode', '=', 'Tunai')
            ->where('created_at', 'LIKE', "%$now%")
            ->where('id_user', '=', $user)
            ->sum('bayar');
            $debit = Penjualan::where('metode', '=', 'Debit')
            ->where('created_at', 'LIKE', "%$now%")
            ->where('id_user', '=', $user)
            ->sum('bayar');
            $jual = Penjualan::where('created_at', 'LIKE', "%$now")
              ->where('id_user', '=', $user)
              ->sum('bayar');
            $pengeluaran_tunai = Pengeluaran::where('metode', "=", "Tunai")
            ->where('created_at', 'LIKE', "%$now")
            ->where('id_user', '=', $user)
            ->sum('nominal');

            $pengeluaran_debit = Pengeluaran::where('metode', "=", "Debit")
            ->where('created_at', 'LIKE', "%$now")
            ->where('id_user', '=', $user)
            ->sum('nominal');

            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$now")
            ->where('id_user', '=', $user)
            ->sum('nominal');
            $setoran = $jual - $total_pengeluaran;

        return view('kasir.nota_status', compact('setting','total_pengunjung','total_transaksi','tunai', 'debit', 'jual', 'pengeluaran_tunai', 'pengeluaran_debit', 'total_pengeluaran', 'setoran'));

    }
}
