<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function index()
    {
        return view('pengeluaran.index');
    }

    public function data()
    {
        $user = auth()->user();
        $today = Carbon::now();

        if ($user->level === 1) {
            $pengeluaran = Pengeluaran::orderBy('id_pengeluaran', 'asc')->get();
        } else {
            $pengeluaran = Pengeluaran::where('id_user', $user->id)
                ->orderBy('id_pengeluaran', 'asc')
                ->get();
        }

        return datatables()
            ->of($pengeluaran)
            ->addIndexColumn()
            ->addColumn('created_at', function ($pengeluaran) {
                return tanggal_indonesia($pengeluaran->created_at, false);
            })
            ->addColumn('metode', function ($pengeluaran) {
                return $pengeluaran->metode;
            })
            ->addColumn('nominal', function ($pengeluaran) {
                return format_money($pengeluaran->nominal);
            })
            ->editColumn('kasir', function ($pengeluaran) {
                return $pengeluaran->user->name ?? '';
            })
            ->addColumn('aksi', function ($pengeluaran) use ($user, $today) {
                $buttons = '';

                if ($user->level === 1 || (Carbon::parse($pengeluaran->created_at)->isSameDay($today) && $user->level === 2)) {
                    $buttons .= '<button onclick="editForm(`'. route('pengeluaran.update', $pengeluaran->id_pengeluaran) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>';
                    $buttons .= '<button onclick="deleteData(`'. route('pengeluaran.destroy', $pengeluaran->id_pengeluaran) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>';
                }

                return $buttons;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        // Kode untuk menampilkan formulir pembuatan pengeluaran
    }

    public function store(Request $request)
    {
        $pengeluaran = new Pengeluaran();
        $pengeluaran->id_user = auth()->id();
        $pengeluaran->metode = $request->metode;
        $pengeluaran->nominal = $request->nominal;
        $pengeluaran->deskripsi = $request->deskripsi;
        $pengeluaran->save();

        return response()->json('Data berhasil ditambahkan', 200);
    }

    public function show($id)
    {
        $pengeluaran = Pengeluaran::find($id);

        return response()->json($pengeluaran);
    }

    public function edit($id)
    {
        // Kode untuk menampilkan formulir pengeditan pengeluaran
    }

    public function update(Request $request, $id)
    {
        $pengeluaran = Pengeluaran::find($id);
        $pengeluaran->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::find($id);
        $pengeluaran->delete();

        return response(null, 204);
    }
}
