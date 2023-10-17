<?php

namespace App\Exports;

use App\Http\Requests\LaporanKeuanganGetRequest;
use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class ExportReport implements FromView
{
    private $startDate;
    private $endDate;
    private $productIds;

    public function __construct($startDate, $endDate, $productIds)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->productIds = $productIds;
    }

    public function view(): View
    {
        $data = $this->getData($this->startDate, $this->endDate, $this->productIds);
        $products = Produk::whereIn('id_produk', $this->productIds)->get();
        return view('exports.laporanstok', ['data' => $data, 'products' => $products]);
    }

    public function getData()
    {
        $selectColumns = [
            DB::raw('DATE(penjualan.created_at) AS Tanggal'),
            'users.name AS kasir',
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.id_penjualan ELSE 0 END) AS total_transaksi'),
            DB::raw('SUM(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.pengunjung ELSE 0 END) AS total_pengunjung'),
        ];

        foreach ($this->productIds as $productId) {  // Menggunakan $this->productIds
            $selectColumns[] = DB::raw("SUM(CASE WHEN penjualandetail.id_produk = $productId THEN penjualandetail.jumlah ELSE 0 END) as total_produk_" . $productId . "_terjual");
        }

        $selectColumns[] = DB::raw('SUM(penjualandetail.jumlah) AS total_semua_produk_terjual');
        $trx = DB::table('penjualan')
            ->select($selectColumns)
            ->join('users', 'users.id', '=', 'penjualan.id_user')
            ->join('penjualandetail', 'penjualandetail.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereIn('penjualandetail.id_produk', $this->productIds)  // Menggunakan $this->productIds
            ->whereDate("penjualan.created_at", ">=", $this->startDate)  // Menggunakan $this->startDate
            ->whereDate("penjualan.created_at", "<=", $this->endDate)  // Menggunakan $this->endDate
            ->groupBy(DB::raw('DATE(penjualan.created_at)'), 'kasir')
            ->get();
        $totalColumns = array_map(function ($productId) {
            return "total_produk_" . $productId . "_terjual";
        }, $this->productIds);  // Menggunakan $this->productIds

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
}
