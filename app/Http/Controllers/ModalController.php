<?php

namespace App\Http\Controllers;
use App\Models\Modal;


use Illuminate\Http\Request;

class ModalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('modal.index');
    }

    public function data()
    {
        $modal = Modal::orderBy('id_modal', 'asc')->get();

        return datatables()
            ->of($modal)
            ->addIndexColumn()
            ->addColumn('created_at', function ($modal){
                return tanggal_indonesia($modal->created_at, false);
            })
            ->addColumn('nominal', function ($modal){
                return format_money($modal->nominal);
            })
            ->addColumn('aksi', function ($modal) {
                return '
                <div class="btn-group">
                    <button onclick="editForm(`'. route('modal.update', $modal->id_modal) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"> Edit</i></button>
                    <button onclick="deleteData(`'. route('modal.destroy', $modal->id_modal) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"> Hapus</i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $modal = Modal::create($request->all());

        return response()->json('Data berhasil ditambahkan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modal = Modal::find($id);

        return response()->json($modal);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modal = Modal::find($id);
        $modal->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modal = Modal::find($id);
        $modal->delete();

        return response(null, 204);
    }
}
