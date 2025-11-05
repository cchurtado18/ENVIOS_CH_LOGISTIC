<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Factura #{{ $invoice->invoice_number }} - CH Logistic</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px 40px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn, .logout-btn {
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
        
        .back-btn:hover, .logout-btn:hover {
            transform: translateY(-2px);
        }
        
        .logout-btn {
            background: #1262b4;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #1262b4;
        }
        
        textarea {
            resize: vertical;
            font-family: inherit;
        }
        
        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .packages-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .packages-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .packages-table tr:hover {
            background: #f8f9fa;
        }
        
        .service-select, .price-input {
            width: 100%;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .service-select:focus, .price-input:focus {
            border-color: #1262b4;
            outline: none;
        }
        
        .submit-btn {
            background: #ff751f;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
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
        
        .readonly-field {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Editar Factura #{{ $invoice->invoice_number }}</h1>
            <div class="header-actions">
                <a href="{{ route('admin.invoice.show', $invoice->id) }}" class="back-btn">← Volver</a>
            </div>
        </div>
        
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ route('admin.invoice.update', $invoice->id) }}" id="invoiceForm">
            @csrf
            @method('PUT')
            
            <!-- Invoice Details -->
            <div class="card">
                <h2>1. Información de Factura</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_number">N° de Factura</label>
                        <input type="number" id="invoice_number" name="invoice_number" value="{{ $invoice->invoice_number }}" readonly class="readonly-field">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Fecha</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                    </div>
                </div>
            </div>
            
            <!-- Sender Info (Hidden - always EnviosCH) -->
            <input type="hidden" name="sender_name" value="{{ $invoice->sender_name }}">
            <input type="hidden" name="sender_location" value="{{ $invoice->sender_location }}">
            <input type="hidden" name="sender_phone" value="{{ $invoice->sender_phone }}">
            
            <!-- Recipient Info -->
            <div class="card">
                <h2>2. Información del Destinatario</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient_name">Nombre</label>
                        <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $invoice->recipient_name) }}">
                    </div>
                    <div class="form-group">
                        <label for="recipient_location">Ubicación</label>
                        <input type="text" id="recipient_location" name="recipient_location" value="{{ old('recipient_location', $invoice->recipient_location) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient_phone">Teléfono</label>
                        <input type="text" id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone', $invoice->recipient_phone) }}">
                    </div>
                </div>
            </div>
            
            <!-- Packages Table -->
            <div class="card">
                <h2>3. Paquetes de la Factura</h2>
                <table class="packages-table">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>WRH</th>
                            <th>Peso (lbs)</th>
                            <th>Descripción</th>
                            <th>Servicio</th>
                            <th>Precio Unitario</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->shipments as $shipment)
                            <tr>
                                <td style="font-weight: 600; font-family: monospace;">{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->wrh ?? 'N/A' }}</td>
                                <td>{{ number_format(($shipment->weight ?? 0) < 1 ? 1 : $shipment->weight, 2) }}</td>
                                <td>
                                    <input type="text" 
                                           name="services[{{ $shipment->id }}][description]" 
                                           value="{{ $shipment->description ?? '' }}" 
                                           class="description-input" 
                                           placeholder="Descripción del paquete">
                                </td>
                                <td>
                                    <select name="services[{{ $shipment->id }}][service_type]" class="service-select" disabled>
                                        <option value="">--Seleccione--</option>
                                        <option value="maritime" {{ $shipment->service_type_billing === 'maritime' ? 'selected' : '' }}>Marítimo</option>
                                        <option value="aerial" {{ $shipment->service_type_billing === 'aerial' ? 'selected' : '' }}>Aéreo</option>
                                    </select>
                                    <input type="hidden" name="services[{{ $shipment->id }}][shipment_id]" value="{{ $shipment->id }}">
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           name="services[{{ $shipment->id }}][price_per_pound]" 
                                           class="price-input" 
                                           value="{{ $shipment->price_per_pound ?? 0 }}"
                                           onchange="calculateValue({{ $shipment->id }})"
                                           id="price_{{ $shipment->id }}">
                                </td>
                                <td>
                                    <span id="value_{{ $shipment->id }}" style="font-weight: 600; color: #1262b4;">${{ number_format($shipment->invoice_value ?? 0, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Delivery Cost -->
            <div class="card">
                <h2>4. Costo de Entrega</h2>
                <div class="form-group">
                    <label for="delivery_cost">Costo de Entrega</label>
                    <input type="number" step="0.01" id="delivery_cost" name="delivery_cost" value="{{ old('delivery_cost', $invoice->delivery_cost) }}">
                </div>
            </div>
            
            <!-- Note -->
            <div class="card">
                <h2>5. Nota Adicional</h2>
                <div class="form-group">
                    <label for="note">Nota (opcional)</label>
                    <textarea id="note" name="note" rows="3">{{ old('note', $invoice->note) }}</textarea>
                </div>
            </div>
            
            <div style="text-align: center;">
                <button type="submit" class="submit-btn">💾 Guardar Cambios</button>
            </div>
        </form>
    </div>
    
    <script>
        function calculateValue(shipmentId) {
            const row = document.getElementById('price_' + shipmentId).closest('tr');
            const weight = parseFloat(row.querySelector('td:nth-child(3)').textContent || 0);
            const price = parseFloat(document.getElementById('price_' + shipmentId).value || 0);
            const total = weight * price;
            document.getElementById('value_' + shipmentId).textContent = '$' + total.toFixed(2);
        }
    </script>
</body>
</html>

