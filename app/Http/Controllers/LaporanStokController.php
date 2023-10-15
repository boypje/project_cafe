<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaporanKeuanganGetRequest;
use App\Models\Produk;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanStokController extends Controller
{
    public function index(Request $request)
    {
        if (empty($request->productIds) || $request->selected_all) {
            $productIds = Produk::pluck('id_produk')->toArray();
        } else {
            $productIds = $request->productIds;
        }
        

        $products = Produk::whereIn('id_produk', $productIds)->get();

        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        return view('laporan_stok.index', compact('tanggalAwal', 'tanggalAkhir', 'products', 'productIds'));
    }

    public function getData($startDate, $endDate, $productIds)
    {
        $selectColumns = [
            DB::raw('DATE(penjualan.created_at) AS Tanggal'),
            'users.name AS kasir',
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.id_penjualan ELSE 0 END) AS total_transaksi'),
            DB::raw('SUM(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.pengunjung ELSE 0 END) AS total_pengunjung'),
        ];

        foreach ($productIds as $productId) {
            $selectColumns[] = DB::raw("SUM(CASE WHEN penjualandetail.id_produk = $productId THEN penjualandetail.jumlah ELSE 0 END) as total_produk_" . $productId . "_terjual");
        }

        $selectColumns[] = DB::raw('SUM(penjualandetail.jumlah) AS total_semua_produk_terjual');
        $trx = DB::table('penjualan')
            ->select($selectColumns)
            ->join('users', 'users.id', '=', 'penjualan.id_user')
            ->join('penjualandetail', 'penjualandetail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereIn('penjualandetail.id_produk', $productIds)
            ->whereDate("penjualan.created_at", ">=", $startDate)
            ->whereDate("penjualan.created_at", "<=", $endDate)
            ->groupBy('kasir', DB::raw('DATE(penjualan.created_at)'))
            ->get();
        $totalColumns = array_map(function ($productId) {
            return "total_produk_" . $productId . "_terjual";
        }, $productIds);

        $totals = [];
        foreach ($totalColumns as $column) {
            $totals[$column] = $trx->sum($column);
        }
        $totals['total_transaksi'] = $trx->sum("total_transaksi");
        $totals['total_pengunjung'] = $trx->sum("total_pengunjung");
        $totals['total_semua_produk_terjual'] = $trx->sum("total_semua_produk_terjual");

        $trx->map(function ($nilai) {
            $nilai->Tanggal = tanggal_indonesia($nilai->Tanggal, false);
            return $nilai;
        });

        $totals['Tanggal'] = '';
        $totals['kasir'] = 'Total';

        $trx[] = $totals;
        return $trx;
    }


    public function data(LaporanKeuanganGetRequest $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');
        if (empty($request->productIds) || $request->selected_all) {
            $productIds = Produk::pluck('id_produk')->toArray();
        } else {
            $productIds = $request->productIds;
        }

        return datatables()
            ->of($this->getData($startDate, $endDate, $productIds))
            ->make(true);
    }

    public function exportPDF(LaporanKeuanganGetRequest $request)
    {
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_awal');

        if (empty($request->productIds) || $request->selected_all) {
            $productIds = Produk::pluck('id_produk')->toArray();
        } else {
            $productIds = $request->productIds;
        }

        $data = $this->getData($startDate, $endDate, $productIds);
        $pdf  = PDF::loadView('laporan_stok.pdf', compact('tanggalAwal', 'tanggalAkhir', 'data'));
        $pdf->setPaper('a4', 'landscape');

        // Mengirim PDF ke browser untuk pencetakan langsung
        return $pdf->stream('Laporan-Kasir-' . date('Y-m-d-his') . '.pdf', ['Attachment' => false]);
    }
}
