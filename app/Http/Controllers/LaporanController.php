<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\LaporanService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index', ['setting' => Setting::toko()]);
    }

    public function data(Request $request, LaporanService $laporan)
    {
        $data = $request->validate([
            'awal' => 'required|date',
            'akhir' => 'required|date|after_or_equal:awal',
        ]);

        return response()->json($laporan->lengkap($data['awal'], $data['akhir']));
    }
}
