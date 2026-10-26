<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use App\Models\User;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KasirController extends Controller
{
    public const METODE = ['Tunai', 'Debit', 'QRIS', 'Transfer'];

    public function index()
    {
        return view('kasir.index', [
            'setting' => Setting::toko(),
            'metode' => self::METODE,
        ]);
    }

    /** Menu untuk layar kasir, termasuk stok tersedia (paket dihitung dari komponennya). */
    public function menu()
    {
        $produk = Produk::with(['komponen', 'category'])
            ->where('tampil_kasir', true)
            ->orderBy('nama_produk')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id_produk,
                    'nama' => $p->nama_produk,
                    'harga' => (int) $p->harga_jual,
                    'kategori' => $p->id_category,
                    'stok' => $p->stokTersedia(),
                    'minimum' => (int) $p->stok_minimum,
                    'paket' => $p->isPaket() ? $p->komponen->map(function ($k) {
                        return ($k->pivot->jumlah > 1 ? $k->pivot->jumlah . '× ' : '') . $k->nama_produk;
                    })->implode(', ') : null,
                    // Komponen yang stoknya dihitung, untuk menghitung sisa di keranjang.
                    'komponen' => $p->komponen->where('kelola_stok', true)->map(function ($k) {
                        return ['id' => $k->id_produk, 'jumlah' => (int) $k->pivot->jumlah];
                    })->values(),
                ];
            });

        $kategori = Category::orderBy('nama_kategori')->get(['id_category as id', 'nama_kategori as nama']);

        // Stok seluruh bahan/produk satuan (termasuk yang tidak tampil di kasir).
        $stok = Produk::where('kelola_stok', true)->pluck('stok', 'id_produk');

        return response()->json(compact('produk', 'kategori', 'stok'));
    }

    public function checkout(Request $request, StokService $stok)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:produk,id_produk',
            'items.*.jumlah' => 'required|integer|min:1|max:999',
            'diskon' => 'nullable|integer|min:0',
            'metode' => ['required', Rule::in(self::METODE)],
            'diterima' => 'nullable|integer|min:0',
            'pengunjung' => 'nullable|integer|min:0|max:500',
            'nama_pelanggan' => 'nullable|string|max:100',
            'meja' => 'nullable|string|max:20',
            'catatan' => 'nullable|string|max:255',
        ], [
            'items.required' => 'Pesanan masih kosong.',
            'metode.in' => 'Metode pembayaran tidak dikenal.',
        ]);

        $setting = Setting::toko();
        $items = collect($data['items'])->groupBy('id')->map->sum('jumlah')->all();
        $produk = Produk::with('komponen')->whereIn('id_produk', array_keys($items))->get()->keyBy('id_produk');

        $total = 0;
        $totalItem = 0;
        foreach ($items as $id => $qty) {
            $total += $produk[$id]->harga_jual * $qty;
            $totalItem += $qty;
        }

        $diskon = min((int) ($data['diskon'] ?? 0), $total);
        $pajak = (int) round(($total - $diskon) * $setting->pajak_persen / 100);
        $bayar = $total - $diskon + $pajak;
        $diterima = $data['metode'] === 'Tunai' ? (int) ($data['diterima'] ?? 0) : $bayar;

        if ($diterima < $bayar) {
            return response()->json(['message' => 'Uang diterima kurang dari total bayar.'], 422);
        }

        $kurang = $stok->kekurangan($stok->kebutuhan($items));
        if ($kurang->isNotEmpty() && $setting->blokir_stok_habis) {
            return response()->json([
                'message' => 'Stok tidak cukup: ' . $kurang->pluck('nama')->implode(', '),
            ], 422);
        }

        $penjualan = DB::transaction(function () use ($data, $items, $produk, $total, $totalItem, $diskon, $pajak, $bayar, $diterima, $stok) {
            $penjualan = Penjualan::create([
                'metode' => $data['metode'],
                'pengunjung' => $data['pengunjung'] ?? 1,
                'nama_pelanggan' => $data['nama_pelanggan'] ?? null,
                'meja' => $data['meja'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'total_item' => $totalItem,
                'total_harga' => $total,
                'diskon' => $diskon,
                'pajak' => $pajak,
                'bayar' => $bayar,
                'diterima' => $diterima,
                'id_user' => auth()->id(),
            ]);

            foreach ($items as $id => $qty) {
                $p = $produk[$id];
                PenjualanDetail::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_produk' => $id,
                    'harga_jual' => $p->harga_jual,
                    'harga_pokok' => $p->hppEfektif(),
                    'jumlah' => $qty,
                    'subtotal' => $p->harga_jual * $qty,
                ]);
            }

            $stok->potongPenjualan($penjualan->id_penjualan, $items);

            return $penjualan;
        });

        return response()->json([
            'id' => $penjualan->id_penjualan,
            'nomor' => $penjualan->nomor,
            'bayar' => $bayar,
            'diterima' => $diterima,
            'kembali' => $diterima - $bayar,
            'nota_url' => route('kasir.nota', $penjualan->id_penjualan),
            'peringatan' => $kurang->isNotEmpty()
                ? 'Stok minus untuk: ' . $kurang->pluck('nama')->implode(', ') . '. Segera lakukan stok masuk.'
                : null,
        ]);
    }

    public function nota($id)
    {
        $penjualan = Penjualan::with(['detail.produk', 'user'])->findOrFail($id);

        return view('kasir.nota', [
            'penjualan' => $penjualan,
            'setting' => Setting::toko(),
        ]);
    }

    /** Transaksi hari ini untuk panel riwayat kasir (cetak ulang). */
    public function riwayat()
    {
        $q = Penjualan::valid()->with('user')->whereDate('created_at', today())->latest('id_penjualan');
        if (! auth()->user()->isAdmin()) {
            $q->where('id_user', auth()->id());
        }

        return response()->json($q->limit(100)->get()->map(function ($p) {
            return [
                'id' => $p->id_penjualan,
                'nomor' => $p->nomor,
                'jam' => $p->created_at->format('H:i'),
                'metode' => $p->metode,
                'total' => (int) $p->bayar,
                'item' => (int) $p->total_item,
                'meja' => $p->meja,
                'pelanggan' => $p->nama_pelanggan,
                'kasir' => optional($p->user)->name,
                'nota_url' => route('kasir.nota', $p->id_penjualan),
            ];
        }));
    }

    /** Laporan tutup kasir (rekap shift/harian) — pengganti "nota status". */
    public function tutup(Request $request)
    {
        $users = auth()->user()->isAdmin() ? User::orderBy('name')->get(['id', 'name']) : collect();

        return view('kasir.tutup', [
            'users' => $users,
            'setting' => Setting::toko(),
        ]);
    }

    public function tutupData(Request $request)
    {
        $tanggal = $request->input('tanggal', today()->toDateString());
        $userId = auth()->user()->isAdmin() ? $request->input('user') : auth()->id();
        $rentang = [Carbon::parse($tanggal)->startOfDay(), Carbon::parse($tanggal)->endOfDay()];

        $jual = Penjualan::valid()->whereBetween('created_at', $rentang)
            ->when($userId, function ($q) use ($userId) {
                $q->where('id_user', $userId);
            });

        $metode = (clone $jual)->selectRaw('metode, COUNT(*) as transaksi, SUM(bayar) as total')->groupBy('metode')->get();
        $ringkas = (clone $jual)->selectRaw('COUNT(*) as transaksi, COALESCE(SUM(pengunjung),0) as pengunjung, COALESCE(SUM(total_harga),0) as kotor, COALESCE(SUM(diskon),0) as diskon, COALESCE(SUM(pajak),0) as pajak, COALESCE(SUM(bayar),0) as total, COALESCE(SUM(total_item),0) as item')->first();

        $items = DB::table('penjualandetail as d')
            ->join('penjualan as j', 'j.id_penjualan', '=', 'd.id_penjualan')
            ->leftJoin('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->leftJoin('category as c', 'c.id_category', '=', 'p.id_category')
            ->whereIn('j.id_penjualan', (clone $jual)->select('id_penjualan'))
            ->selectRaw('d.id_produk, p.nama_produk as nama, c.nama_kategori as kategori, SUM(d.jumlah) as qty, SUM(d.subtotal) as total')
            ->groupBy('d.id_produk', 'p.nama_produk', 'c.nama_kategori')
            ->orderBy('c.nama_kategori')->orderByDesc('qty')
            ->get();

        $bahan = app(StokService::class)->kebutuhan($items->pluck('qty', 'id_produk')->map(function ($v) {
            return (int) $v;
        })->all());
        $namaBahan = Produk::whereIn('id_produk', array_keys($bahan))->pluck('nama_produk', 'id_produk');
        $stokBahan = Produk::whereIn('id_produk', array_keys($bahan))->pluck('stok', 'id_produk');

        $keluar = Pengeluaran::whereBetween('created_at', $rentang)
            ->when($userId, function ($q) use ($userId) {
                $q->where(function ($w) use ($userId) {
                    $w->where('id_user', $userId)->orWhereNull('id_user');
                });
            })
            ->orderBy('created_at')->get(['deskripsi', 'kategori', 'metode', 'nominal', 'created_at']);

        $tunai = (int) optional($metode->firstWhere('metode', 'Tunai'))->total;
        $keluarTunai = (int) $keluar->where('metode', 'Tunai')->sum('nominal');

        return response()->json([
            'tanggal' => $tanggal,
            'ringkasan' => $ringkas,
            'metode' => $metode,
            'items' => $items,
            'bahan' => collect($bahan)->map(function ($qty, $id) use ($namaBahan, $stokBahan) {
                return ['nama' => $namaBahan[$id] ?? '-', 'qty' => $qty, 'sisa' => (int) ($stokBahan[$id] ?? 0)];
            })->sortBy('nama')->values(),
            'pengeluaran' => $keluar,
            'setoran' => [
                'penjualan_tunai' => $tunai,
                'pengeluaran_tunai' => $keluarTunai,
                'setor_tunai' => $tunai - $keluarTunai,
                'non_tunai' => (int) $metode->sum('total') - $tunai,
            ],
        ]);
    }
}
