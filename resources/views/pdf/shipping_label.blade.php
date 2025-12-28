@php
    $formatPrice = fn($v) => 'Rp ' . number_format($v ?? 0, 0, ',', '.');
    $formatDate = fn($v) => \Carbon\Carbon::parse($v)->format('d M Y');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        /* Ukuran 150mm x 100mm dalam Points */
        @page {
            margin: 0;
            size: 425.2pt 283.5pt;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            width: 425.2pt;
            height: 283.5pt;
            color: #1e293b;
        }

        .container {
            padding: 15pt;
            position: relative;
            height: 253.5pt;
            /* Tinggi dikurangi padding */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td {
            vertical-align: top;
            overflow: hidden;
        }

        .header td {
            vertical-align: middle;
        }

        .logo-box {
            width: 40pt;
            height: 40pt;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .divider {
            border-top: 1px solid #e2e8f0;
            margin: 8pt 0;
        }

        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 6pt;
            padding: 6pt;
            height: 65pt;
            /* Tinggi tetap agar tidak mendorong footer */
        }

        .title-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 3pt;
        }

        .text-bold {
            font-size: 10pt;
            font-weight: bold;
        }

        .text-small {
            font-size: 8pt;
            line-height: 1.2;
        }

        .text-muted {
            color: #64748b;
            font-size: 7pt;
        }

        /* Footer dipaksa berada di bawah */
        .footer-absolute {
            position: absolute;
            bottom: 15pt;
            left: 15pt;
            right: 15pt;
            border-top: 1px solid #e2e8f0;
            padding-top: 8pt;
        }

        .barcode-img {
            height: 28pt;
            width: auto;
        }

        ul {
            margin: 0;
            padding-left: 12pt;
        }

        li {
            font-size: 8pt;
            margin-bottom: 2pt;
        }
    </style>
</head>

<body>
    <div class="container">
        <table class="header">
            <tr>
                <td width="50pt">
                    <div class="logo-box">
                        @if ($store['logo_data'] ?? false)
                            <img src="{{ $store['logo_data'] }}" style="width: 100%;">
                        @else
                            <span style="line-height: 40pt; font-weight: bold;">{{ substr($store['name'], 0, 2) }}</span>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="text-bold">{{ $store['name'] }}</div>
                    <div class="text-small text-muted">{{ Str::limit($store['address'], 60) }}</div>
                    <div class="text-small text-muted">{{ $store['phone'] }} | {{ $store['email'] }}</div>
                </td>
                <td width="100pt" style="text-align: right;">
                    <div class="text-muted">INVOICE</div>
                    <div class="text-bold" style="font-size: 10pt; color: #000;">{{ $transaction->invoice }}</div>
                    <div class="text-small">{{ $formatDate($transaction->created_at) }}</div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <table>
            <tr>
                <td style="padding-right: 5pt;">
                    <div class="section-box">
                        <div class="title-label">Penerima</div>
                        <div class="text-bold">{{ $transaction->customer->name ?? 'Umum' }}</div>
                        <div class="text-small">{{ $transaction->customer->phone ?? '-' }}</div>
                        <div class="text-small text-muted">
                            {{ Str::limit($transaction->customer->address ?? 'No Address', 80) }}</div>
                    </div>
                </td>
                <td style="padding-left: 5pt;">
                    <div class="section-box">
                        <div class="title-label">Ringkasan Pesanan</div>
                        <table class="text-small">
                            <tr>
                                <td>Item</td>
                                <td style="text-align: right;">{{ $transaction->details->count() }} unit</td>
                            </tr>
                            <tr>
                                <td style="padding-top: 15pt;" class="text-bold">Total</td>
                                <td style="padding-top: 15pt; text-align: right;" class="text-bold">
                                    {{ $formatPrice($transaction->grand_total) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="title-label" style="margin-top: 10pt;">Daftar Produk</div>
        <div style="height: 50pt; overflow: hidden;">
            <ul>
                @foreach ($transaction->details->take(3) as $detail)
                    <li>{{ Str::limit($detail->product->title, 40) }} ({{ $detail->qty }}x)</li>
                @endforeach
            </ul>
        </div>

        <div class="footer-absolute">
            <table class="header">
                <tr>
                    <td class="text-muted">
                        Kasir: {{ $transaction->cashier->name ?? '-' }}<br>
                        Dicetak: {{ now()->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align: right;">
                        <img src="{{ $barcode }}" class="barcode-img">
                        <div class="text-muted" style="letter-spacing: 2px;">{{ $transaction->invoice }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
