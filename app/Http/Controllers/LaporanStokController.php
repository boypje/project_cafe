<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\User;
use App\Models\Produk;
use Barryvdh\DomPDF\PDF;
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

        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

    public function getData($awal, $akhir)
    {
        $trx = DB::table('penjualan')
            ->select(
                'users.name AS kasir',
                'penjualan.created_at',
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 1 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_1_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 2 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_2_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 3 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_3_terjual'),
                DB::raw('SUM(CASE WHEN penjualandetail.id_produk = 4 THEN penjualandetail.jumlah ELSE 0 END) as total_produk_4_terjual'),
                DB::raw('SUM(penjualandetail.jumlah) AS total_semua_produk_terjual'),
                DB::raw('SUM(CASE WHEN metode = "Tunai" THEN penjualan.total_harga ELSE 0 END) as total_tunai'),
                DB::raw('SUM(CASE WHEN metode = "Debit" THEN penjualan.total_harga ELSE 0 END) as total_debit'),
                DB::raw('SUM(penjualan.total_harga) as total_semua_metode')
            )
            ->join('users', 'users.id', '=', 'penjualan.id_user')
            ->join('penjualandetail', 'penjualandetail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereDate("penjualan.created_at",  ">=", $awal)
            ->whereDate("penjualan.created_at",  "<=", $akhir)
            ->groupBy('users.name', 'penjualan.created_at')
            ->get();
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
        $pdf  = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'data'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-pendapatan-' . date('Y-m-d-his') . '.pdf');
    }
}
