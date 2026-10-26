<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('setting.index', ['setting' => Setting::toko()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:500',
            'kota' => 'nullable|string|max:20',
            'telepon' => 'nullable|string|max:50',
            'pajak_persen' => 'required|numeric|min:0|max:100',
            'footer_nota' => 'nullable|string|max:255',
            'blokir_stok_habis' => 'required|boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        $setting = Setting::first() ?? new Setting(['path_logo' => '']);
        foreach (['nama_usaha', 'alamat', 'kota', 'telepon', 'footer_nota'] as $kolom) {
            $setting->$kolom = $data[$kolom] ?? '';
        }
        $setting->pajak_persen = $data['pajak_persen'];
        $setting->blokir_stok_habis = (bool) $data['blokir_stok_habis'];

        if ($request->hasFile('logo')) {
            $nama = 'logo-' . date('YmdHis') . '.' . $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('img'), $nama);
            $setting->path_logo = '/img/' . $nama;
        }
        $setting->save();

        return response()->json(['message' => 'Pengaturan disimpan.', 'path_logo' => $setting->path_logo]);
    }
}
