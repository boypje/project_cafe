<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengeluaranController extends Controller
{
    public const KATEGORI = ['Bahan Baku', 'Gaji', 'Sewa', 'Listrik & Air', 'Gas', 'Operasional', 'Perlengkapan', 'Perawatan', 'Lainnya'];

    public function index()
    {
        return view('pengeluaran.index', [
            'kategori' => collect(self::KATEGORI)
                ->merge(Pengeluaran::distinct()->pluck('kategori')->filter())
                ->unique()->values(),
            'metode' => KasirController::METODE,
        ]);
    }

    public function data(Request $request)
    {
        $awal = $request->input('awal', today()->startOfMonth()->toDateString());
        $akhir = $request->input('akhir', today()->toDateString());

        $q = Pengeluaran::with('user')
            ->whereBetween('created_at', [Carbon::parse($awal)->startOfDay(), Carbon::parse($akhir)->endOfDay()])
            ->when($request->kategori, function ($q, $v) {
                $q->where('kategori', $v);
            })
            ->when($request->cari, function ($q, $v) {
                $q->where('deskripsi', 'like', "%$v%");
            });

        // Kasir hanya melihat pengeluaran yang ia catat sendiri.
        if (! auth()->user()->isAdmin()) {
            $q->where('id_user', auth()->id());
        }

        $jumlah = (int) (clone $q)->sum('nominal');
        $page = $q->orderByDesc('created_at')->paginate(25);

        return response()->json([
            'data' => collect($page->items())->map(function ($p) {
                return [
                    'id' => $p->id_pengeluaran,
                    'tanggal' => $p->created_at->format('Y-m-d'),
                    'jam' => $p->created_at->format('H:i'),
                    'deskripsi' => $p->deskripsi,
                    'kategori' => $p->kategori,
                    'metode' => $p->metode,
                    'nominal' => (int) $p->nominal,
                    'user' => optional($p->user)->name,
                ];
            }),
            'page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'total' => $page->total(),
            'jumlah' => $jumlah,
        ]);
    }

    private function validasi(Request $request)
    {
        return $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'deskripsi' => 'required|string|max:500',
            'kategori' => 'required|string|max:50',
            'metode' => ['required', Rule::in(KasirController::METODE)],
            'nominal' => 'required|integer|min:1',
        ], ['tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.']);
    }

    /** Tanggal pilihan digabung dengan jam sekarang (atau jam lama saat diedit). */
    private function waktu($tanggal, $lama = null)
    {
        $jam = $lama ? $lama->format('H:i:s') : now()->format('H:i:s');

        return Carbon::parse($tanggal . ' ' . $jam);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $p = new Pengeluaran(collect($data)->except('tanggal')->all());
        $p->id_user = auth()->id();
        $p->created_at = $this->waktu($data['tanggal']);
        $p->save();

        return response()->json(['message' => 'Pengeluaran dicatat.']);
    }

    public function update(Request $request, $id)
    {
        $p = $this->milik($id);
        $data = $this->validasi($request);
        $p->fill(collect($data)->except('tanggal')->all());
        $p->created_at = $this->waktu($data['tanggal'], $p->created_at);
        $p->save();

        return response()->json(['message' => 'Pengeluaran diperbarui.']);
    }

    public function destroy($id)
    {
        $this->milik($id)->delete();

        return response()->json(['message' => 'Pengeluaran dihapus.']);
    }

    private function milik($id)
    {
        $p = Pengeluaran::findOrFail($id);
        abort_unless(auth()->user()->isAdmin() || $p->id_user == auth()->id(), 403);

        return $p;
    }
}
