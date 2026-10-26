<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\StokMutasi;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StokController extends Controller
{
    public function index()
    {
        return view('stok.index', [
            'produk' => Produk::with('komponen')->where('kelola_stok', true)->orderBy('nama_produk')->get()
                ->reject->isPaket()->map(function ($p) {
                    return ['id' => $p->id_produk, 'nama' => $p->nama_produk, 'satuan' => $p->satuan, 'stok' => (int) $p->stok, 'harga_pokok' => (int) $p->harga_pokok];
                })->values(),
            'tipe' => StokMutasi::TIPE,
        ]);
    }

    public function mutasi(Request $request)
    {
        $awal = $request->input('awal', today()->subDays(6)->toDateString());
        $akhir = $request->input('akhir', today()->toDateString());

        $page = StokMutasi::with(['produk', 'user'])
            ->whereBetween('created_at', [Carbon::parse($awal)->startOfDay(), Carbon::parse($akhir)->endOfDay()])
            ->when($request->produk, function ($q, $v) {
                $q->where('id_produk', $v);
            })
            ->when($request->tipe, function ($q, $v) {
                $q->where('tipe', $v);
            })
            ->orderByDesc('id_stok_mutasi')
            ->paginate(30);

        return response()->json([
            'data' => collect($page->items())->map(function ($m) {
                return [
                    'waktu' => $m->created_at->format('Y-m-d H:i'),
                    'produk' => optional($m->produk)->nama_produk ?? '-',
                    'tipe' => $m->tipe,
                    'tipe_label' => StokMutasi::TIPE[$m->tipe] ?? $m->tipe,
                    'jumlah' => (int) $m->jumlah,
                    'stok_akhir' => (int) $m->stok_akhir,
                    'keterangan' => $m->keterangan,
                    'user' => optional($m->user)->name,
                ];
            }),
            'page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
        ]);
    }

    /** Stok masuk (belanja), keluar (rusak/terbuang), atau koreksi (hasil opname). */
    public function store(Request $request, StokService $stok)
    {
        $data = $request->validate([
            'tipe' => ['required', Rule::in(['masuk', 'keluar', 'koreksi'])],
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:produk,id_produk',
            'items.*.jumlah' => 'required|integer|min:0',
            'items.*.harga_pokok' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:255',
        ], ['items.required' => 'Pilih minimal satu produk.']);

        DB::transaction(function () use ($data, $stok) {
            foreach ($data['items'] as $item) {
                $produk = Produk::lockForUpdate()->findOrFail($item['id']);

                if ($data['tipe'] === 'masuk' && ! empty($item['harga_pokok'])) {
                    $produk->harga_pokok = $item['harga_pokok'];
                }

                $delta = [
                    'masuk' => (int) $item['jumlah'],
                    'keluar' => -(int) $item['jumlah'],
                    'koreksi' => (int) $item['jumlah'] - (int) $produk->stok,
                ][$data['tipe']];

                if ($delta === 0) {
                    $produk->save();
                    continue;
                }

                $stok->mutasi($produk, $delta, $data['tipe'], ['keterangan' => $data['keterangan'] ?? null]);
            }
        });

        return response()->json(['message' => 'Stok berhasil diperbarui.']);
    }
}
