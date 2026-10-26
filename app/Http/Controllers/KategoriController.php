<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        return view('kategori.index');
    }

    public function data()
    {
        return response()->json(
            Category::withCount('produk')->orderBy('urutan')->orderBy('nama_kategori')->get()->map(function ($c) {
                return ['id' => $c->id_category, 'nama' => $c->nama_kategori, 'jumlah_produk' => $c->produk_count];
            })
        );
    }

    private function validasi(Request $request, $id = null)
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('category', 'nama_kategori')->ignore($id, 'id_category')],
        ], ['nama.unique' => 'Kategori sudah ada.']);
    }

    public function store(Request $request)
    {
        Category::create(['nama_kategori' => $this->validasi($request)['nama']]);

        return response()->json(['message' => 'Kategori ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        Category::findOrFail($id)->update(['nama_kategori' => $this->validasi($request, $id)['nama']]);

        return response()->json(['message' => 'Kategori diperbarui.']);
    }

    public function destroy($id)
    {
        $kategori = Category::withCount('produk')->findOrFail($id);
        if ($kategori->produk_count > 0) {
            return response()->json(['message' => 'Kategori masih dipakai ' . $kategori->produk_count . ' produk.'], 422);
        }
        $kategori->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
