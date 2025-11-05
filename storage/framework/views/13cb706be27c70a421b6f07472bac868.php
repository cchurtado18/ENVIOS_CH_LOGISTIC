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
            gap: 20px;
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
        
        .logout-btn, .dashboard-btn {
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
        
        .logout-btn:hover, .dashboard-btn:hover {
            transform: translateY(-2px);
        }
        
        .dashboard-btn {
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
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        
        .clients-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .clients-table thead {
            background: #f8f9fa;
        }
        
        .clients-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .clients-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .clients-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .view-btn {
            padding: 8px 15px;
            background: #ff751f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
        }
        
        .count-badge {
            display: inline-block;
            background: #1262b4;
            color: white;
            border-radius: 10px;
            padding: 4px 12px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .search-container {
            margin-bottom: 25px;
        }
        
        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #1262b4;
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
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .clients-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Panel de Administración <span class="admin-badge">ADMIN</span></h1>
            </div>
            <div class="header-actions">
                <div class="user-info">
                    <div class="user-name"><?php echo e($user['name'] ?? 'Admin'); ?></div>
                    <div class="user-email"><?php echo e($user['email'] ?? ''); ?></div>
                </div>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="dashboard-btn">📊 Dashboard</a>
                <a href="<?php echo e(route('admin.inventory')); ?>" class="dashboard-btn" style="background: #ff751f;">📦 Inventario</a>
                <a href="<?php echo e(route('admin.invoice.index')); ?>" class="dashboard-btn" style="background: #28a745;">📄 Facturas</a>
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout-btn">Cerrar Sesión</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <h2>👥 Clientes Registrados</h2>
            
            <div class="search-container">
                <input type="text" 
                       id="clientSearch" 
                       class="search-input" 
                       placeholder="🔍 Buscar por nombre, email o teléfono...">
            </div>
            
            <?php if($clients->count() > 0): ?>
                <table class="clients-table" id="clientsTable">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Departamento</th>
                            <th>Paquetes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($client->name); ?></td>
                                <td><?php echo e($client->email); ?></td>
                                <td><?php echo e($client->phone ?? 'N/A'); ?></td>
                                <td><?php echo e($client->department ?? 'N/A'); ?></td>
                                <td>
                                    <span class="count-badge"><?php echo e($client->shipments_count); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.client', $client->id)); ?>" class="view-btn">Ver Detalles</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <p>No hay clientes registrados aún</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Client search functionality
        document.getElementById('clientSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const table = document.getElementById('clientsTable');
            
            if (!table) return;
            
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                const phone = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>

<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/admin/index.blade.php ENDPATH**/ ?>