<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        return view('penjualan.index');
    }

    public function data()
    {
        $penjualan = Penjualan::where('total_harga', '>', 0)
            ->orderBy('id_penjualan', 'desc')
            ->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('id_penjualan', function ($penjualan) {
                return tambah_nol_didepan($penjualan->id_penjualan, 10);
            })
            ->addColumn('metode', function ($penjualan) {
                return $penjualan->metode;
            })
            ->addColumn('total_harga', function ($penjualan) {
                return format_money($penjualan->total_harga);
            })
            ->editColumn('diskon', function ($penjualan) {
                return format_money($penjualan->diskon);
            })
            ->addColumn('bayar', function ($penjualan) {
                return format_money($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->addColumn('status', function ($penjualan) {
                if ($penjualan->status === 'SUKSES') {
                    return '<small class="label label-success">' . $penjualan->status . '</small>';
                } elseif ($penjualan->status === 'SALAH') {
                    return '<small class="label label-warning">' . $penjualan->status . '</small>';
                }
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <button onclick="showDetail(`' . route('penjualan.show', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-success btn-flat"><i class="fa fa-eye"></i></button>
                <button onclick="editForm(`'. route('penjualan.update', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                <button onclick="deleteData(`' . route('penjualan.destroy', $penjualan->id_penjualan) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
            
            ';
            })
            ->rawColumns(['status','aksi'])
            ->make(true);
    }


    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->total_item = 0;
        $penjualan->metode = "";
        $penjualan->pengunjung = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon = 0;
        $penjualan->bayar = 0;
        $penjualan->diterima = 0;
        $penjualan->status = "";
        $penjualan->id_user = auth()->id();
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {
        $penjualan = Penjualan::findOrFail($request->id_penjualan);
        $penjualan->metode = $request->metode;
        $penjualan->pengunjung = $request->pengunjung;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = $request->diterima;
        $penjualan->status = "SUKSES";
        $penjualan->update();

        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $item->diskon = $request->diskon;
            $produk = Produk::find($item->id_produk);
            $produk->stok -= $item->jumlah;
            $produk->update();
        }

        return redirect()->route('transaksi.selesai');
    }

    public function show($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return format_money($detail->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return $detail->jumlah;
            })
            ->addColumn('subtotal', function ($detail) {
                return format_money($detail->subtotal);
            })
            ->rawColumns(['nama_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $penjualan = Penjualan::find($id);
        $penjualan->status = $request->status;
        
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        if($penjualan->status = "SALAH"){
            foreach ($detail as $item) {
                $produk = Produk::find($item->id_produk);
                if ($produk) {
                    $produk->stok += $item->jumlah;
                    $produk->update();
                }
    
                $item->delete();
            }
        }
        $penjualan->update();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (!$penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    }
}
