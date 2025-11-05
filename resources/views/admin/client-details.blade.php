<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente: {{ $client->name }} - CH Logistic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1262b4;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
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
        .header h1 { color: #333; font-size: 28px; }
        .header-actions { display: flex; gap: 15px; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-secondary { background: #666; color: white; }
        .btn-primary { background: #ff751f; color: white; }
        .btn-danger { background: #dc3545; color: white; }
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
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
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
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        tr:hover { background: #f8f9fa; }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .success-message {
            background: #dfe;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .password-display {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-weight: 600;
            font-size: 18px;
            text-align: center;
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

        @if (session('password'))
            <div class="password-display">
                Nueva contraseña generada: <strong>{{ session('password') }}</strong>
            </div>
        @endif

        <div class="header">
            <h1>Cliente: {{ $client->name }}</h1>
            <div class="header-actions">
                <a href="{{ route('admin.clients') }}" class="btn btn-secondary">← Volver</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn" style="background: #1262b4; color: white;">Cerrar Sesión</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>📋 Información del Cliente</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nombre</div>
                    <div class="info-value">{{ $client->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $client->email }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Teléfono</div>
                    <div class="info-value">{{ $client->phone }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Departamento</div>
                    <div class="info-value">{{ $client->department }}</div>
                </div>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <div class="info-label">Dirección</div>
                    <div class="info-value">{{ $client->address }}</div>
                </div>
            </div>

            <div class="actions-grid">
                <a href="{{ route('admin.client.edit', $client->id) }}" class="btn btn-primary">✏️ Editar Cliente</a>
                <a href="{{ route('admin.client.assign', $client->id) }}" class="btn btn-primary">📦 Asignar Paquete</a>
                <form method="POST" action="{{ route('admin.client.reset-password', $client->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de resetear la contraseña?')">🔑 Resetear Contraseña</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2>📦 Paquetes en Tránsito ({{ $inTransitShipments->count() }})</h2>
            @if($inTransitShipments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Estado</th>
                            <th>Peso (lb)</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inTransitShipments as $shipment)
                        <tr>
                            <td>{{ $shipment->tracking_number }}</td>
                            <td>{{ $shipment->status }}</td>
                            <td>{{ $shipment->weight ?? 'N/A' }}</td>
                            <td>{{ $shipment->description ?? 'N/A' }}</td>
                            <td>{{ $shipment->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>No hay paquetes en tránsito</p>
                </div>
            @endif
        </div>

        <div class="card">
            <h2>✅ Paquetes Entregados ({{ $deliveredShipments->count() }})</h2>
            @if($deliveredShipments->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Peso (lb)</th>
                            <th>Descripción</th>
                            <th>Fecha Entrega</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveredShipments as $shipment)
                        <tr>
                            <td>{{ $shipment->tracking_number }}</td>
                            <td>{{ $shipment->weight ?? 'N/A' }}</td>
                            <td>{{ $shipment->description ?? 'N/A' }}</td>
                            <td>{{ $shipment->delivery_date ? $shipment->delivery_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>No hay paquetes entregados</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
