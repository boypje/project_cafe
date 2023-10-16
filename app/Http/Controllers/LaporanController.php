<?php

namespace App\Http\Controllers;

use App\Models\Modal;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use PDF;

class LaporanController extends Controller
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
        $no = 1;
        $data = array();
        $pendapatan = 0;
        $total_pendapatan = 0;
        $penjualan_tunai = 0;
        $penjualan_debit = 0;
        $pengeluaran_tunai = 0;
        $pengeluaran_debit = 0;
        $temp = 0;

        while (strtotime($awal) <= strtotime($akhir)) {
            $tanggal = $awal;
            $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

            $penjualan_tunai = Penjualan::where('metode', "=", "Tunai")
                                            ->where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');
            $penjualan_debit = Penjualan::where('metode', "=", "Debit")
                                            ->where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');

            $pengeluaran_tunai = Pengeluaran::where('metode', "=", "Tunai")
                                            ->where('created_at', 'LIKE', "%$tanggal%")->sum('nominal');
            $pengeluaran_debit = Pengeluaran::where('metode', "=", "Debit")
                                            ->where('created_at', 'LIKE', "%$tanggal%")->sum('nominal');

            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal%")->sum('bayar');
            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal%")->sum('nominal');

            $pendapatan = $total_penjualan - $total_pengeluaran;
            $temp += $pendapatan;
            $total_pendapatan = $temp;

            $row = array();
            $row['DT_RowIndex'] = $no++;
            $row['tanggal'] = tanggal_indonesia($tanggal, false);
            $row['penjualan_tunai'] = format_money($penjualan_tunai);
            $row['penjualan_debit'] = format_money($penjualan_debit);
            $row['penjualan'] = format_money($total_penjualan);
            $row['pengeluaran_tunai'] = format_money($pengeluaran_tunai);
            $row['pengeluaran_debit'] = format_money($pengeluaran_debit);
            $row['pengeluaran'] = format_money($total_pengeluaran);
            $row['pendapatan'] = format_money($pendapatan);

            $data[] = $row;

        }

        $data[] = [
            'DT_RowIndex' => '',
            'tanggal' => '',
            'penjualan_tunai' => '',
            'penjualan_debit' => '',
            'penjualan' => '',
            'pengeluaran_tunai' => '',
            'pengeluaran_debit' => '',
            'pengeluaran' => 'Total Setoran',
            'pendapatan' => format_money($total_pendapatan),
        ];

        return $data;
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
        
        return $pdf->stream('Laporan-pendapatan-'. date('Y-m-d-his') .'.pdf');
    }
}