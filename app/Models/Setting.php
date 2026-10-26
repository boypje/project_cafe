<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'setting';
    protected $primaryKey = 'id_setting';
    protected $guarded = [];

    protected $casts = [
        'pajak_persen' => 'float',
        'blokir_stok_habis' => 'boolean',
    ];

    public static function toko()
    {
        return static::first() ?? new static([
            'nama_usaha' => config('app.name'),
            'alamat' => '',
            'telepon' => '',
            'path_logo' => '',
            'pajak_persen' => 0,
            'blokir_stok_habis' => false,
        ]);
    }
}
