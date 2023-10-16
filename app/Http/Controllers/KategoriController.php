<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class KategoriController extends Controller
{
    public function index()
    {
        return view('category.index');
    }

    public function data()
    {
        $categories = Category::orderBy('id_category', 'asc')->get();

        return datatables()
            ->of($categories)
            ->addIndexColumn()
            ->addColumn('aksi', function ($category) {
                return '<button onclick="editForm(\''. route('category.update', $category->id_category) .'\')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button onclick="deleteData(\''. route('category.destroy', $category->id_category) .'\')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        // Tambahkan kode untuk menampilkan formulir pembuatan kategori di sini
    }

    public function store(Request $request)
    {
        $category = new Category();
        $category->nama_kategori = $request->nama_kategori;
        $category->save();

        return response()->json('Data berhasil ditambahkan', 200);
    }

    public function show($id)
    {
        $category = Category::find($id);

        return response()->json($category);
    }

    public function edit($id)
    {
        // Tambahkan kode untuk menampilkan formulir pengeditan kategori di sini
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $category->nama_kategori = $request->nama_kategori;
        $category->update();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();

        return response(null, 204);
    }
}
