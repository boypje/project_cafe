<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $guarded = [];

    protected $casts = [
        'harga_jual' => 'integer',
        'harga_pokok' => 'integer',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'kelola_stok' => 'boolean',
        'tampil_kasir' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category', 'id_category');
    }

    /** Bahan/komponen yang ikut berkurang saat produk ini terjual (resep paket). */
    public function komponen()
    {
        return $this->belongsToMany(Produk::class, 'produk_komponen', 'id_produk', 'id_komponen')
            ->withPivot('jumlah');
    }

    public function isPaket()
    {
        return $this->komponen->isNotEmpty();
    }

    /** HPP efektif: harga pokok sendiri, atau jumlah HPP komponen untuk paket. */
    public function hppEfektif()
    {
        if ($this->harga_pokok > 0 || ! $this->isPaket()) {
            return (int) $this->harga_pokok;
        }

        return (int) $this->komponen->sum(function ($k) {
            return $k->harga_pokok * $k->pivot->jumlah;
        });
    }

    /** Porsi yang masih bisa dijual; null berarti stok tidak dibatasi. */
    public function stokTersedia()
    {
        if ($this->isPaket()) {
            $batas = $this->komponen
                ->filter(function ($k) {
                    return $k->kelola_stok;
                })
                ->map(function ($k) {
                    return intdiv(max($k->stok, 0), max($k->pivot->jumlah, 1));
                });

            return $batas->isEmpty() ? null : $batas->min();
        }

        return $this->kelola_stok ? (int) $this->stok : null;
    }
}
