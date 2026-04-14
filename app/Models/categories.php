<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Wajib di-import

class categories extends Model
{
    use SoftDeletes; // 2. Aktifkan SoftDeletes

    protected $fillable = ['nama_kategori'];

    public function products()
    {
        return $this->hasMany(products::class, 'id_kategori');
    }
}
