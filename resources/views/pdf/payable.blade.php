<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Inter', 'Helvetica', 'Arial', sans-serif; padding: 20px; margin: 0; color: #0f172a; }
        .header { display:flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .logo { width: 48px; height: 48px; border:1px solid #e2e8f0; border-radius: 8px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
        .logo img { max-width:100%; max-height:100%; object-fit:contain; }
        .badge { padding: 4px 10px; border-radius: 999px; background:#dcfce7; color:#15803d; font-weight:700; font-size:12px; }
        .card { border:1px solid #e2e8f0; border-radius:12px; padding:12px; margin-top:10px; }
        .title { font-size:11px; text-transform: uppercase; color:#64748b; letter-spacing:0.5px; font-weight:700; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        td { padding:4px 0; font-size:13px; }
        .barcode img { height: 36px; }
    </style>
</head>
<body>
    <div class="header">
        <div style="display:flex; gap:10px; align-items:center;">
            <div className="logo" class="logo">
                @if($store['logo'])
                    <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}">
                @else
                    <strong>{{ substr($store['name'],0,2) }}</strong>
                @endif
            </div>
            <div>
                <div style="font-weight:700; font-size:16px;">{{ $store['name'] }}</div>
                @if($store['address'])<div style="font-size:12px; color:#475569;">{{ $store['address'] }}</div>@endif
                <div style="font-size:12px; color:#475569;">{{ $store['phone'] ? 'Telp: '.$store['phone'].' • ' : '' }}{{ $store['email'] }}</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div class="badge">Hutang</div>
            <div style="font-size:16px; font-weight:700;">{{ $payable->document_number }}</div>
            <div style="font-size:12px; color:#475569;">Jatuh tempo: {{ $payable->due_date ?? '-' }}</div>
        </div>
    </div>

    <div class="card">
        <div class="title">Supplier</div>
        <div style="font-weight:700;">{{ $payable->supplier->name ?? '-' }}</div>
        @if($payable->supplier?->phone)<div style="font-size:12px; color:#475569;">{{ $payable->supplier->phone }}</div>@endif
    </div>

    <div class="card" style="display:flex; gap:12px;">
        <div style="flex:1;">
            <div class="title">Total</div>
            <div style="font-weight:700; font-size:16px;">{{ number_format($payable->total,0,',','.') }}</div>
        </div>
        <div style="flex:1;">
            <div class="title">Terbayar</div>
            <div style="font-weight:700; font-size:16px; color:#16a34a;">{{ number_format($payable->paid,0,',','.') }}</div>
        </div>
        <div style="flex:1;">
            <div class="title">Sisa</div>
            <div style="font-weight:700; font-size:16px; color:#c2410c;">
                {{ number_format(max(0, $payable->total - $payable->paid),0,',','.') }}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="title">Riwayat Pembayaran</div>
        <table>
            @forelse($payable->payments as $pay)
            <tr>
                <td>{{ \Carbon\Carbon::parse($pay->paid_at)->format('d M Y') }}</td>
                <td>{{ strtoupper($pay->method ?? '-') }}</td>
                <td style="text-align:right;">{{ number_format($pay->amount,0,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" style="color:#94a3b8;">Belum ada pembayaran</td></tr>
            @endforelse
        </table>
    </div>

    <div style="margin-top:12px; display:flex; justify-content: space-between; align-items:center;">
        <div style="font-size:11px; color:#94a3b8;">Dicetak pada {{ now()->format('d M Y') }}</div>
        <div class="barcode">
            <img src="{{ $barcode }}" alt="barcode">
            <div style="font-size:10px; text-align:right;">{{ $payable->document_number }}</div>
        </div>
    </div>
</body>
</html>
