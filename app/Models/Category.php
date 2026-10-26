<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';
    protected $primaryKey = 'id_category';
    protected $guarded = [];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_category', 'id_category');
    }
}
