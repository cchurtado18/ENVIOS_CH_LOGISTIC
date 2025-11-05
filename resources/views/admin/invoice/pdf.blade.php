<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        .invoice-container {
            width: 100%;
        }
        
        .invoice-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #1262b4;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1262b4;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 10px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
        }
        
        .parties-table td {
            padding: 12px;
            border: 2px solid #1262b4;
            vertical-align: top;
            border-radius: 10px;
        }
        
        .party-label {
            font-weight: bold;
            color: #1262b4;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .party-info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .packages-table {
            margin-top: 20px;
        }
        
        .packages-table th {
            background: #1262b4;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        .packages-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .packages-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .summary-table {
            margin-top: 30px;
            border-top: 2px solid #1262b4;
            padding-top: 20px;
        }
        
        .summary-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .summary-label {
            font-weight: bold;
        }
        
        .summary-value {
            text-align: right;
            font-weight: bold;
            color: #1262b4;
        }
        
        .total-row td {
            background: #1262b4;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            padding: 12px 15px;
            border-radius: 10px;
        }
        
        .note {
            margin-top: 30px;
            padding: 15px;
            background: #fff3cd;
            border: 2px solid #ff751f;
            text-align: center;
            font-weight: bold;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-title">FACTURA ENVIOS CH</div>
        
        <!-- Invoice Info -->
        <table class="info-table">
            <tr>
                <td width="50%">
                    <strong>N° de factura:</strong><br>
                    {{ $invoice->invoice_number }}
                </td>
                <td width="50%">
                    <strong>Fecha:</strong><br>
                    {{ $invoice->invoice_date->format('d/m/Y') }}
                </td>
            </tr>
        </table>
        
        <!-- Sender and Recipient -->
        <table class="parties-table">
            <tr>
                <td width="50%">
                    <div class="party-label">Envia:</div>
                    <div class="party-info">{{ $invoice->sender_name ?? 'EnviosCH' }}</div>
                    <div class="party-info">{{ $invoice->sender_location ?? 'Managua, Nicaragua' }}</div>
                    <div class="party-info">{{ $invoice->sender_phone ?? '89288565' }}</div>
                </td>
                <td width="50%">
                    <div class="party-label">Para:</div>
                    <div class="party-info">{{ $invoice->recipient_name ?? $invoice->user->name }}</div>
                    <div class="party-info">{{ $invoice->recipient_location ?? $invoice->user->department ?? '' }}</div>
                    <div class="party-info">{{ $invoice->recipient_phone ?? $invoice->user->phone ?? '' }}</div>
                </td>
            </tr>
        </table>
        
        <!-- Packages Table -->
        <table class="packages-table">
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th>Descripción</th>
                    <th>Tracking</th>
                    <th>Servicio</th>
                    <th>Peso (lb)</th>
                    <th>Precio Unitario</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->shipments as $shipment)
                    <tr>
                        <td>{{ $shipment->wrh ?? 'N/A' }}</td>
                        <td>{{ $shipment->description ?? 'N/A' }}</td>
                        <td style="font-family: monospace; font-weight: 600;">{{ $shipment->tracking_number }}</td>
                        <td>
                            @if($shipment->service_type_billing === 'maritime')
                                Marítimo
                            @elseif($shipment->service_type_billing === 'aerial')
                                Aéreo
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ number_format(($shipment->weight ?? 0) < 1 ? 1 : $shipment->weight, 2) }}</td>
                        <td>${{ number_format($shipment->price_per_pound ?? 0, 2) }}</td>
                        <td style="font-weight: 600;">${{ number_format($shipment->invoice_value ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Summary -->
        <table class="summary-table">
            <tr>
                <td class="summary-label">Libras Aéreas:</td>
                <td class="summary-value">{{ number_format($invoice->total_aerial_lbs, 2) }} lb</td>
            </tr>
            <tr>
                <td class="summary-label">Libras Marítimas:</td>
                <td class="summary-value">{{ number_format($invoice->total_maritime_lbs, 2) }} lb</td>
            </tr>
            <tr>
                <td class="summary-label">Cantidad de Paquetes:</td>
                <td class="summary-value">{{ $invoice->package_count }}</td>
            </tr>
            <tr>
                <td class="summary-label">Subtotal:</td>
                <td class="summary-value">${{ number_format($invoice->subtotal_maritime + $invoice->subtotal_aerial, 2) }}</td>
            </tr>
            @if($invoice->delivery_cost > 0)
                <tr>
                    <td class="summary-label">Delivery:</td>
                    <td class="summary-value">${{ number_format($invoice->delivery_cost, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="summary-label" style="color: white;">Total:</td>
                <td class="summary-value" style="color: white;">${{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </table>
        
        @if($invoice->note)
            <!-- Note -->
            <div class="note">
                {{ $invoice->note }}
            </div>
        @endif
    </div>
</body>
</html>
