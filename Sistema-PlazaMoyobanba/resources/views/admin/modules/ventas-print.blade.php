<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ strtoupper($sale->document_type) }} {{ $sale->code }}</title>
    <style>
        body { font-family: Arial, sans-serif; color:#0f172a; margin: 24px; }
        .wrap { max-width: 860px; margin: 0 auto; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 16px; }
        .chip { border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-weight:700; }
        .box { border:1px solid #e2e8f0; border-radius:10px; padding:12px; margin-top: 12px; }
        table { width:100%; border-collapse: collapse; font-size: 13px; }
        th, td { border-bottom:1px solid #e2e8f0; padding:8px; text-align:left; }
        th { background:#f8fafc; }
        .totals { margin-top: 14px; width: 320px; margin-left:auto; }
        .totals div { display:flex; justify-content:space-between; padding:4px 0; }
        .totals .strong { border-top:1px solid #cbd5e1; margin-top:4px; padding-top:8px; font-weight:700; }
        @media print { .no-print { display:none; } body { margin:0; } .wrap { max-width:100%; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <div>
            <h2 style="margin:0;">SOL & LUNA</h2>
            <div style="font-size:12px;color:#475569;">Comprobante de venta</div>
        </div>
        <div class="chip">
            {{ strtoupper($sale->document_type) }}<br>
            {{ $sale->code }}
        </div>
    </div>

    <div class="box">
        <table>
            <tr><td><strong>Fecha</strong></td><td>{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td><strong>Cliente</strong></td><td>{{ $sale->client->full_name }}</td></tr>
            <tr><td><strong>Documento</strong></td><td>{{ $sale->client->dni }}</td></tr>
            <tr><td><strong>Método de pago</strong></td><td>{{ $sale->paymentType->name ?? '-' }}</td></tr>
            <tr><td><strong>Estado</strong></td><td>{{ $sale->status === 'paid' ? 'Pagado' : 'Pendiente' }}</td></tr>
        </table>
    </div>

    <div class="box">
        <h4 style="margin:0 0 8px 0;">Detalle</h4>
        <table>
            <thead>
            <tr>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Cant.</th>
                <th>P. Unit.</th>
                <th>Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($sale->items as $item)
                <tr>
                    <td>Producto</td>
                    <td>{{ $item->product->name ?? 'Producto' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>S/ {{ number_format((float)$item->unit_price, 2) }}</td>
                    <td>S/ {{ number_format((float)$item->subtotal, 2) }}</td>
                </tr>
            @empty
            @endforelse
            @foreach($sale->rentals as $rental)
                <tr>
                    <td>Habitación</td>
                    <td>
                        Hab. {{ $rental->room->room_number ?? '-' }} ({{ $rental->room->type ?? '-' }})<br>
                        <small>{{ \Carbon\Carbon::parse($rental->start_at)->format('d/m/Y H:i') }} - {{ \Carbon\Carbon::parse($rental->end_at)->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>1</td>
                    <td>S/ {{ number_format((float)$rental->rate, 2) }}</td>
                    <td>S/ {{ number_format((float)$rental->subtotal, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><strong>S/ {{ number_format((float)$sale->subtotal, 2) }}</strong></div>
            <div><span>IGV (18%)</span><strong>S/ {{ number_format((float)$sale->igv, 2) }}</strong></div>
            <div class="strong"><span>Total</span><strong>S/ {{ number_format((float)$sale->total, 2) }}</strong></div>
        </div>
    </div>

    <div class="no-print" style="margin-top:16px; text-align:right;">
        <button onclick="window.print()" style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border:0;background:#1e3a8a;color:#fff;border-radius:8px;cursor:pointer;" aria-label="Imprimir comprobante" title="Imprimir comprobante">
            <img src="{{ asset('images/imprimir.svg') }}" alt="Imprimir comprobante" style="width:16px;height:16px;filter:brightness(0) invert(1);">
        </button>
    </div>
</div>
</body>
</html>
