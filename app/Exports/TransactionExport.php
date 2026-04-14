<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles; // Tambahin ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Tambahin ini

class TransactionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return ['Nomor Transaksi', 'Tanggal Jadwal', 'Nama Pelanggan', 'Nama Kasir', 'Produk / Paket', 'Uang Bayar', 'Uang Kembali', 'Total Omzet'];
    }

    public function map($transaction): array
    {
        $uang_bayar = $transaction->uang_bayar ?? 0;
        $uang_kembali = $transaction->uang_kembali ?? 0;
        $total_harga = $uang_bayar - $uang_kembali;

        return [
            $transaction->nomor_unik,
            date('d M Y', strtotime($transaction->jadwal)),
            $transaction->nama_pelanggan,
            $transaction->user->name ?? 'Kasir (Terhapus)',
            $transaction->product->nama_produk ?? 'Produk (Terhapus)',
            'Rp ' . number_format($uang_bayar, 0, ',', '.'),
            'Rp ' . number_format($uang_kembali, 0, ',', '.'),
            'Rp ' . number_format($total_harga, 0, ',', '.'),
        ];
    }

    // --- BIKIN HEADER JADI TEBAL (BOLD) ---
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]], // Baris 1 (Header) jadi Bold
        ];
    }
}
