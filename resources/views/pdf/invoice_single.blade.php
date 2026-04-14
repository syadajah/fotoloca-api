<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('file:///usr/share/fonts/truetype/dejavu/DejaVuSans.ttf');
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: #111;
            line-height: 1.6;
            text-align: center;
            /* Kunci centering DomPDF */
        }

        .wrapper {
            width: 180mm;
            /* ✅ A4 210mm - 10mm margin (5mm kiri + 5mm kanan) */
            margin: 0 auto;
            text-align: left;
            /* Tambahkan padding dalam sedikit agar teks tidak nempel garis tepi */
            padding: 8mm 0;
        }


        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #000;
            padding-bottom: 18px;
        }

        .header h2 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
            font-weight: 800;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            color: #444;
            font-weight: 500;
        }

        table.info {
            width: 100%;
            margin: 20px 0;
        }

        table.info td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 13px;
        }

        table.info .label {
            width: 35%;
            font-weight: 600;
            color: #333;
        }

        table.info .value {
            width: 65%;
        }

        table.items {
            width: 100%;
            margin: 25px 0;
            border-collapse: collapse;
        }

        table.items th {
            border-bottom: 2px solid #000;
            padding: 10px 0;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.items td {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        table.items .price {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            white-space: nowrap;
        }

        table.items .addon {
            padding-left: 18px;
            font-style: italic;
            color: #555;
        }

        .total-section {
            border-top: 3px solid #000;
            border-bottom: 2px solid #000;
            padding: 15px 0;
            margin: 25px 0;
            background: #f8f9fa;
        }

        .total-section table {
            width: 100%;
            margin: 0;
        }

        .total-section td {
            padding: 4px 0;
            font-size: 13px;
        }

        .total-section .grand-total {
            font-size: 16px;
            font-weight: 800;
            color: #000;
        }

        .kode-unik {
            text-align: center;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 4px;
            margin: 30px auto;
            padding: 14px 10px;
            border: 2px dashed #000;
            background: #fff9e6;
            font-family: 'Courier New', monospace;
            max-width: 85%;
        }

        .footer {
            text-align: center;
            margin-top: 35px;
            font-size: 11px;
            color: #666;
            font-style: italic;
            line-height: 1.5;
        }

        .footer p {
            margin: 3px 0;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h2>FOTOLOCA</h2>
            <p>Invoice & Struk Pembayaran Resmi</p>
        </div>

        <table class="info">
            <tr>
                <td class="label">No. Unik</td>
                <td class="value">: {{ $transaction->nomor_unik ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="value">:
                    {{ $transaction->created_at ? date('d M Y H:i', strtotime($transaction->created_at)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pelanggan</td>
                <td class="value">: {{ $transaction->nama_pelanggan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kasir</td>
                <td class="value">: {{ $transaction->user?->name ?? 'Sistem' }}</td>
            </tr>
            <tr>
                <td class="label">Jadwal</td>
                <td class="value">: {{ $transaction->jadwal ? date('d M Y', strtotime($transaction->jadwal)) : '-' }}
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 70%;">Item / Layanan</th>
                    <th class="price">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: 600;">{{ $transaction->product?->nama_produk ?? 'Produk Dihapus' }}</td>
                    <td class="price">Rp {{ number_format($transaction->product?->harga_produk ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @if ($transaction->addons && $transaction->addons->count() > 0)
                    @foreach ($transaction->addons as $addon)
                        <tr>
                            <td class="addon">+ {{ $addon->nama_addon ?? 'Addon' }}</td>
                            <td class="price">Rp {{ number_format($addon->harga_addon ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="total-section">
            <table>
                <tr class="grand-total">
                    <td>Total Tagihan</td>
                    <td class="price">Rp
                        {{ number_format(($transaction->product?->harga_produk ?? 0) + ($transaction->addons?->sum('harga_addon') ?? 0), 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Tunai</td>
                    <td class="price">Rp {{ number_format($transaction->uang_bayar ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kembali</td>
                    <td class="price">Rp {{ number_format($transaction->uang_kembali ?? 0, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="kode-unik">
            {{ $transaction->nomor_unik ?? 'XXX-XXX' }}
        </div>

        <div class="footer">
            <p>Simpan invoice ini sebagai bukti pengambilan hasil foto.</p>
            <p>Terima kasih telah menggunakan layanan FotoLoca!</p>
        </div>
    </div>
</body>

</html>
