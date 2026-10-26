<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Services\LaporanService;

class DashboardController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('kasir.index');
        }

        return view('dashboard');
    }

    public function data(LaporanService $laporan)
    {
        $hari = today()->toDateString();
        $kemarin = today()->subDay()->toDateString();
        $awalBulan = today()->startOfMonth()->toDateString();
        // Bulan lalu pada rentang tanggal yang sama (MTD vs MTD) supaya adil.
        $awalBulanLalu = today()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $akhirBulanLalu = today()->subMonthNoOverflow()->toDateString();
        $awal30 = today()->subDays(29)->toDateString();

        return response()->json([
            'hari_ini' => $laporan->ringkasan($hari, $hari),
            'kemarin' => $laporan->ringkasan($kemarin, $kemarin),
            'bulan_ini' => $laporan->ringkasan($awalBulan, $hari),
            'bulan_lalu' => $laporan->ringkasan($awalBulanLalu, $akhirBulanLalu),
            'tren' => $laporan->harian($awal30, $hari),
            'jam_hari_ini' => $laporan->pola($hari, $hari)['jam'],
            'produk_bulan_ini' => $laporan->produk($awalBulan, $hari)['produk']->take(8)->values(),
            'metode_bulan_ini' => $laporan->metode($awalBulan, $hari),
            'stok' => collect($laporan->stok($awal30, $hari)['items'])
                ->whereIn('status', ['habis', 'menipis'])->sortBy('stok')->values()->take(10),
            'terbaru' => Penjualan::valid()->with('user')->latest('id_penjualan')->limit(8)->get()->map(function ($p) {
                return [
                    'id' => $p->id_penjualan,
                    'nomor' => $p->nomor,
                    'waktu' => $p->created_at->format('d/m H:i'),
                    'metode' => $p->metode,
                    'total' => (int) $p->bayar,
                    'kasir' => optional($p->user)->name,
                ];
            }),
        ]);
    }
}
