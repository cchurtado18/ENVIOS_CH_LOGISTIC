<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - CH Logistic</title>
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
            max-width: 1400px;
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
        
        .logout-btn {
            padding: 10px 20px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab {
            flex: 1;
            padding: 15px;
            background: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            color: #666;
        }
        
        .tab:hover {
            background: #f0f0f0;
        }
        
        .tab.active {
            background: #1262b4;
            color: white;
        }
        
        .tab-count {
            display: inline-block;
            background: rgba(255,255,255,0.3);
            color: white;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .tab.active .tab-count {
            background: rgba(255,255,255,0.5);
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        .search-filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
        }
        
        .search-box input:focus {
            border-color: #1262b4;
            outline: none;
        }
        
        .filter-select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .shipments-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .shipments-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .shipments-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .shipments-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-en_camino {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-recibido_ch {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-facturado {
            background: #d4edda;
            color: #155724;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .detail-btn {
            padding: 6px 15px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .detail-btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Inventario de Paquetes</h1>
            <div class="header-actions">
                <a href="<?php echo e(route('admin.index')); ?>" class="back-btn">← Panel Admin</a>
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout-btn">Cerrar Sesión</button>
                </form>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('en_camino')">
                En Camino <span class="tab-count"><?php echo e($groupedShipments['en_camino']->count()); ?></span>
            </button>
            <button class="tab" onclick="switchTab('recibido_ch')">
                Recibido CH <span class="tab-count"><?php echo e($groupedShipments['recibido_ch']->count()); ?></span>
            </button>
            <button class="tab" onclick="switchTab('facturado')">
                Facturado <span class="tab-count"><?php echo e($groupedShipments['facturado']->count()); ?></span>
            </button>
        </div>
        
        <!-- Search and Filter -->
        <div class="card">
            <div class="search-filter-bar">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="🔍 Buscar por tracking, cliente..." onkeyup="filterShipments()">
                </div>
                <select class="filter-select" id="clientFilter" onchange="filterShipments()">
                    <option value="">Todos los clientes</option>
                    <?php $__currentLoopData = $shipments->pluck('user')->unique('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <!-- En Camino Tab -->
            <div id="tab-en_camino" class="tab-content">
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Cliente</th>
                            <th>WRH</th>
                            <th>Peso</th>
                            <th>Estado</th>
                            <th>Registrado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="shipments-en_camino-body">
                        <?php $__currentLoopData = $groupedShipments['en_camino']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="font-weight: 600; font-family: monospace;"><?php echo e($shipment->tracking_number); ?></td>
                                <td><?php echo e($shipment->user->name); ?></td>
                                <td><?php echo e($shipment->wrh ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($shipment->weight): ?>
                                        <?php echo e(number_format($shipment->weight, 2)); ?> <?php echo e($shipment->weight_unit ?? 'lbs'); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-en_camino">
                                        En Camino
                                    </span>
                                </td>
                                <td><?php echo e($shipment->pickup_date ? $shipment->pickup_date->format('d/m/Y H:i') : 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.client', $shipment->user_id)); ?>" class="detail-btn">Ver Cliente</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if($groupedShipments['en_camino']->count() === 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🚚</div>
                        <p>No hay paquetes en camino</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recibido CH Tab -->
            <div id="tab-recibido_ch" class="tab-content" style="display: none;">
                <?php if($groupedShipments['recibido_ch']->count() > 0): ?>
                    <table class="shipments-table">
                        <thead>
                            <tr>
                                <th>Tracking</th>
                                <th>Cliente</th>
                                <th>WRH</th>
                                <th>Peso</th>
                                <th>Estado</th>
                                <th>Recibido</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="shipments-recibido_ch-body">
                            <?php $__currentLoopData = $groupedShipments['recibido_ch']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td style="font-weight: 600; font-family: monospace;"><?php echo e($shipment->tracking_number); ?></td>
                                    <td><?php echo e($shipment->user->name); ?></td>
                                    <td><?php echo e($shipment->wrh ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if($shipment->weight): ?>
                                            <?php echo e(number_format($shipment->weight, 2)); ?> <?php echo e($shipment->weight_unit ?? 'lbs'); ?>

                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-recibido_ch">
                                            Recibido CH
                                        </span>
                                    </td>
                                    <td><?php echo e($shipment->delivery_date ? $shipment->delivery_date->format('d/m/Y H:i') : 'N/A'); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('admin.client', $shipment->user_id)); ?>" class="detail-btn">Ver Cliente</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <p>No hay paquetes recibidos en CH</p>
                        <p style="margin-top: 10px; font-size: 14px; color: #999;">Cuando los paquetes cambien su estado interno a "Recibido CH", aparecerán aquí.</p>
                    </div>
                <?php endif; ?>
                
                <!-- Action buttons - Always visible -->
                <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                    <a href="<?php echo e(route('admin.inventory.report.received-ch')); ?>" 
                       style="display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 10px; font-weight: 600; margin-right: 10px;"
                       title="<?php if($groupedShipments['recibido_ch']->count() === 0): ?> No hay paquetes para incluir en el reporte <?php else: ?> Descargar reporte con <?php echo e($groupedShipments['recibido_ch']->count()); ?> paquete(s) <?php endif; ?>">
                        📥 Descargar Reporte para Conductor
                        <?php if($groupedShipments['recibido_ch']->count() > 0): ?>
                            <span style="display: block; font-size: 11px; margin-top: 5px; opacity: 0.9;">(<?php echo e($groupedShipments['recibido_ch']->count()); ?> paquete(s))</span>
                        <?php endif; ?>
                    </a>
                    <?php if($groupedShipments['recibido_ch']->count() > 0): ?>
                        <a href="<?php echo e(route('admin.invoice.create')); ?>" style="display: inline-block; padding: 12px 30px; background: #ff751f; color: white; text-decoration: none; border-radius: 10px; font-weight: 600;">📄 Crear Factura</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Facturado Tab -->
            <div id="tab-facturado" class="tab-content" style="display: none;">
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Cliente</th>
                            <th>WRH</th>
                            <th>Peso</th>
                            <th>Factura</th>
                            <th>Valor</th>
                            <th>Facturado</th>
                        </tr>
                    </thead>
                    <tbody id="shipments-facturado-body">
                        <?php $__currentLoopData = $groupedShipments['facturado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="font-weight: 600; font-family: monospace;"><?php echo e($shipment->tracking_number); ?></td>
                                <td><?php echo e($shipment->user->name); ?></td>
                                <td><?php echo e($shipment->wrh ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($shipment->weight): ?>
                                        <?php echo e(number_format($shipment->weight, 2)); ?> <?php echo e($shipment->weight_unit ?? 'lbs'); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($shipment->invoice): ?>
                                        #<?php echo e($shipment->invoice->invoice_number); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 600;">
                                    <?php if($shipment->invoice_value): ?>
                                        $<?php echo e(number_format($shipment->invoice_value, 2)); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($shipment->invoiced_at ? $shipment->invoiced_at->format('d/m/Y') : 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if($groupedShipments['facturado']->count() === 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">💰</div>
                        <p>No hay paquetes facturados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tabBtn => {
                tabBtn.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById('tab-' + tab).style.display = 'block';

            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        function filterShipments() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const clientFilter = document.getElementById('clientFilter').value;
            const activeTab = document.querySelector('.tab.active').getAttribute('onclick');
            
            // Determine which list to filter
            let tabName = 'en_camino';
            if (activeTab.includes('recibido_ch')) {
                tabName = 'recibido_ch';
            } else if (activeTab.includes('facturado')) {
                tabName = 'facturado';
            }
            
            const shipmentRows = document.querySelectorAll(`#shipments-${tabName}-body tr`);
            
            shipmentRows.forEach(row => {
                const trackingNumber = row.querySelector('td:first-child').textContent.toLowerCase();
                const clientName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const cells = row.querySelectorAll('td');
                const clientId = cells[1].dataset?.clientId || '';
                
                const matchesSearch = searchTerm === '' || trackingNumber.includes(searchTerm) || clientName.includes(searchTerm);
                const matchesClient = clientFilter === '' || clientId === clientFilter;
                
                if (matchesSearch && matchesClient) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>

<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/admin/inventory.blade.php ENDPATH**/ ?>