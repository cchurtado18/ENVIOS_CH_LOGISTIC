<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Factura - CH Logistic</title>
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
        
        .packages-selection {
            margin-top: 20px;
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
        
        .checkbox-cell {
            text-align: center;
        }
        
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
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
        
        .required-badge {
            color: #c33;
            margin-left: 5px;
        }
        
        .client-select-box {
            background: #e8f4fd;
            border: 2px solid #1262b4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Crear Factura</h1>
            <div class="header-actions">
                <a href="{{ route('admin.inventory') }}" class="back-btn">← Volver a Inventario</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Cerrar Sesión</button>
                </form>
            </div>
        </div>
        
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <!-- Client Selection -->
        <div class="card">
            <h2>1. Seleccionar Cliente</h2>
            <div class="client-select-box">
                <form method="GET" action="{{ route('admin.invoice.create') }}">
                    <label for="client_id">Cliente:</label>
                    <select name="client_id" id="client_id" required onchange="this.form.submit()">
                        <option value="">-- Seleccionar Cliente --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
        
        @if($shipments->count() > 0)
        <form method="POST" action="{{ route('admin.invoice.store') }}" id="invoiceForm">
            @csrf
            <input type="hidden" name="client_id" value="{{ request('client_id') }}">
            
            <!-- Invoice Details -->
            <div class="card">
                <h2>2. Información de Factura</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_number">N° de Factura</label>
                        <input type="number" id="invoice_number" name="invoice_number" value="{{ $nextInvoiceNumber }}" readonly style="background: #f0f0f0;">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Fecha</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>
            
            <!-- Sender Info (Hidden - always EnviosCH) -->
            <input type="hidden" name="sender_name" value="EnviosCH">
            <input type="hidden" name="sender_location" value="Managua, Nicaragua">
            <input type="hidden" name="sender_phone" value="89288565">
            
            <!-- Recipient Info -->
            <div class="card">
                <h2>3. Información del Destinatario</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient_name">Nombre</label>
                        <input type="text" id="recipient_name" name="recipient_name" value="{{ $shipments->first()?->user->name ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="recipient_location">Ubicación</label>
                        <input type="text" id="recipient_location" name="recipient_location" value="{{ $shipments->first()?->user->department ?? '' }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient_phone">Teléfono</label>
                        <input type="text" id="recipient_phone" name="recipient_phone" value="{{ $shipments->first()?->user->phone ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label for="recipient_address">Dirección</label>
                        <input type="text" id="recipient_address" name="recipient_address" value="{{ $shipments->first()?->user->address ?? '' }}">
                    </div>
                </div>
            </div>
            
            <!-- Packages Selection -->
            <div class="card">
                <h2>4. Seleccionar Paquetes</h2>
                <div class="packages-selection">
                    <table class="packages-table">
                        <thead>
                            <tr>
                                <th>✓</th>
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
                            @foreach($shipments as $shipment)
                                <tr>
                                    <td class="checkbox-cell">
                                        <input type="checkbox" 
                                               name="shipment_ids[]" 
                                               value="{{ $shipment->id }}"
                                               onchange="toggleServiceInputs(this, '{{ $shipment->id }}')">
                                    </td>
                                    <td style="font-weight: 600; font-family: monospace;">{{ $shipment->tracking_number }}</td>
                                    <td>{{ $shipment->wrh ?? 'N/A' }}</td>
                                    <td>{{ number_format(($shipment->weight ?? 0) < 1 ? 1 : $shipment->weight, 2) }}</td>
                                    <td>
                                        <input type="text" 
                                               name="services[{{ $shipment->id }}][description]" 
                                               value="{{ $shipment->description ?? '' }}" 
                                               class="description-input" 
                                               placeholder="Descripción del paquete" 
                                               disabled>
                                    </td>
                                    <td>
                                        <select name="services[{{ $shipment->id }}][service_type]" 
                                                class="service-select" 
                                                disabled 
                                                required
                                                id="service_{{ $shipment->id }}">
                                            <option value="">--Seleccione--</option>
                                            <option value="maritime">Marítimo</option>
                                            <option value="aerial">Aéreo</option>
                                        </select>
                                        <input type="hidden" name="services[{{ $shipment->id }}][shipment_id]" value="{{ $shipment->id }}">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               step="0.01" 
                                               name="services[{{ $shipment->id }}][price_per_pound]" 
                                               class="price-input" 
                                               placeholder="0.00" 
                                               disabled 
                                               required
                                               onchange="calculateValue({{ $shipment->id }})"
                                               id="price_{{ $shipment->id }}">
                                    </td>
                                    <td>
                                        <span id="value_{{ $shipment->id }}" style="font-weight: 600; color: #1262b4;">$0.00</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Delivery Cost -->
            <div class="card">
                <h2>5. Costo de Entrega</h2>
                <div class="form-group">
                    <label for="delivery_cost">Costo de Entrega</label>
                    <input type="number" step="0.01" id="delivery_cost" name="delivery_cost" value="0.00">
                </div>
            </div>
            
            <!-- Note -->
            <div class="card">
                <h2>6. Nota Adicional</h2>
                <div class="form-group">
                    <label for="note">Nota (opcional)</label>
                    <textarea id="note" name="note" rows="3" placeholder="Ej: ESTA FACTURA SERA CANCELADA VIA TRANSFERENCIA"></textarea>
                </div>
            </div>
            
            <div style="text-align: center;">
                <button type="submit" class="submit-btn">💾 Crear Factura</button>
            </div>
        </form>
        @else
            <div class="card">
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">📦</div>
                    <p style="color: #666; font-size: 18px;">No hay paquetes listos para facturar</p>
                    <a href="{{ route('admin.inventory') }}" class="back-btn" style="margin-top: 20px;">← Volver a Inventario</a>
                </div>
            </div>
        @endif
    </div>
    
    <script>
        function toggleServiceInputs(checkbox, shipmentId) {
            const serviceSelect = document.getElementById('service_' + shipmentId);
            const priceInput = document.getElementById('price_' + shipmentId);
            const descriptionInput = document.querySelector(`input[name="services[${shipmentId}][description]"]`);
            
            if (checkbox.checked) {
                serviceSelect.disabled = false;
                priceInput.disabled = false;
                descriptionInput.disabled = false;
                serviceSelect.required = true;
                priceInput.required = true;
            } else {
                serviceSelect.disabled = true;
                priceInput.disabled = true;
                descriptionInput.disabled = true;
                serviceSelect.required = false;
                priceInput.required = false;
                serviceSelect.value = '';
                priceInput.value = '';
                document.getElementById('value_' + shipmentId).textContent = '$0.00';
            }
        }
        
        function calculateValue(shipmentId) {
            const weight = parseFloat(document.querySelector(`input[type="checkbox"][value="${shipmentId}"]`)?.parentElement.parentElement.querySelector('td:nth-child(4)').textContent || 0);
            const price = parseFloat(document.getElementById('price_' + shipmentId).value || 0);
            const total = weight * price;
            document.getElementById('value_' + shipmentId).textContent = '$' + total.toFixed(2);
        }
    </script>
</body>
</html>

