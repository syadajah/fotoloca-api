<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Kasih tau Laravel nama tabelnya (opsional sih kalau namanya udah jamak 'transactions', tapi biar aman)
    protected $table = 'transactions';

    // Daftar kolom yang diizinin buat diisi secara massal (Mass Assignment) dari Controller
    protected $fillable = [
        'id_users',
        'id_produk',
        'nomor_unik',
        'nama_pelanggan',
        'email_pelanggan',
        'jadwal',
        'uang_bayar',
        'uang_kembali',
    ];

    protected $casts = [
        'uang_bayar' => 'integer',
        'uang_kembali' => 'integer',
        'jadwal' => 'date',
    ];

    /**
     * Accessor untuk format jadwal ke 'd M Y'.
     */
    protected function jadwal(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y') : null,
        );
    }

    // =========================================================
    // RELASI ANTAR TABEL (JOIN)
    // Ini kepake banget nanti pas lu mau cetak INVOICE
    // =========================================================

    /**
     * Relasi ke tabel Users (Kasir yang melayani)
     */
    public function user()
    {
        // Parameter: (Nama Model Target, 'foreign_key_di_tabel_ini', 'primary_key_di_tabel_target')
        return $this->belongsTo(User::class, 'id_users', 'id');
    }

    /**
     * Relasi ke tabel Products (Paket foto yang dibeli)
     */
    public function product()
    {
        return $this->belongsTo(products::class, 'id_produk', 'id');
    }

    public function addons()
    {
        return $this->belongsToMany(AddOn::class, 'transaction_addon', 'transaction_id', 'addon_id');
        // Catatan: Pastiin 'AddOn::class' sesuai sama nama model Add-on lu ya
    }
}
