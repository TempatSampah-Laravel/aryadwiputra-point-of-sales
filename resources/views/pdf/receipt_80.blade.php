<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { font-family: monospace; width: 80mm; margin: 0; padding: 8px; font-size: 12px; }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .line { border-bottom: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        .barcode img { height: 32px; }
    </style>
</head>
<body>
    @if($store['logo_data'] ?? false)
        <div class="center">
            <img src="{{ $store['logo_data'] }}" alt="{{ $store['name'] }}" style="height:32px; margin-bottom:6px;">
        </div>
    @elseif(!empty($store['logo']))
        <div class="center">
            <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}" style="height:32px; margin-bottom:6px;">
        </div>
    @endif
    <div class="center bold">{{ $store['name'] }}</div>
    @if($store['address'])<div class="center">{{ $store['address'] }}</div>@endif
    @if($store['phone'])<div class="center">Telp: {{ $store['phone'] }}</div>@endif
    <div class="line"></div>
    <div>Invoice: {{ $transaction->invoice }}</div>
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
            <td>Subtotal</td>
            <td style="text-align:right;">{{ number_format($transaction->grand_total,0,',','.') }}</td>
        </tr>
        @if($transaction->discount)
        <tr>
            <td>Diskon</td>
            <td style="text-align:right;">-{{ number_format($transaction->discount,0,',','.') }}</td>
        </tr>
        @endif
        @if($transaction->shipping_cost)
        <tr>
            <td>Ongkir</td>
            <td style="text-align:right;">{{ number_format($transaction->shipping_cost,0,',','.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="bold">Total</td>
            <td style="text-align:right;" class="bold">{{ number_format($transaction->grand_total,0,',','.') }}</td>
        </tr>
    </table>
    <div class="line"></div>
    <div class="center">
        <div class="barcode">
            <img src="{{ $barcode }}" alt="barcode">
        </div>
        <div style="font-size:11px;">{{ $transaction->invoice }}</div>
        <div>Terima kasih!</div>
    </div>
</body>
</html>
