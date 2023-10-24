<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\Request;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportPerforma;


class PerformaController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');
        $user = User::pluck('name', 'id');
        $user_id = $request->input('user_id');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('performa.index', compact('tanggalAwal', 'tanggalAkhir', 'user', 'user_id'));
    }

    public function getData($awal, $akhir, $user_id)
    {
        $selectColumns = [
            DB::raw('DATE(penjualan.created_at) AS Tanggal'),
            'users.name AS kasir',
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.id_penjualan ELSE 0 END) AS total_transaksi'),
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL AND penjualan.status = "SUKSES" THEN penjualan.id_penjualan END) AS transaksi_sukses'),
            DB::raw('COUNT(DISTINCT CASE WHEN penjualan.metode IS NOT NULL AND penjualan.status = "SALAH" THEN penjualan.id_penjualan END) AS transaksi_salah'),
            DB::raw('SUM(CASE WHEN penjualan.metode IS NOT NULL THEN penjualan.pengunjung ELSE 0 END) AS total_pengunjung'),
        ];

        $trx = DB::table('penjualan')
            ->select($selectColumns)
            ->leftJoin('users', 'users.id', '=', 'penjualan.id_user')
            ->whereDate("penjualan.created_at", ">=", $awal)
            ->whereDate("penjualan.created_at", "<=", $akhir)
            ->where('penjualan.total_harga', '!=', 0);

            for ($i = 0; $i <= 500; $i++) {
                if ($user_id == $i) {
                    $trx->where('penjualan.id_user', $user_id);
                }
            }

        $trx = $trx->groupBy(DB::raw('DATE(penjualan.created_at)'), 'kasir')
            ->get();

        $trx->transform(function ($item, $key) {
            $item->Nomor = $key + 1;
            $item->Tanggal = tanggal_indonesia($item->Tanggal, false);
            return $item;
        });

        $totals = [
            'Nomor' => '',
            'Tanggal' => '',
            'kasir' => 'Total',
            'total_transaksi' => $trx->sum('total_transaksi'),
            'transaksi_sukses' => $trx->sum('transaksi_sukses'),
            'transaksi_salah' => $trx->sum('transaksi_salah'),
            'total_pengunjung' => $trx->sum('total_pengunjung'),
        ];

        $trx[] = $totals;

        return $trx;
    }


    public function data($awal, $akhir, $user_id)
    {
        $data = $this->getData($awal, $akhir, $user_id);

        return datatables()
            ->of($data)
            ->make(true);
    }

    public function exportPDF($awal, $akhir, $user_id)
    {
        $data = $this->getData($awal, $akhir, $user_id);
        $pdf  = PDF::loadView('performa.pdf', compact('awal', 'akhir', 'data'));
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->stream('Laporan-Peforma-Kasir-'. date('Y-m-d-his') .'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $awal = $request->input('tanggal_awal');
        $akhir = $request->input('tanggal_akhir');
        $user_id = $request->input('user_id');

        return Excel::download(new ExportPerforma($awal, $akhir, $user_id), "Laporan_Performa_Kasir.xlsx");
    }

}