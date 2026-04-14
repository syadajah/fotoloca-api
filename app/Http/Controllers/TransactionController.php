<?php

namespace App\Http\Controllers;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendInvoiceEmailJob;
use App\Models\ActivityLog;
use App\Models\AddOn;
use App\Models\products;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil user yang sedang login
        $user = Auth::user();

        // 2. Query dasar dengan relasi produk & kategori (mendukung soft delete)
        $query = Transaction::with([
            'product' => function ($q) {
                $q->withTrashed()->with(['category' => function ($qCat) {
                    $qCat->withTrashed();
                }]);
            },
            // 👇 PERBAIKAN: Tambahkan fungsi withTrashed() untuk relasi user/kasir
            'user' => function ($qUser) {
                $qUser->withTrashed();
            }
        ]);

        // --- LOGIKA PEMISAH RIWAYAT TRANSAKSI ---
        // Jika yang login BUKAN admin dan BUKAN owner (berarti Kasir)
        if ($user->role !== 'admin' && $user->role !== 'owner') {
            // Kasir cuma boleh liat transaksi yang id_users-nya sama dengan ID dia
            $query->where('id_users', $user->id);
        }

        // 1. Filter Pencarian (Case Insensitive)
        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama_pelanggan) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nomor_unik) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('product', function ($q) use ($search) {
                        // Cari juga di nama produk yang sudah di-soft delete
                        $q->withTrashed()->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
                    });
            });
        }

        // 2. Filter Tanggal
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 3. Urutkan berdasarkan waktu pembuatan terbaru
        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi berhasil dimuat',
            'data' => $transactions
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|integer',
            'nama_pelanggan' => 'required|string',
            'email_pelanggan' => 'nullable|email',
            'jadwal' => 'required|date',
            'uang_bayar' => 'required|integer',
            'nomor_unik' => 'required|string',
            'addons' => 'nullable|array',
        ]);

        $cekJadwal = Transaction::where('id_produk', $request->id_produk)
            ->whereDate('jadwal', $request->jadwal)
            ->exists();

        if ($cekJadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Waduh! Tanggal ' . date('d M Y', strtotime($request->jadwal)) . ' sudah di-booking.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Gunakan withTrashed() biar bisa transaksi meskipun produk disembunyikan
            $product = products::withTrashed()->findOrFail($request->id_produk);
            $total_harga = $product->harga_produk;

            if ($request->has('addons') && is_array($request->addons) && count($request->addons) > 0) {
                $total_addon_price = AddOn::whereIn('id', $request->addons)->sum('harga_addon');
                $total_harga += $total_addon_price;
            }

            $uang_kembali = $request->uang_bayar - $total_harga;

            if ($uang_kembali < 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Uang tidak cukup! Total tagihan: Rp " . number_format($total_harga, 0, ',', '.')
                ], 400);
            }

            $transaction = Transaction::create([
                'id_users' => Auth::id(), // Lebih rapi pakai Auth::id()
                'id_produk' => $request->id_produk,
                'nama_pelanggan' => $request->nama_pelanggan,
                'email_pelanggan' => $request->email_pelanggan,
                'jadwal' => $request->jadwal,
                'uang_bayar' => $request->uang_bayar,
                'uang_kembali' => $uang_kembali,
                'nomor_unik' => $request->nomor_unik,
            ]);

            if ($request->has('addons') && is_array($request->addons) && count($request->addons) > 0) {
                $transaction->addons()->sync($request->addons);
            }

            DB::commit();

            // Catat ke Log Aktivitas
            ActivityLog::insertLog(
                'Tambah Transaksi',
                'Membuat transaksi baru: ' . $request->nomor_unik . ' untuk pelanggan ' . $request->nama_pelanggan
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'data' => $transaction->load(['addons', 'user', 'product'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTransactionSetup($id_produk)
    {
        try {
            $bookedDates = Transaction::where('id_produk', $id_produk)
                ->pluck('jadwal');

            $addons = AddOn::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'booked_dates' => $bookedDates,
                    'addons' => $addons
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal narik setup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $query = Transaction::with([
            'product' => function ($q) {
                $q->withTrashed();
            },
            'user' => function ($qUser) {
                $qUser->withTrashed();
            }
        ]);

        if ($user->role !== 'admin' && $user->role !== 'owner') {
            $query->where('id_users', $user->id);
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('jadwal', '>=', $request->start_date); // Pakai jadwal biar sesuai realita photo shoot
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('jadwal', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('jadwal', 'asc')->get();
        $type = $request->query('type', 'excel'); // Nangkep request type

        // --- TAMBAHAN LOG AKTIVITAS ---
        ActivityLog::insertLog(
            'Export Laporan',
            'Mengekspor laporan transaksi ke format ' . strtoupper($type)
        );

        // JIKA MINTA PDF
        if ($type === 'pdf') {
            $pdf = Pdf::loadView('pdf.transaction', [
                'transactions' => $transactions,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date
            ]);
            // Set kertas landscape kalau kolomnya banyak
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download('Laporan_Transaksi_' . date('Ymd') . '.pdf');
        }

        // JIKA MINTA EXCEL
        return Excel::download(new TransactionExport($transactions), 'Laporan_Transaksi_' . date('Ymd') . '.xlsx');
    }

    public function sendInvoiceEmail($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            if (empty($transaction->email_pelanggan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email pelanggan tidak dicantumkan saat transaksi.'
                ], 400);
            }

            // Dispatch ke queue biar nggak blocking
            SendInvoiceEmailJob::dispatch($transaction)->onQueue('emails');

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dikirim ke email pelanggan!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printInvoice($id)
    {
        try {
            $transaction = Transaction::with(['product', 'user', 'addons'])->findOrFail($id);

            $pdf = Pdf::loadView('pdf.invoice_single', compact('transaction'));

            // ✅ MARGIN TIPIS 5MM (Mentok tapi aman dari terpotong printer)
            $pdf->setOption('margin_top', '5mm');
            $pdf->setOption('margin_bottom', '5mm');
            $pdf->setOption('margin_left', '5mm');
            $pdf->setOption('margin_right', '5mm');

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('defaultFont', 'DejaVu Sans');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            $pdfContent = $pdf->output();

            if (empty($pdfContent) || strlen($pdfContent) < 1000) {
                return response()->json(['success' => false, 'message' => 'PDF kosong'], 500);
            }

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Length', strlen($pdfContent))
                ->header('Content-Disposition', 'inline; filename="Invoice-' . $transaction->nomor_unik . '.pdf"');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
