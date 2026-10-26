<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananTahan extends Model
{
    protected $table = 'pesanan_tahan';
    protected $primaryKey = 'id_pesanan_tahan';
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'pengunjung' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
