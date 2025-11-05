<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Cliente - CH Logistic</title>
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
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        
        .client-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-icon {
            font-size: 20px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
        }
        
        .shipments-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .shipments-table thead {
            background: #f8f9fa;
        }
        
        .shipments-table th {
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
        
        .shipments-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-in_transit {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-exception {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .tab {
            flex: 1;
            padding: 12px 20px;
            text-align: center;
            cursor: pointer;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .tab:hover {
            color: #1262b4;
            background: #f8f9fa;
        }
        
        .tab.active {
            color: #1262b4;
            border-bottom-color: #ff751f;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-count {
            display: inline-block;
            background: #1262b4;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .tab.active .tab-count {
            background: #ff751f;
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
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .shipments-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detalles de Cliente</h1>
            <div class="header-actions">
                <a href="<?php echo e(route('admin.client.assign', $client->id)); ?>" class="back-btn" style="background: #28a745;">➕ Asignar Paquete</a>
                <a href="<?php echo e(route('admin.client.edit', $client->id)); ?>" class="back-btn" style="background: #ff751f;">✏️ Editar Cliente</a>
                <div style="display: inline-block; position: relative;">
                    <form action="<?php echo e(route('admin.client.reset-password', $client->id)); ?>" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="manual" value="0">
                        <button type="submit" class="back-btn" style="background: #dc3545;" onclick="return confirm('¿Estás seguro? Se generará una nueva contraseña aleatoria y se enviará por email al cliente.');">🔑 Resetear (con Email)</button>
                    </form>
                    <form action="<?php echo e(route('admin.client.reset-password', $client->id)); ?>" method="POST" style="display: inline; margin-left: 5px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="manual" value="1">
                        <button type="submit" class="back-btn" style="background: #6c757d;" onclick="return confirm('¿Estás seguro? Se generará una nueva contraseña aleatoria y se mostrará en pantalla (sin enviar email).');">🔑 Resetear (Manual)</button>
                    </form>
                </div>
                <a href="<?php echo e(route('admin.index')); ?>" class="back-btn">← Volver</a>
            </div>
        </div>
        
        <?php if(session('success')): ?>
            <div class="success-message">
                <?php echo e(session('success')); ?>

                <?php if(session('password_shown')): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #fff; border: 2px solid #28a745; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <strong style="font-size: 16px; color: #333;">🔑 Nueva Contraseña Generada:</strong>
                            <button onclick="copyPassword()" style="background: #1262b4; color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer; font-size: 12px;">📋 Copiar</button>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 2px dashed #dc3545; text-align: center;">
                            <code id="password-display" style="font-size: 24px; font-weight: bold; color: #dc3545; letter-spacing: 2px; font-family: 'Courier New', monospace;"><?php echo e(session('password_shown')); ?></code>
                        </div>
                        <p style="margin-top: 10px; font-size: 13px; color: #666; text-align: center;">
                            ✅ Contraseña actualizada. Comparte esta contraseña con el cliente de forma segura.
                        </p>
                    </div>
                    <script>
                        function copyPassword() {
                            const password = '<?php echo e(session('password_shown')); ?>';
                            navigator.clipboard.writeText(password).then(function() {
                                const btn = event.target;
                                const originalText = btn.textContent;
                                btn.textContent = '✓ Copiado';
                                btn.style.background = '#28a745';
                                setTimeout(function() {
                                    btn.textContent = originalText;
                                    btn.style.background = '#1262b4';
                                }, 2000);
                            });
                        }
                    </script>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if(session('warning')): ?>
            <div class="error-message" style="background: #fff3cd; border-color: #ffc107; color: #856404;">
                <strong>⚠️ Advertencia:</strong> <?php echo e(session('warning')); ?>

                <?php if(session('password_shown')): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #fff; border: 2px solid #dc3545; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <strong style="font-size: 16px; color: #333;">🔑 Nueva Contraseña Generada:</strong>
                            <button onclick="copyPasswordWarning()" style="background: #dc3545; color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer; font-size: 12px;">📋 Copiar</button>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 2px dashed #dc3545; text-align: center;">
                            <code id="password-display-warning" style="font-size: 24px; font-weight: bold; color: #dc3545; letter-spacing: 2px; font-family: 'Courier New', monospace;"><?php echo e(session('password_shown')); ?></code>
                        </div>
                        <p style="margin-top: 10px; font-size: 13px; color: #666; text-align: center;">
                            ⚠️ El email no se pudo enviar. Por favor, comparte esta contraseña con el cliente manualmente.
                        </p>
                    </div>
                    <script>
                        function copyPasswordWarning() {
                            const password = '<?php echo e(session('password_shown')); ?>';
                            navigator.clipboard.writeText(password).then(function() {
                                const btn = event.target;
                                const originalText = btn.textContent;
                                btn.textContent = '✓ Copiado';
                                btn.style.background = '#28a745';
                                setTimeout(function() {
                                    btn.textContent = originalText;
                                    btn.style.background = '#dc3545';
                                }, 2000);
                            });
                        }
                    </script>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if(session('error')): ?>
            <div class="error-message">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Información del Cliente</h2>
            <div class="client-info">
                <div class="info-item">
                    <span class="info-icon">👤</span>
                    <div>
                        <div class="info-label">Nombre</div>
                        <div class="info-value"><?php echo e($client->name); ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <span class="info-icon">📧</span>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo e($client->email); ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <span class="info-icon">📞</span>
                    <div>
                        <div class="info-label">Teléfono</div>
                        <div class="info-value"><?php echo e($client->phone ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <div class="info-item">
                    <span class="info-icon">📍</span>
                    <div>
                        <div class="info-label">Departamento</div>
                        <div class="info-value"><?php echo e($client->department ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <?php if($client->address): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="info-icon">🏠</span>
                    <div>
                        <div class="info-label">Dirección</div>
                        <div class="info-value"><?php echo e($client->address); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <h2>📦 Paquetes del Cliente</h2>
            
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('active')">
                    En Tránsito <span class="tab-count" id="count-active">0</span>
                </button>
                <button class="tab" onclick="switchTab('delivered')">
                    Historial <span class="tab-count" id="count-delivered">0</span>
                </button>
            </div>
            
            <!-- Active Shipments Tab -->
            <div id="tab-active" class="tab-content active">
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Estado</th>
                            <th>WRH</th>
                            <th>Peso</th>
                            <th>Transportista</th>
                            <th>Registrado</th>
                        </tr>
                    </thead>
                    <tbody id="shipments-active-body">
                        <?php
                            $activeShipments = $shipments->whereIn('status', ['pending', 'in_transit', 'exception']);
                            $deliveredShipments = $shipments->where('status', 'delivered');
                        ?>
                        <?php $__currentLoopData = $activeShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo e($shipment->tracking_number); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo e($shipment->status); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $shipment->status))); ?>

                                    </span>
                                </td>
                                <td><?php echo e($shipment->wrh ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($shipment->weight): ?>
                                        <?php echo e(number_format($shipment->weight, 2)); ?> <?php echo e($shipment->weight_unit ?? 'lbs'); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($shipment->carrier ?? 'N/A'); ?></td>
                                <td><?php echo e($shipment->pickup_date ? $shipment->pickup_date->format('d/m/Y H:i') : 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if($activeShipments->count() === 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🚚</div>
                        <p>No hay paquetes en tránsito</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Delivered Shipments Tab -->
            <div id="tab-delivered" class="tab-content">
                <table class="shipments-table">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>WRH</th>
                            <th>Peso</th>
                            <th>Transportista</th>
                            <th>Registrado</th>
                            <th>Entregado</th>
                        </tr>
                    </thead>
                    <tbody id="shipments-delivered-body">
                        <?php $__currentLoopData = $deliveredShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo e($shipment->tracking_number); ?></td>
                                <td><?php echo e($shipment->wrh ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($shipment->weight): ?>
                                        <?php echo e(number_format($shipment->weight, 2)); ?> <?php echo e($shipment->weight_unit ?? 'lbs'); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($shipment->carrier ?? 'N/A'); ?></td>
                                <td><?php echo e($shipment->pickup_date ? $shipment->pickup_date->format('d/m/Y H:i') : 'N/A'); ?></td>
                                <td><?php echo e($shipment->delivery_date ? $shipment->delivery_date->format('d/m/Y H:i') : '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php if($deliveredShipments->count() === 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📬</div>
                        <p>No hay paquetes entregados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
            // Set counts
            document.getElementById('count-active').textContent = <?php echo e($activeShipments->count()); ?>;
            document.getElementById('count-delivered').textContent = <?php echo e($deliveredShipments->count()); ?>;
            
            function switchTab(tab) {
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(tabBtn => {
                    tabBtn.classList.remove('active');
                });
                
                // Show selected tab content
                document.getElementById('tab-' + tab).classList.add('active');
                
                // Add active class to clicked tab
                event.target.classList.add('active');
            }
        </script>
    </div>
</body>
</html>

<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/admin/client-details.blade.php ENDPATH**/ ?>