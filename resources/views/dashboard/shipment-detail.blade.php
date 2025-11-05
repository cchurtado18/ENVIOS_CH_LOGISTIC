<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Paquete - CH Logistic</title>
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
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .tracking-number {
            color: #1262b4;
            font-size: 18px;
            font-weight: 600;
            font-family: monospace;
        }
        
        .content {
            background: white;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            margin-bottom: 30px;
            text-transform: uppercase;
            font-size: 14px;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-in_transit {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-exception {
            background: #f8d7da;
            color: #721c24;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #1262b4;
        }
        
        .info-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: 600;
        }
        
        .info-value.text-muted {
            color: #999;
            font-style: italic;
        }
        
        .events-section {
            margin-top: 30px;
        }
        
        .events-title {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .event-item {
            padding: 20px;
            border-left: 4px solid #1262b4;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            position: relative;
        }
        
        .event-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 25px;
            width: 12px;
            height: 12px;
            background: #ff751f;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .event-status {
            color: #333;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .event-date {
            color: #666;
            font-size: 14px;
        }
        
        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background: #1262b4;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
        }
        
        .no-events {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Estado del Paquete</h1>
            <p class="tracking-number">{{ $shipment->tracking_number }}</p>
        </div>
        
        <div class="content">
            @if($shipment)
                @php
                    // Determine status display
                    $statusValue = $shipment->status ?? 'pending';
                    // If status is pending but has tracking events, show as in_transit
                    if ($statusValue === 'pending' && $shipment->tracking_events && count($shipment->tracking_events) > 0) {
                        $statusValue = 'in_transit';
                    }
                    
                    $statusClass = 'status-' . $statusValue;
                    $statusText = [
                        'delivered' => 'Entregado',
                        'pending' => 'Pendiente',
                        'in_transit' => 'En Tránsito',
                        'exception' => 'Excepción'
                    ];
                    $status = $statusText[$statusValue] ?? 'Pendiente';
                @endphp
                
                <div class="status-badge {{ $statusClass }}">
                    {{ $status }}
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">WRH</div>
                        <div class="info-value @if(($shipment->wrh ?? 'pendiente') === 'pendiente') text-muted @endif">
                            {{ $shipment->wrh ?? 'pendiente' }}
                        </div>
                    </div>
                    
                    @if($shipment->weight)
                        <div class="info-card">
                            <div class="info-label">Peso</div>
                            <div class="info-value">{{ number_format($shipment->weight, 2) }} {{ $shipment->weight_unit ?? 'lbs' }}</div>
                        </div>
                    @endif
                    
                    @if($shipment->pickup_date)
                        <div class="info-card">
                            <div class="info-label">Fecha de Registro</div>
                            <div class="info-value">{{ $shipment->pickup_date->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                    
                    @if($shipment->delivery_date)
                        <div class="info-card">
                            <div class="info-label">Fecha de Entrega</div>
                            <div class="info-value">{{ $shipment->delivery_date->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                    
                    @if($shipment->carrier)
                        <div class="info-card">
                            <div class="info-label">Transportista</div>
                            <div class="info-value">{{ $shipment->carrier }}</div>
                        </div>
                    @endif
                    
                    @if($shipment->description)
                        <div class="info-card">
                            <div class="info-label">Descripción</div>
                            <div class="info-value">{{ $shipment->description }}</div>
                        </div>
                    @endif
                </div>
                
                @if($shipment->tracking_events && count($shipment->tracking_events) > 0)
                    <div class="events-section">
                        <h2 class="events-title">📋 Historial de Seguimiento</h2>
                        
                        @foreach($shipment->tracking_events as $event)
                            @php
                                $eventText = $event['status'] ?? $event['description'] ?? '';
                                // Translate event statuses
                                if (stripos($eventText, 'recibido en oficina metrocentro') !== false || 
                                    stripos($eventText, 'received in metrocentro office') !== false) {
                                    $eventText = 'En almacén fiscal de Nicaragua';
                                } elseif (stripos($eventText, 'delivered') !== false || 
                                          stripos($eventText, 'entregado') !== false) {
                                    $eventText = 'En oficina';
                                }
                            @endphp
                            <div class="event-item">
                                <div class="event-status">{{ $eventText }}</div>
                                <div class="event-date">{{ $event['date'] ?? '' }} {{ $event['time'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="no-events">
                        <p>No hay eventos de seguimiento disponibles</p>
                    </div>
                @endif
            @endif
            
            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
                <a href="{{ route('dashboard') }}" class="btn-back">← Volver al Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>


