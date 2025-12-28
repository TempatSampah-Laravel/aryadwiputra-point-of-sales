<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { font-family: monospace; width: 58mm; margin: 0; padding: 6px; font-size: 11px; }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .line { border-bottom: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        .barcode img { height: 28px; }
    </style>
</head>
<body>
    @if($store['logo_data'] ?? false)
        <div class="center">
            <img src="{{ $store['logo_data'] }}" alt="{{ $store['name'] }}" style="height:28px; margin-bottom:4px;">
        </div>
    @elseif(!empty($store['logo']))
        <div class="center">
            <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}" style="height:28px; margin-bottom:4px;">
        </div>
    @endif
    <div class="center bold">{{ $store['name'] }}</div>
    @if($store['phone'])<div class="center">Telp: {{ $store['phone'] }}</div>@endif
    <div class="line"></div>
    <div>Inv: {{ $transaction->invoice }}</div>
    <div>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</div>
    <div class="line"></div>
    <table>
        @foreach($transaction->details as $item)
        <tr>
            <td colspan="3">{{ $item->product->title ?? 'Produk' }}</td>
        </tr>
        <tr>
            <td>{{ $item->qty }}x</td>
            <td style="text-align:right;">{{ number_format($item->price / max(1,$item->qty),0,',','.') }}</td>
            <td style="text-align:right;">{{ number_format($item->price,0,',','.') }}</td>
        </tr>
        @endforeach
    </table>
    <div class="line"></div>
    <table>
        <tr>
            <td>Total</td>
            <td style="text-align:right;" class="bold">{{ number_format($transaction->grand_total,0,',','.') }}</td>
        </tr>
    </table>
    <div class="line"></div>
    <div class="center">
        <div class="barcode">
            <img src="{{ $barcode }}" alt="barcode">
        </div>
        <div style="font-size:10px;">{{ $transaction->invoice }}</div>
        <div>Terima kasih!</div>
    </div>
</body>
</html>
