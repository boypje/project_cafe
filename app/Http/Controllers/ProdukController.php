<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\ProdukKomponen;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    public function index()
    {
        return view('produk.index', [
            'kategori' => Category::orderBy('nama_kategori')->get(['id_category', 'nama_kategori']),
        ]);
    }

    public function data()
    {
        $produk = Produk::with(['category', 'komponen'])->orderBy('nama_produk')->get();

        return response()->json($produk->map(function ($p) {
            $hpp = $p->hppEfektif();
            return [
                'id' => $p->id_produk,
                'nama' => $p->nama_produk,
                'id_category' => $p->id_category,
                'kategori' => optional($p->category)->nama_kategori,
                'harga_jual' => (int) $p->harga_jual,
                'harga_pokok' => (int) $p->harga_pokok,
                'hpp' => $hpp,
                'margin' => $p->harga_jual > 0 && $hpp > 0 ? round(($p->harga_jual - $hpp) / $p->harga_jual * 100, 1) : null,
                'stok' => (int) $p->stok,
                'tersedia' => $p->stokTersedia(),
                'satuan' => $p->satuan,
                'stok_minimum' => (int) $p->stok_minimum,
                'kelola_stok' => $p->kelola_stok,
                'tampil_kasir' => $p->tampil_kasir,
                'komponen' => $p->komponen->map(function ($k) {
                    return ['id' => $k->id_produk, 'nama' => $k->nama_produk, 'jumlah' => (int) $k->pivot->jumlah];
                })->values(),
            ];
        }));
    }

    private function validasi(Request $request, $id = null)
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('produk', 'nama_produk')->ignore($id, 'id_produk')],
            'id_category' => 'required|exists:category,id_category',
            'harga_jual' => 'required|integer|min:0',
            'harga_pokok' => 'nullable|integer|min:0',
            'stok' => 'nullable|integer',
            'satuan' => 'nullable|string|max:20',
            'stok_minimum' => 'nullable|integer|min:0',
            'kelola_stok' => 'boolean',
            'tampil_kasir' => 'boolean',
            'komponen' => 'array',
            'komponen.*.id' => ['required', 'integer', 'exists:produk,id_produk', Rule::notIn([$id])],
            'komponen.*.jumlah' => 'required|integer|min:1|max:99',
        ], [
            'nama.unique' => 'Nama produk sudah dipakai.',
            'komponen.*.id.not_in' => 'Produk tidak boleh menjadi komponen dirinya sendiri.',
        ]);
    }

    private function simpan(Produk $produk, array $data, StokService $stok)
    {
        DB::transaction(function () use ($produk, $data, $stok) {
            $baru = ! $produk->exists;
            $produk->fill([
                'nama_produk' => $data['nama'],
                'id_category' => $data['id_category'],
                'harga_jual' => $data['harga_jual'],
                'harga_pokok' => $data['harga_pokok'] ?? 0,
                'satuan' => $data['satuan'] ?? 'porsi',
                'stok_minimum' => $data['stok_minimum'] ?? 0,
                'kelola_stok' => $data['kelola_stok'] ?? true,
                'tampil_kasir' => $data['tampil_kasir'] ?? true,
            ]);
            if ($baru) {
                $produk->stok = 0;
            }
            $produk->save();

            // Perubahan stok dari form dicatat sebagai stok awal / stok opname.
            $target = $data['stok'] ?? null;
            if ($target !== null && (int) $target !== (int) $produk->stok && empty($data['komponen'])) {
                $stok->mutasi($produk, (int) $target - (int) $produk->stok, $baru ? 'masuk' : 'koreksi', [
                    'keterangan' => $baru ? 'Stok awal' : 'Ubah stok dari data produk',
                ]);
            }

            ProdukKomponen::where('id_produk', $produk->id_produk)->delete();
            foreach (collect($data['komponen'] ?? [])->unique('id') as $k) {
                ProdukKomponen::create([
                    'id_produk' => $produk->id_produk,
                    'id_komponen' => $k['id'],
                    'jumlah' => $k['jumlah'],
                ]);
            }
        });
    }

    public function store(Request $request, StokService $stok)
    {
        $this->simpan(new Produk(), $this->validasi($request), $stok);

        return response()->json(['message' => 'Produk ditambahkan.']);
    }

    public function update(Request $request, $id, StokService $stok)
    {
        $produk = Produk::findOrFail($id);
        $this->simpan($produk, $this->validasi($request, $id), $stok);

        return response()->json(['message' => 'Produk diperbarui.']);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        if (PenjualanDetail::where('id_produk', $id)->exists()) {
            return response()->json([
                'message' => 'Produk sudah pernah terjual sehingga tidak bisa dihapus (riwayat laporan akan rusak). Nonaktifkan "Tampil di kasir" saja.',
            ], 422);
        }

        if (ProdukKomponen::where('id_komponen', $id)->exists()) {
            return response()->json(['message' => 'Produk masih dipakai sebagai komponen paket lain.'], 422);
        }

        $produk->delete();

        return response()->json(['message' => 'Produk dihapus.']);
    }
}
