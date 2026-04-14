<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class products extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'harga_produk',
        'deskripsi',
        'foto',
        'tier_level',
    ];

    public function category(){
        return $this->belongsTo(categories::class, 'id_kategori');
    }
}
