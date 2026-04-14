<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboard()
    {
        try {
            // =========================================================
            // 1. INFO PENGGUNA BERJALAN (ADMIN & KASIR) - UDAH DIUPDATE
            // =========================================================

            // Hitung Admin
            $totalAdmin = User::where('role', 'admin')->count();
            $adminAktif = User::where('role', 'admin')->where('is_active', true)->count();

            // Hitung Kasir
            $totalKasir = User::where('role', 'kasir')->count();
            $kasirAktif = User::where('role', 'kasir')->where('is_active', true)->count();

            // =========================================================
            // 2. Info Transaksi Bulanan
            // =========================================================
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $lastMonth = now()->subMonth()->month;
            $lastMonthYear = now()->subMonth()->year;

            // Hitung bulan ini
            $transaksiBulanIni = Transaction::whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)->get();
            $totalTransaksi = $transaksiBulanIni->count();

            $pendapatanBulanIni = 0;
            foreach ($transaksiBulanIni as $tx) {
                $pendapatanBulanIni += ($tx->uang_bayar - $tx->uang_kembali);
            }

            // Hitung bulan lalu
            $transaksiBulanLalu = Transaction::whereMonth('created_at', $lastMonth)
                ->whereYear('created_at', $lastMonthYear)->get();

            $pendapatanBulanLalu = 0;
            foreach ($transaksiBulanLalu as $tx) {
                $pendapatanBulanLalu += ($tx->uang_bayar - $tx->uang_kembali);
            }

            // Hitung Persentase Keuntungan
            $persentase = 0;
            if ($pendapatanBulanLalu > 0) {
                $persentase = (($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100;
            } elseif ($pendapatanBulanIni > 0) {
                $persentase = 100;
            }

            // =========================================================
            // 3. Bundle Produk Terlaris (Top 3)
            // =========================================================
            $topProducts = Transaction::select('id_produk', DB::raw('count(*) as total_dipesan'))
                ->groupBy('id_produk')
                ->orderByDesc('total_dipesan')
                ->take(3)
                ->with(['product' => function ($query) {
                    $query->withTrashed()->with(['category' => function ($qCat) {
                        $qCat->withTrashed();
                    }]);
                }])
                ->get();

            // =========================================================
            // 4. Ambil Semua Tanggal Booking Unik
            // =========================================================
            $bookedDates = Transaction::select('jadwal')
                ->whereNotNull('jadwal')
                ->distinct()
                ->pluck('jadwal');

            // =========================================================
            // 5. KEMBALIKAN RESPONSE JSON
            // =========================================================
            return response()->json([
                'success' => true,
                'data' => [
                    // 👇 TAMBAHAN DATA ADMIN DI SINI
                    'admin' => [
                        'total' => $totalAdmin,
                        'aktif' => $adminAktif
                    ],
                    'kasir' => [
                        'total' => $totalKasir,
                        'aktif' => $kasirAktif
                    ],
                    'transaksi' => [
                        'total_transaksi' => $totalTransaksi,
                        'total_pendapatan' => $pendapatanBulanIni,
                        'persentase' => round($persentase, 1)
                    ],
                    'top_products' => $topProducts,
                    'booked_dates' => $bookedDates
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
