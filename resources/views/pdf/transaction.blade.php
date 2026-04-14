<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #5A5A5A; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #333; }
        .header p { margin: 5px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN TRANSAKSI FOTOLOCA</h2>
        <p>Periode: {{ $startDate ? date('d M Y', strtotime($startDate)) : 'Semua Waktu' }} - {{ $endDate ? date('d M Y', strtotime($endDate)) : 'Sekarang' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nomor Unik</th>
                <th>Pelanggan</th>
                <th>Kasir</th>
                <th>Produk</th>
                <th>Omzet</th>
            </tr>
        </thead>
        <tbody>
            @php $totalOmzet = 0; @endphp
            @foreach($transactions as $index => $tx)
                @php 
                    $uangBayar = $tx->uang_bayar ?? 0;
                    $uangKembali = $tx->uang_kembali ?? 0;
                    $omzet = $uangBayar - $uangKembali; 
                    $totalOmzet += $omzet;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($tx->jadwal)) }}</td>
                    <td class="text-center">{{ $tx->nomor_unik ?? '-' }}</td>
                    <td>{{ $tx->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $tx->user?->name ?? 'Kasir (Terhapus)' }}</td>
                    <td>{{ $tx->product?->nama_produk ?? 'Produk (Terhapus)' }}</td>
                    <td class="text-right">Rp {{ number_format($omzet, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right" style="color: #D4AF37;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>