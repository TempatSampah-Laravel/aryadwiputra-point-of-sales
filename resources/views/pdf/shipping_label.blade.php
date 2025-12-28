<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { font-family: 'Inter', 'Helvetica', 'Arial', sans-serif; width: 160mm; height: 110mm; padding: 12mm; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px; }
        .store { display: flex; gap: 10px; }
        .logo { width: 40px; height: 40px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .section { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 10px; }
        .title { font-size: 10px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; font-weight: 700; }
        .barcode img { height: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="store">
            <div class="logo">
                @if($store['logo'])
                    <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}">
                @else
                    <strong>{{ substr($store['name'],0,2) }}</strong>
                @endif
            </div>
            <div>
                <div style="font-weight:700;">{{ $store['name'] }}</div>
                @if($store['address'])<div style="font-size:10px;color:#475569;">{{ $store['address'] }}</div>@endif
                <div style="font-size:10px;color:#475569;">
                    {{ $store['phone'] ? 'Telp: '.$store['phone'].' • ' : '' }}{{ $store['email'] }}
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:10px;color:#94a3b8;">Invoice</div>
            <div style="font-size:14px;font-weight:700;">{{ $transaction->invoice }}</div>
            <div style="font-size:10px;color:#94a3b8;">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="title">Penerima</div>
        <div style="font-size:14px;font-weight:700;">{{ $transaction->customer->name ?? 'Umum' }}</div>
        @if($transaction->customer?->phone)<div style="font-size:12px;color:#475569;">{{ $transaction->customer->phone }}</div>@endif
        @if($transaction->customer?->address)<div style="font-size:12px;color:#475569;">{{ $transaction->customer->address }}</div>@endif
    </div>

    <div class="section">
        <div class="title">Detail Order</div>
        <div style="display:flex; justify-content: space-between; font-size:12px;">
            <span>Tanggal</span>
            <span>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y') }}</span>
        </div>
        <div style="display:flex; justify-content: space-between; font-size:12px;">
            <span>Jumlah Item</span>
            <span>{{ $transaction->details->count() }} item</span>
        </div>
        <div style="display:flex; justify-content: space-between; font-size:12px; font-weight:700;">
            <span>Total</span>
            <span>{{ number_format($transaction->grand_total,0,',','.') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="title">Produk</div>
        <div style="font-size:12px; line-height:1.4;">
            {{ $transaction->details->map(fn($d) => ($d->product->title ?? 'Produk')." ({$d->qty}x)")->join(', ') }}
        </div>
    </div>

    <div style="display:flex; justify-content: space-between; align-items:center; margin-top:12px;">
        <div style="font-size:10px; color:#94a3b8;">
            Kasir: {{ $transaction->cashier->name ?? '-' }}
        </div>
        <div class="barcode">
            <img src="{{ $barcode }}" alt="barcode">
            <div style="font-size:10px; text-align:right;">{{ $transaction->invoice }}</div>
        </div>
    </div>
</body>
</html>
