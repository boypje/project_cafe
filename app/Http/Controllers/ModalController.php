<?php

namespace App\Http\Controllers;

use App\Models\Modal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ModalController extends Controller
{
    public function index()
    {
        return view('modal.index');
    }

    public function data()
    {
        return response()->json(Modal::orderByDesc('created_at')->get()->map(function ($m) {
            return [
                'id' => $m->id_modal,
                'tanggal' => $m->created_at ? $m->created_at->format('Y-m-d') : null,
                'deskripsi' => $m->deskripsi,
                'nominal' => (int) $m->nominal,
            ];
        }));
    }

    private function validasi(Request $request)
    {
        return $request->validate([
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'nominal' => 'required|integer|min:1',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $m = new Modal(['deskripsi' => $data['deskripsi'], 'nominal' => $data['nominal']]);
        $m->created_at = Carbon::parse($data['tanggal'] . ' ' . now()->format('H:i:s'));
        $m->save();

        return response()->json(['message' => 'Modal dicatat.']);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validasi($request);
        $m = Modal::findOrFail($id);
        $jam = $m->created_at ? $m->created_at->format('H:i:s') : '00:00:00';
        $m->fill(['deskripsi' => $data['deskripsi'], 'nominal' => $data['nominal']]);
        $m->created_at = Carbon::parse($data['tanggal'] . ' ' . $jam);
        $m->save();

        return response()->json(['message' => 'Modal diperbarui.']);
    }

    public function destroy($id)
    {
        Modal::findOrFail($id)->delete();

        return response()->json(['message' => 'Modal dihapus.']);
    }
}
