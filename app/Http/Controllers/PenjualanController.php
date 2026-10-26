<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\User;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        return view('penjualan.index', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'metode' => KasirController::METODE,
        ]);
    }

    public function data(Request $request)
    {
        $awal = $request->input('awal', today()->startOfMonth()->toDateString());
        $akhir = $request->input('akhir', today()->toDateString());

        $q = Penjualan::valid()->with('user')
            ->whereBetween('created_at', [Carbon::parse($awal)->startOfDay(), Carbon::parse($akhir)->endOfDay()])
            ->when($request->metode, function ($q, $v) {
                $q->where('metode', $v);
            })
            ->when($request->user, function ($q, $v) {
                $q->where('id_user', $v);
            })
            ->when($request->cari, function ($q, $v) {
                $angka = (int) preg_replace('/\D/', '', $v);
                $q->where(function ($w) use ($v, $angka) {
                    $w->where('nama_pelanggan', 'like', "%$v%")->orWhere('meja', $v);
                    if ($angka) {
                        $w->orWhere('id_penjualan', $angka);
                    }
                });
            });

        $total = (clone $q)->selectRaw('COUNT(*) as transaksi, COALESCE(SUM(bayar),0) as bayar, COALESCE(SUM(diskon),0) as diskon')->first();
        $page = $q->orderByDesc('id_penjualan')->paginate(25);

        return response()->json([
            'data' => collect($page->items())->map(function ($p) {
                return [
                    'id' => $p->id_penjualan,
                    'nomor' => $p->nomor,
                    'waktu' => $p->created_at->format('Y-m-d H:i'),
                    'metode' => $p->metode,
                    'item' => (int) $p->total_item,
                    'total_harga' => (int) $p->total_harga,
                    'diskon' => (int) $p->diskon,
                    'pajak' => (int) $p->pajak,
                    'bayar' => (int) $p->bayar,
                    'meja' => $p->meja,
                    'pelanggan' => $p->nama_pelanggan,
                    'kasir' => optional($p->user)->name,
                ];
            }),
            'page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
            'ringkasan' => $total,
        ]);
    }

    public function show($id)
    {
        $p = Penjualan::with(['detail.produk', 'user'])->findOrFail($id);

        return response()->json([
            'id' => $p->id_penjualan,
            'nomor' => $p->nomor,
            'waktu' => $p->created_at->format('Y-m-d H:i'),
            'kasir' => optional($p->user)->name,
            'metode' => $p->metode,
            'meja' => $p->meja,
            'pelanggan' => $p->nama_pelanggan,
            'pengunjung' => (int) $p->pengunjung,
            'catatan' => $p->catatan,
            'total_harga' => (int) $p->total_harga,
            'diskon' => (int) $p->diskon,
            'pajak' => (int) $p->pajak,
            'bayar' => (int) $p->bayar,
            'diterima' => (int) $p->diterima,
            'nota_url' => route('kasir.nota', $p->id_penjualan),
            'items' => $p->detail->map(function ($d) {
                return [
                    'nama' => optional($d->produk)->nama_produk ?? '(produk dihapus)',
                    'harga' => (int) $d->harga_jual,
                    'jumlah' => (int) $d->jumlah,
                    'subtotal' => (int) $d->subtotal,
                ];
            }),
        ]);
    }

    /** Batalkan transaksi: stok dikembalikan lalu data dihapus. */
    public function destroy($id, StokService $stok)
    {
        $penjualan = Penjualan::with('detail')->findOrFail($id);

        DB::transaction(function () use ($penjualan, $stok) {
            $lunas = $penjualan->metode !== null && $penjualan->metode !== '';
            $stok->kembalikanPenjualan($penjualan->id_penjualan, $penjualan->detail, $lunas);
            $penjualan->detail()->delete();
            $penjualan->delete();
        });

        return response()->json(['message' => 'Transaksi dibatalkan dan stok dikembalikan.']);
    }
}
