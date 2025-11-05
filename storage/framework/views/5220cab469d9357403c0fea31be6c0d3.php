<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CH Logistic</title>
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
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .user-email {
            color: #666;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            background: #1262b4;
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
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: #1262b4;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-secondary {
            background: #666;
        }
        
        .admin-badge {
            display: inline-block;
            padding: 5px 12px;
            background: #ff751f;
            color: white;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 25px;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-card-title {
            color: #666;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .stat-card-icon {
            font-size: 32px;
        }
        
        .stat-card-value {
            color: #333;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stat-card-footer {
            color: #999;
            font-size: 12px;
        }
        
        .actions-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .actions-section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            background: #1262b4;
            color: white;
            border-color: #1262b4;
            transform: translateY(-3px);
        }
        
        .action-card-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .action-card-title {
            font-weight: 600;
            font-size: 16px;
        }
        
        .recent-activity {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .recent-activity h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item:hover {
            background: #f8f9fa;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .activity-meta {
            color: #999;
            font-size: 14px;
        }
        
        .create-client-btn {
            background: #ff751f;
            padding: 15px 30px;
            font-size: 18px;
        }
        
        .create-client-btn:hover {
            background: #e66318;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Panel de Administración<span class="admin-badge">ADMIN</span></h1>
            </div>
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-name"><?php echo e($user['name']); ?></div>
                    <div class="user-email"><?php echo e($user['email']); ?></div>
                </div>
                <a href="<?php echo e(route('admin.index')); ?>" class="btn btn-secondary">👥 Clientes</a>
                <form action="<?php echo e(route('logout')); ?>" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn">🚪 Cerrar Sesión</button>
                </form>
            </div>
        </div>

        <!-- Quick Action: Create Client -->
        <div class="actions-section">
            <a href="<?php echo e(route('admin.client.create')); ?>" class="btn create-client-btn">
                ➕ Crear Nuevo Cliente
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Total Clientes</div>
                    <div class="stat-card-icon">👥</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($totalClients)); ?></div>
                <div class="stat-card-footer">Clientes registrados</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Total Paquetes</div>
                    <div class="stat-card-icon">📦</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($totalShipments)); ?></div>
                <div class="stat-card-footer">Paquetes en el sistema</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">En Tránsito</div>
                    <div class="stat-card-icon">🚚</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($shipmentsInTransit)); ?></div>
                <div class="stat-card-footer">Paquetes en camino</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Entregados</div>
                    <div class="stat-card-icon">✅</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($shipmentsDelivered)); ?></div>
                <div class="stat-card-footer">Paquetes entregados</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Recibidos CH</div>
                    <div class="stat-card-icon">📥</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($shipmentsReceivedCH)); ?></div>
                <div class="stat-card-footer">Listos para facturar</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Facturados</div>
                    <div class="stat-card-icon">💰</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($shipmentsInvoiced)); ?></div>
                <div class="stat-card-footer">Paquetes facturados</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Total Facturas</div>
                    <div class="stat-card-icon">📄</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($totalInvoices)); ?></div>
                <div class="stat-card-footer">Facturas creadas</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">En Verificación</div>
                    <div class="stat-card-icon">⏳</div>
                </div>
                <div class="stat-card-value"><?php echo e(number_format($pendingTrackings)); ?></div>
                <div class="stat-card-footer">Trackings pendientes</div>
            </div>
        </div>

        <!-- Financial Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Ingresos Totales</div>
                    <div class="stat-card-icon">💵</div>
                </div>
                <div class="stat-card-value">$<?php echo e(number_format($totalRevenue, 2)); ?></div>
                <div class="stat-card-footer">Total histórico</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-card-title">Ingresos Mensuales</div>
                    <div class="stat-card-icon">📅</div>
                </div>
                <div class="stat-card-value">$<?php echo e(number_format($monthlyRevenue, 2)); ?></div>
                <div class="stat-card-footer"><?php echo e(now()->format('F Y')); ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-section">
            <h2>Accesos Rápidos</h2>
            <div class="actions-grid">
                <a href="<?php echo e(route('admin.index')); ?>" class="action-card">
                    <div class="action-card-icon">👥</div>
                    <div class="action-card-title">Ver Clientes</div>
                </a>
                <a href="<?php echo e(route('admin.inventory')); ?>" class="action-card">
                    <div class="action-card-icon">📦</div>
                    <div class="action-card-title">Inventario</div>
                </a>
                <a href="<?php echo e(route('admin.invoice.index')); ?>" class="action-card">
                    <div class="action-card-icon">📄</div>
                    <div class="action-card-title">Facturas</div>
                </a>
                <a href="<?php echo e(route('admin.invoice.create')); ?>" class="action-card">
                    <div class="action-card-icon">➕</div>
                    <div class="action-card-title">Crear Factura</div>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if($recentShipments->count() > 0): ?>
        <div class="recent-activity">
            <h2>Paquetes Recientes</h2>
            <?php $__currentLoopData = $recentShipments->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="activity-item">
                <div class="activity-info">
                    <div class="activity-title">
                        📦 <?php echo e($shipment->tracking_number); ?>

                        <?php if($shipment->user): ?>
                            - <?php echo e($shipment->user->name); ?>

                        <?php endif; ?>
                    </div>
                    <div class="activity-meta">
                        Estado: <?php echo e(ucfirst($shipment->status)); ?> | 
                        Creado: <?php echo e($shipment->created_at->format('d/m/Y H:i')); ?>

                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <?php if($recentClients->count() > 0): ?>
        <div class="recent-activity">
            <h2>Clientes Recientes</h2>
            <?php $__currentLoopData = $recentClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="activity-item">
                <div class="activity-info">
                    <div class="activity-title">👤 <?php echo e($client->name); ?></div>
                    <div class="activity-meta">
                        <?php echo e($client->email); ?> | 
                        Registrado: <?php echo e($client->created_at->format('d/m/Y')); ?>

                    </div>
                </div>
                <a href="<?php echo e(route('admin.client', $client->id)); ?>" class="btn btn-primary">Ver</a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>