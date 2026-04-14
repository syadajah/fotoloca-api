<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // 👇 PERBAIKAN: Gunakan withTrashed() agar nama user yang udah dihapus tetep muncul di log
        $query = ActivityLog::with(['user' => function ($q) {
            $q->withTrashed();
        }]);
        // --- LOGIKA SAKTI PEMISAH LOG ---

        // Cek apakah ada parameter user_id di request (misal: ?user_id=5)
        if ($request->has('user_id') && $request->user_id != '') {

            // Cek Role: Cuma Admin atau Owner yang boleh "ngintip" log orang lain
            if ($user->role === 'admin' || $user->role === 'owner') {
                $query->where('user_id', $request->user_id);
            } else {
                // Kalau Kasir coba-coba kirim user_id orang lain lewat Postman/URL, 
                // kita paksa balik ke ID dia sendiri (Security Protection)
                $query->where('user_id', $user->id);
            }
        } else {
            // Jika TIDAK ADA parameter user_id (berarti klik tombol history global),
            // Maka semua role (termasuk Admin/Owner) cuma liat log miliknya sendiri.
            $query->where('user_id', $user->id);
        }

        $logs = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $logs
        ], 200);
    }
}
