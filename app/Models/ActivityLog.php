<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description'];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // --- FUNGSI SAKTI BUAT NYATET LOG ---
    public static function insertLog($action, $description)
    {
        // Auth::id() akan otomatis mengambil ID dari akun (Admin/User/Owner) yang sedang melakukan aksi
        if (Auth::check()) {
            self::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description
            ]);
        }
    }
}
