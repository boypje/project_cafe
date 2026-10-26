<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';
    protected $primaryKey = 'id_stok_mutasi';
    protected $guarded = [];

    public const TIPE = [
        'penjualan' => 'Penjualan',
        'batal' => 'Pembatalan',
        'masuk' => 'Stok Masuk',
        'keluar' => 'Rusak/Terbuang',
        'koreksi' => 'Stok Opname',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
