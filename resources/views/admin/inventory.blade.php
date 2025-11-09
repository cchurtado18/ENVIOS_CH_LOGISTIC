<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - CH Logistic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
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
        .btn-logout { background: #1262b4; color: white; }
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            margin: 2px;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        .tabs {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }
        .tab {
            padding: 15px 25px;
            background: #f8f9fa;
            border: none;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }
        .tab.active {
            background: #1262b4;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
            padding: 60px 20px;
            color: #666;
        }
        .download-btn {
            margin-top: 20px;
            padding: 12px 25px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .modal-header {
            margin-bottom: 20px;
        }
        .modal-header h2 {
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
        }
        .form-group select:focus, .form-group input:focus {
            outline: none;
            border-color: #1262b4;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .actions-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @include('admin.partials.nav')

    <div class="container">
        <div class="header">
            <h1>📦 Inventario</h1>
            <div class="header-actions">
                <a href="{{ route('admin.index') }}" class="btn btn-secondary">← Panel Admin</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-logout">Cerrar Sesión</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <div class="tabs">
                <button class="tab active" onclick="showTab('recibido_ch')">Recibido CH ({{ $groupedShipments['recibido_ch']->count() }})</button>
                <button class="tab" onclick="showTab('en_transito')">En Tránsito ({{ $groupedShipments['en_transito']->count() }})</button>
                <button class="tab" onclick="showTab('facturado')">Facturado ({{ $groupedShipments['facturado']->count() }})</button>
                <button class="tab" onclick="showTab('entregado')">Entregado ({{ $groupedShipments['entregado']->count() }})</button>
            </div>

            <!-- Tab: Recibido CH -->
            <div id="recibido_ch" class="tab-content active">
                @if($groupedShipments['recibido_ch']->count() > 0)
                    <a href="{{ route('admin.inventory.report.received-ch') }}" class="download-btn">📥 Descargar Reporte para Conductor</a>
                    <table style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Cliente</th>
                                <th>Peso (lb)</th>
                                <th>Descripción</th>
                                <th>Fecha Recibido</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedShipments['recibido_ch'] as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->user->name ?? 'N/A' }}</td>
                                <td>{{ $shipment->weight ?? 'N/A' }}</td>
                                <td>{{ $shipment->description ?? 'N/A' }}</td>
                                <td>{{ $shipment->created_at->format('d/m/Y') }}</td>
                                <td class="actions-cell">
                                    <button class="btn btn-warning btn-small" onclick="openStatusModal({{ $shipment->id }}, '{{ $shipment->internal_status }}')">✏️ Cambiar Estado</button>
                                    <form method="POST" action="{{ route('admin.shipment.delete', $shipment->id) }}" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar este paquete?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit" class="btn btn-danger btn-small">🗑️ Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <p>No hay paquetes en "Recibido CH"</p>
                    </div>
                    <a href="{{ route('admin.inventory.report.received-ch') }}" class="download-btn">📥 Descargar Reporte para Conductor</a>
                @endif
            </div>

            <!-- Tab: En Tránsito -->
            <div id="en_transito" class="tab-content">
                @if($groupedShipments['en_transito']->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Cliente</th>
                                <th>Peso (lb)</th>
                                <th>Estado</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedShipments['en_transito'] as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->user->name ?? 'N/A' }}</td>
                                <td>{{ $shipment->weight ?? 'N/A' }}</td>
                                <td>{{ $shipment->status }}</td>
                                <td>{{ $shipment->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="actions-cell">
                                    <button class="btn btn-warning btn-small" onclick="openStatusModal({{ $shipment->id }}, '{{ $shipment->internal_status }}')">✏️ Cambiar Estado</button>
                                    <form method="POST" action="{{ route('admin.shipment.delete', $shipment->id) }}" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar este paquete?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit" class="btn btn-danger btn-small">🗑️ Eliminar</button>
                                    </form>
                                </td>
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

            <!-- Tab: Facturado -->
            <div id="facturado" class="tab-content">
                @if($groupedShipments['facturado']->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Cliente</th>
                                <th>Peso (lb)</th>
                                <th>Valor Factura</th>
                                <th>Fecha Factura</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedShipments['facturado'] as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->user->name ?? 'N/A' }}</td>
                                <td>{{ $shipment->weight ?? 'N/A' }}</td>
                                <td>${{ number_format($shipment->invoice_value ?? 0, 2) }}</td>
                                <td>{{ $shipment->invoiced_at ? $shipment->invoiced_at->format('d/m/Y') : 'N/A' }}</td>
                                <td class="actions-cell">
                                    <button class="btn btn-warning btn-small" onclick="openStatusModal({{ $shipment->id }}, '{{ $shipment->internal_status }}')">✏️ Cambiar Estado</button>
                                    <form method="POST" action="{{ route('admin.shipment.delete', $shipment->id) }}" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar este paquete?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit" class="btn btn-danger btn-small">🗑️ Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <p>No hay paquetes facturados</p>
                    </div>
                @endif
            </div>

            <!-- Tab: Entregado -->
            <div id="entregado" class="tab-content">
                @if($groupedShipments['entregado']->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Cliente</th>
                                <th>Peso (lb)</th>
                                <th>Fecha Entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedShipments['entregado'] as $shipment)
                            <tr>
                                <td>{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->user->name ?? 'N/A' }}</td>
                                <td>{{ $shipment->weight ?? 'N/A' }}</td>
                                <td>{{ $shipment->delivery_date ? $shipment->delivery_date->format('d/m/Y') : 'N/A' }}</td>
                                <td class="actions-cell">
                                    <button class="btn btn-warning btn-small" onclick="openStatusModal({{ $shipment->id }}, '{{ $shipment->internal_status }}')">✏️ Cambiar Estado</button>
                                    <form method="POST" action="{{ route('admin.shipment.delete', $shipment->id) }}" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar este paquete?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                                        <button type="submit" class="btn btn-danger btn-small">🗑️ Eliminar</button>
                                    </form>
                                </td>
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
    </div>

    <!-- Modal para cambiar estado -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cambiar Estado del Paquete</h2>
            </div>
            <form id="statusForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                
                <div class="form-group">
                    <label for="internal_status">Estado Interno</label>
                    <select name="internal_status" id="internal_status" required>
                        <option value="en_transito">En Tránsito</option>
                        <option value="recibido_ch">Recibido CH</option>
                        <option value="facturado">Facturado</option>
                        <option value="entregado">Entregado</option>
                    </select>
                </div>

                <div class="form-group" id="deliveryDateGroup" style="display: none;">
                    <label for="delivery_date">Fecha de Entrega (opcional)</label>
                    <input type="date" name="delivery_date" id="delivery_date">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function openStatusModal(shipmentId, currentStatus) {
            const modal = document.getElementById('statusModal');
            const form = document.getElementById('statusForm');
            const statusSelect = document.getElementById('internal_status');
            const deliveryDateGroup = document.getElementById('deliveryDateGroup');

            // Set form action
            form.action = '/admin/shipment/' + shipmentId + '/status';

            // Set current status
            statusSelect.value = currentStatus;

            // Show/hide delivery date field based on status
            function toggleDeliveryDate() {
                if (statusSelect.value === 'recibido_ch' || statusSelect.value === 'entregado') {
                    deliveryDateGroup.style.display = 'block';
                } else {
                    deliveryDateGroup.style.display = 'none';
                }
            }

            statusSelect.addEventListener('change', toggleDeliveryDate);
            toggleDeliveryDate();

            // Show modal
            modal.style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target == modal) {
                closeStatusModal();
            }
        }
    </script>
</body>
</html>
