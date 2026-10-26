<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_penjualan', 'id_penjualan');
    }

    /** Transaksi yang benar-benar dibayar (aplikasi lama menyisakan draft tanpa metode). */
    public function scopeValid($query)
    {
        return $query->whereNotNull('penjualan.metode')->where('penjualan.metode', '<>', '');
    }

    public function getNomorAttribute()
    {
        return 'TRX-' . str_pad($this->id_penjualan, 6, '0', STR_PAD_LEFT);
    }
}
