<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id_user');
        return $this->belongsTo(User::class, 'id', 'id_user');
    }
}
