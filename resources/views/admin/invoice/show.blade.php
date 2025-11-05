<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $invoice->invoice_number }} - CH Logistic</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1262b4;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 20px 40px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            padding: 10px 20px;
            background: #666;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
        }
        
        .print-btn {
            padding: 10px 20px;
            background: #ff751f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
        }
        
        .invoice-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1262b4;
        }
        
        .invoice-title {
            font-size: 36px;
            font-weight: 700;
            color: #1262b4;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .parties-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .party-box {
            border: 2px solid #1262b4;
            border-radius: 10px;
            padding: 20px;
        }
        
        .party-label {
            font-weight: 700;
            color: #1262b4;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .party-info {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .packages-table th {
            background: #1262b4;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .packages-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        .packages-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .packages-table tr:hover {
            background: #e8f4fd;
        }
        
        .summary-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #1262b4;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 16px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            font-weight: 600;
            color: #333;
        }
        
        .summary-value {
            font-weight: 700;
            color: #1262b4;
        }
        
        .total-row {
            background: #1262b4;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .total-row .summary-label,
        .total-row .summary-value {
            color: white;
            font-size: 20px;
        }
        
        .note-section {
            margin-top: 30px;
            padding: 20px;
            background: #fff3cd;
            border: 2px solid #ff751f;
            border-radius: 10px;
            text-align: center;
        }
        
        .note-section strong {
            color: #856404;
            font-size: 16px;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border: 1px solid #fcc;
        }
        
        .success-message {
            background: #dfe;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border: 1px solid #c3e6cb;
        }
        
        @media print {
            body {
                background: white;
            }
            .header {
                display: none;
            }
            .invoice-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if (session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="header">
            <a href="{{ route('admin.invoices') }}" class="back-btn">← Volver a Facturas</a>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="back-btn" style="background: #ff751f;">✏️ Editar</a>
                <form method="POST" action="{{ route('admin.invoices.destroy', $invoice->id) }}" style="display: inline;" onsubmit="return confirm('¿Está seguro que desea eliminar esta factura?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="back-btn" style="background: #dc3545;">🗑️ Eliminar</button>
                </form>
                <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" class="print-btn" target="_blank">📄 Descargar PDF</a>
            </div>
        </div>
        
        <div class="invoice-container">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="invoice-title">FACTURA ENVIOS CH</div>
            </div>
            
            <!-- Invoice Info -->
            <div class="invoice-info">
                <div class="info-box">
                    <div class="info-label">N° de factura:</div>
                    <div class="info-value">{{ $invoice->invoice_number }}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">Fecha:</div>
                    <div class="info-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                </div>
            </div>
            
            <!-- Sender and Recipient -->
            <div class="parties-section">
                <div class="party-box">
                    <div class="party-label">Envia:</div>
                    <div class="party-info">{{ $invoice->sender_name ?? 'EnviosCH' }}</div>
                    <div class="party-info">{{ $invoice->sender_location ?? 'Managua, Nicaragua' }}</div>
                    <div class="party-info">{{ $invoice->sender_phone ?? '89288565' }}</div>
                </div>
                <div class="party-box">
                    <div class="party-label">Para:</div>
                    <div class="party-info">{{ $invoice->recipient_name ?? $invoice->user->name }}</div>
                    <div class="party-info">{{ $invoice->recipient_location ?? $invoice->user->department ?? '' }}</div>
                    <div class="party-info">{{ $invoice->recipient_phone ?? $invoice->user->phone ?? '' }}</div>
                </div>
            </div>
            
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
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label">Libras Aéreas:</span>
                    <span class="summary-value">{{ number_format($invoice->total_aerial_lbs, 2) }} lb</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Libras Marítimas:</span>
                    <span class="summary-value">{{ number_format($invoice->total_maritime_lbs, 2) }} lb</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Cantidad de Paquetes:</span>
                    <span class="summary-value">{{ $invoice->package_count }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value">${{ number_format($invoice->subtotal_maritime + $invoice->subtotal_aerial, 2) }}</span>
                </div>
                @if($invoice->delivery_cost > 0)
                    <div class="summary-row">
                        <span class="summary-label">Delivery:</span>
                        <span class="summary-value">${{ number_format($invoice->delivery_cost, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row total-row">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value">${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
            
            @if($invoice->note)
                <!-- Note -->
                <div class="note-section">
                    <strong>{{ $invoice->note }}</strong>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

