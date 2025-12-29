@php
    $fontFamily = "'Inter', 'Helvetica', 'Arial', sans-serif";
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: {{ $fontFamily }};
            margin: 0;
            padding: 24px;
            color: #0f172a;
        }

        .header {
            margin-bottom: 20px;
        }

        .store {
            display: flex;
            gap: 12px;
        }

        .logo {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #e0f2fe;
            color: #0284c7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th {
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #475569;
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 8px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }

        .right {
            text-align: right;
        }

        .total {
            font-size: 16px;
            font-weight: 700;
            color: #0ea5e9;
        }

        .footer {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .barcode {
            text-align: center;
        }

        .barcode img {
            height: 28px;
        }
    </style>
</head>

<body>
    <table class="header" style="width:100%; table-layout:fixed;">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <div class="store">
                    <div class="logo" style="width:60px;height:60px;">
                        @if ($store['logo_data'])
                            <img src="{{ $store['logo_data'] }}" alt="{{ $store['name'] }}">
                        @elseif($store['logo'])
                            <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}">
                        @else
                            <strong>{{ substr($store['name'], 0, 2) }}</strong>
                        @endif
                    </div>
                    <div>
                        <h2 style="margin:0;">{{ $store['name'] }}</h2>
                        @if ($store['address'])
                            <div style="font-size:12px;color:#475569; margin-top:2px;">{{ $store['address'] }}</div>
                        @endif
                        <div style="font-size:12px;color:#475569; margin-top:2px;">
                            {{ $store['phone'] ? 'Telp: ' . $store['phone'] . ' • ' : '' }}{{ $store['email'] }}
                        </div>
                    </div>
                </div>
            </td>
            <td style="width:40%; vertical-align:top; text-align:right;">
                <div class="badge">INVOICE</div>
                <div style="font-size:18px;font-weight:700; margin-top:4px;">{{ $transaction->invoice }}</div>
                <div style="font-size:12px;color:#475569; margin-top:4px;">
                    {{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width:100%; margin-top:12px; table-layout:fixed;">
        <tr>
            <td style="width:50%; vertical-align:top; font-size:13px;">
                <div style="color:#64748b;font-weight:600;">Pelanggan</div>
                <div style="font-weight:700; margin-top:2px;">{{ $transaction->customer->name ?? 'Umum' }}</div>
                @if ($transaction->customer?->phone)
                    <div style="color:#475569; margin-top:2px;">{{ $transaction->customer->phone }}</div>
                @endif
                @if ($transaction->customer?->address)
                    <div style="color:#475569; margin-top:2px;">{{ $transaction->customer->address }}</div>
                @endif
            </td>
            <td style="width:50%; vertical-align:top; font-size:13px; text-align:right;">
                <div style="color:#64748b;font-weight:600;">Kasir</div>
                <div style="font-weight:700; margin-top:2px;">{{ $transaction->cashier->name ?? '-' }}</div>
                <div style="margin-top:6px;">
                    <div><strong>Status:</strong> {{ $transaction->payment_status }}</div>
                    <div><strong>Metode:</strong> {{ $transaction->payment_method }}</div>
                    @if ($transaction->receivable && $transaction->receivable->due_date)
                        <div><strong>Jatuh tempo:</strong> {{ $transaction->receivable->due_date }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="right">Qty</th>
                <th class="right">Harga</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->details as $index => $detail)
                <tr style="background: {{ $index % 2 === 0 ? '#f8fafc' : '#fff' }};">
                    <td>{{ $detail->product->title ?? 'Produk' }}</td>
                    <td class="right">{{ $detail->qty }}</td>
                    <td class="right">{{ number_format($detail->price / max(1, $detail->qty), 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="barcode" style="margin-top: 20px">
            <img src="{{ $barcode }}" alt="barcode">
            <div style="font-size:10px;color:#475569;">{{ $transaction->invoice }}</div>
        </div>
        <div style="font-size:11px;color:#94a3b8; text-align:center; margin-top: 20px;">
            Terima kasih atas kepercayaan Anda.
        </div>
    </div>
</body>

</html>
