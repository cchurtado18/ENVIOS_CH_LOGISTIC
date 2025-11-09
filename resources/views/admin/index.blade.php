<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - CH Logistic</title>
    <style>
        :root {
            --blue: #1262b4;
            --orange: #ff751f;
            --gray: #6b7280;
            --green: #22b373;
            --bg: #ffffff;
            --card-radius: 18px;
            --shadow: 0 18px 40px rgba(18, 98, 180, 0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: #1f2937;
            min-height: 100vh;
            padding: 28px 20px 48px;
        }

        .page {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        header h1 {
            font-size: 30px;
            color: var(--blue);
        }

        header p {
            color: var(--gray);
            font-size: 14px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid rgba(18, 98, 180, 0.12);
            background: white;
            color: var(--blue);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-primary {
            background: var(--orange);
            border-color: transparent;
            color: #fff;
        }

        .card {
            background: white;
            border-radius: var(--card-radius);
            padding: 28px;
            box-shadow: var(--shadow);
        }

        .metric-card {
            min-height: 160px;
        }

        .metric-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .metric-link:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 50px rgba(18, 98, 180, 0.18);
        }

        .metric-link:focus-visible {
            outline: 2px solid var(--orange);
            outline-offset: 4px;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .grid.stats {
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }

        .metric-label {
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
        }

        .metric-value {
            font-size: 42px;
            font-weight: 700;
            color: #0f172a;
        }

        .metric-note {
            font-size: 13px;
            color: var(--gray);
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .filters label {
            font-size: 13px;
            color: var(--gray);
        }

        .filters select {
            min-width: 160px;
            padding: 9px 14px;
            border-radius: 10px;
            border: 1px solid rgba(17, 24, 39, 0.12);
            background: white;
            font-size: 14px;
            color: #111827;
        }

        .filters-actions {
            margin-left: auto;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .shipments-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 700px;
        }

        .shipments-table thead {
            background: rgba(18, 98, 180, 0.04);
        }

        .shipments-table th {
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 16px;
        }

        .shipments-table tbody tr {
            transition: background 0.2s ease;
        }

        .shipments-table tbody tr:hover {
            background: rgba(18, 98, 180, 0.05);
        }

        .shipments-table td {
            padding: 18px 16px;
            font-size: 14px;
            vertical-align: top;
            border-bottom: 1px solid rgba(17, 24, 39, 0.05);
        }

        .shipments-table td.no-label::before {
            display: none;
        }

        .shipments-table tbody tr:last-child td {
            border-bottom: none;
        }

        .tracking-cell .tracking-number {
            font-weight: 600;
            color: #0f172a;
        }

        .tracking-cell .tracking-date {
            font-size: 12px;
            color: var(--gray);
            margin-top: 4px;
        }

        .status-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .client-name {
            color: #1f2937;
            font-weight: 500;
        }

        .date-pill {
            font-size: 13px;
            color: var(--gray);
        }

        .date-pill.success {
            color: var(--green);
        }

        .badge {
            display: inline-flex;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .badge-blue { background: rgba(18, 98, 180, 0.12); color: var(--blue); }
        .badge-orange { background: rgba(255, 117, 31, 0.18); color: var(--orange); }
        .badge-green { background: rgba(34, 179, 115, 0.18); color: var(--green); }
        .badge-gray { background: rgba(107, 114, 128, 0.18); color: #4b5563; }

        .empty-state {
            text-align: center;
            padding: 24px 12px;
            color: var(--gray);
            font-size: 14px;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid rgba(17, 24, 39, 0.06);
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(18, 98, 180, 0.25);
            border-radius: 999px;
        }

        @media (max-width: 1024px) {
            .grid.stats {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }
        }

        @media (max-width: 900px) {
            header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .actions { width: 100%; justify-content: flex-start; gap: 8px; }
            .actions .btn { flex: 1; justify-content: center; }
        }

        @media (max-width: 720px) {
            body { padding: 22px 16px 40px; }
            .grid.stats {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
            .metric-card { min-height: 130px; }
            .metric-value { font-size: 34px; }
            .filters { flex-direction: column; align-items: stretch; gap: 16px; }
            .filters select { width: 100%; }
            .filters-actions { margin-left: 0; width: 100%; }
            .filters .btn { width: 100%; text-align: center; justify-content: center; }
            .table-responsive { border: none; overflow: visible; }
            .shipments-table { min-width: 100%; border-collapse: collapse; }
            .shipments-table thead { display: none; }
            .shipments-table tbody tr {
                display: block;
                background: white;
                border: 1px solid rgba(17, 24, 39, 0.08);
                border-radius: 16px;
                box-shadow: 0 12px 30px rgba(18, 98, 180, 0.12);
                margin-bottom: 16px;
                padding: 16px 18px;
            }
            .shipments-table tbody tr:hover {
                background: white;
                box-shadow: 0 14px 36px rgba(18, 98, 180, 0.16);
            }
            .shipments-table td {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
                padding: 8px 0;
                border-bottom: none;
            }
            .shipments-table td::before {
                content: attr(data-label);
                font-size: 12px;
                font-weight: 600;
                color: var(--gray);
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .shipments-table td.no-label::before {
                display: none;
            }
            .status-stack { justify-content: flex-start; }
            .tracking-cell .tracking-number { font-size: 16px; }
            .tracking-cell .tracking-date { margin-top: 2px; }
        }

        @media (max-width: 540px) {
            body { padding: 20px 14px 36px; }
            .grid.stats { grid-template-columns: 1fr; }
            .metric-card { min-height: 120px; }
            .metric-value { font-size: 30px; }
        }
    </style>
</head>
<body>
    <div class="page">
        @include('admin.partials.nav')

        <header>
            <div>
                <h1>Panel de Administración</h1>
                <p>Control rápido de paquetes, entregas y facturación.</p>
            </div>
            <div class="actions">
                <a href="{{ route('admin.inventory') }}" class="btn">📦 Inventario</a>
                <a href="{{ route('admin.invoices') }}" class="btn">💰 Facturas</a>
                <a href="{{ route('admin.client.create') }}" class="btn btn-primary">➕ Nuevo cliente</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <form method="GET" class="card filters" style="gap: 16px;">
            <div>
                <label for="range">Período</label><br>
                <select id="range" name="range" onchange="this.form.submit()">
                    @foreach($rangeOptions as $key => $label)
                        <option value="{{ $key }}" @selected($range === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status">Estado interno</label><br>
                <select id="status" name="status" onchange="this.form.submit()">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($statusFilter === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['range', 'status']))
                <div class="filters-actions">
                    <a href="{{ route('admin.index') }}" class="btn">Limpiar filtros</a>
                </div>
            @endif
        </form>

        @php
            $metricCards = [
                [
                    'label' => 'Clientes activos',
                    'value' => $stats['clients'],
                    'note' => 'Gestionar clientes',
                    'href' => route('admin.clients'),
                ],
                [
                    'label' => 'Facturas emitidas',
                    'value' => $stats['invoices_count'],
                    'note' => 'Historial de facturas',
                    'href' => route('admin.invoices'),
                ],
                [
                    'label' => 'Paquetes en tránsito',
                    'value' => $stats['in_transit'],
                    'note' => 'Ver inventario',
                    'href' => route('admin.inventory', ['status' => \App\Models\Shipment::INTERNAL_STATUS_EN_TRANSITO]),
                ],
                [
                    'label' => 'Recibidos CH',
                    'value' => $stats['received_ch'],
                    'note' => 'Listos para facturar',
                    'href' => route('admin.inventory', ['status' => \App\Models\Shipment::INTERNAL_STATUS_RECIBIDO_CH]),
                ],
                [
                    'label' => 'Paquetes facturados',
                    'value' => $stats['facturado'],
                    'note' => 'Inventario facturado',
                    'href' => route('admin.inventory', ['status' => \App\Models\Shipment::INTERNAL_STATUS_FACTURADO]),
                ],
                [
                    'label' => 'Paquetes entregados',
                    'value' => $stats['delivered'],
                    'note' => 'Ver entregas',
                    'href' => route('admin.inventory', ['status' => \App\Models\Shipment::INTERNAL_STATUS_ENTREGADO]),
                ],
                [
                    'label' => 'Dinero total',
                    'value' => $stats['revenue_total'],
                    'note' => 'Facturación acumulada',
                    'format' => 'currency',
                    'href' => route('admin.invoices'),
                ],
                [
                    'label' => 'Monto diario',
                    'value' => $stats['revenue_today'],
                    'note' => 'Ingresos de hoy',
                    'format' => 'currency',
                    'href' => route('admin.invoices', ['range' => 'today']),
                ],
            ];
        @endphp

        <section class="grid stats">
            @foreach($metricCards as $card)
                @php
                    $displayValue = isset($card['format']) && $card['format'] === 'currency'
                        ? '$' . number_format($card['value'], 2)
                        : number_format($card['value']);
                @endphp
                <a href="{{ $card['href'] }}" class="card metric-card metric-link">
                    <div class="metric-label">{{ $card['label'] }}</div>
                    <div class="metric-value">{{ $displayValue }}</div>
                    @if(!empty($card['note']))
                        <div class="metric-note">{{ $card['note'] }}</div>
                    @endif
                </a>
            @endforeach
        </section>

        <section class="card">
            <div class="section-title">Últimos paquetes</div>
            @if($recentShipments->isEmpty())
                <div class="empty-state">No hay movimientos recientes con los filtros seleccionados.</div>
            @else
                <div class="table-responsive">
                    <table class="shipments-table">
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Estado tracking</th>
                                <th>Estado interno</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentShipments as $shipment)
                                <tr>
                                    <td class="no-label">
                                        <div class="tracking-cell">
                                            <div class="tracking-number">{{ $shipment->tracking_number }}</div>
                                            <div class="tracking-date">
                                                Registrado {{ optional($shipment->created_at)->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Estado tracking">
                                        <div class="status-stack">
                                            @php
                                                $externalMap = [
                                                    \App\Models\Shipment::STATUS_PENDING => ['Pendiente', 'badge-orange'],
                                                    \App\Models\Shipment::STATUS_IN_TRANSIT => ['En tránsito', 'badge-blue'],
                                                    \App\Models\Shipment::STATUS_DELIVERED => ['Entregado', 'badge-green'],
                                                    \App\Models\Shipment::STATUS_EXCEPTION => ['Con incidencia', 'badge-gray'],
                                                ];
                                                [$label, $class] = $externalMap[$shipment->status] ?? [ucfirst(str_replace('_', ' ', $shipment->status)), 'badge-gray'];
                                            @endphp
                                            <span class="badge {{ $class }}">{{ $label }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Estado interno">
                                        <div class="status-stack">
                                            @php
                                                $internalMap = [
                                                    \App\Models\Shipment::INTERNAL_STATUS_EN_TRANSITO => ['En tránsito', 'badge-blue'],
                                                    \App\Models\Shipment::INTERNAL_STATUS_RECIBIDO_CH => ['Recibido CH', 'badge-orange'],
                                                    \App\Models\Shipment::INTERNAL_STATUS_FACTURADO => ['Facturado', 'badge-green'],
                                                    \App\Models\Shipment::INTERNAL_STATUS_ENTREGADO => ['Entregado', 'badge-green'],
                                                ];
                                                [$internalLabel, $internalClass] = $internalMap[$shipment->internal_status] ?? [ucfirst(str_replace('_', ' ', $shipment->internal_status)), 'badge-gray'];
                                            @endphp
                                            <span class="badge {{ $internalClass }}">{{ $internalLabel }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Cliente">
                                        <span class="client-name">{{ $shipment->user?->name ?? 'Sin asignar' }}</span>
                                    </td>
                                    <td data-label="Fecha">
                                        @if($shipment->delivery_date)
                                            <span class="date-pill success">
                                                Entrega {{ $shipment->delivery_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="date-pill">Sin entrega</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

</body>
</html>
