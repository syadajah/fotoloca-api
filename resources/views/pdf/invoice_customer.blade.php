<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $transaction->nomor_unik }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #5A5A5A; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; letter-spacing: 2px; }
        .header p { margin: 5px 0; color: #666; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .item-table th, .item-table td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
        .item-table th { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 16px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #ddd; padding-top: 10px; }
        .kode-unik { font-size: 24px; font-weight: bold; text-align: center; padding: 15px; background: #f2f2f2; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>

    <div class="header">
        <h1>FOTOLOCA</h1>
        <p>Invoice Pembayaran</p>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>No. Transaksi:</strong> {{ $transaction->nomor_unik }}</td>
            <td class="text-right"><strong>Tanggal:</strong> {{ date('d M Y', strtotime($transaction->jadwal)) }}</td>
        </tr>
        <tr>
            <td><strong>Pelanggan:</strong> {{ $transaction->nama_pelanggan }}</td>
            <td class="text-right"><strong>Kasir:</strong> {{ $transaction->user->name ?? 'Kasir' }}</td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th>Deskripsi Item</th>
                <th class="text-right">Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Paket: {{ $transaction->product->nama_produk ?? 'Produk' }}</td>
                <td class="text-right">Rp {{ number_format($transaction->product->harga_produk ?? 0, 0, ',', '.') }}</td>
            </tr>
            @foreach($transaction->addons as $addon)
            <tr>
                <td>Layanan Tambahan: {{ $addon->nama_addon }}</td>
                <td class="text-right">Rp {{ number_format($addon->harga_addon, 0, ',', '.') }}</td>
            </tr>
            @endforeach

            @php
                $totalTagihan = $transaction->uang_bayar - $transaction->uang_kembali;
            @endphp
            <tr class="total-row">
                <td class="text-right" style="padding-top: 20px;">TOTAL TAGIHAN:</td>
                <td class="text-right" style="padding-top: 20px;">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right">Uang Bayar:</td>
                <td class="text-right">Rp {{ number_format($transaction->uang_bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-right">Kembalian:</td>
                <td class="text-right">Rp {{ number_format($transaction->uang_kembali, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="kode-unik">
        KODE UNIK: {{ $transaction->nomor_unik }}
    </div>

    <div class="footer">
        <p>Simpan kode unik ini untuk proses pengambilan hasil foto.</p>
        <p>Terima kasih telah mempercayakan momen berharga Anda kepada Fotoloca.</p>
    </div>

</body>
</html>
