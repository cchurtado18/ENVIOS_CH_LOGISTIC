<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente - CH Logistic</title>
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
            padding: 40px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        label .required {
            color: #dc3545;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            outline: none;
            border-color: #1262b4;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: #ff751f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 10px;
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 117, 31, 0.4);
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
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border: 1px solid #cfc;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Crear Nuevo Cliente</h1>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="back-btn">← Volver al Dashboard</a>
        </div>

        <!-- Form Card -->
        <div class="card">
            <?php if($errors->any()): ?>
                <div class="error-message">
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="success-message">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('admin.client.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre Completo <span class="required">*</span></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo e(old('name')); ?>" 
                               required 
                               placeholder="Ej: Juan Pérez">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="required">*</span></label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo e(old('email')); ?>" 
                               required 
                               placeholder="Ej: cliente@ejemplo.com">
                        <div class="help-text">Debe ser único y válido</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Teléfono <span class="required">*</span></label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo e(old('phone')); ?>" 
                               required 
                               placeholder="Ej: 89288565">
                    </div>

                    <div class="form-group">
                        <label for="department">Departamento <span class="required">*</span></label>
                        <input type="text" 
                               id="department" 
                               name="department" 
                               value="<?php echo e(old('department')); ?>" 
                               required 
                               placeholder="Ej: Managua">
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="address">Dirección <span class="required">*</span></label>
                    <textarea id="address" 
                              name="address" 
                              required 
                              placeholder="Ej: Avenida Central, 2 cuadras al sur"><?php echo e(old('address')); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña <span class="required">*</span></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="8"
                               placeholder="Mínimo 8 caracteres">
                        <div class="help-text">Mínimo 8 caracteres</div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña <span class="required">*</span></label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required 
                               minlength="8"
                               placeholder="Repite la contraseña">
                    </div>
                </div>

                <button type="submit">Crear Cliente</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/carloshurtado/Documents/ch-logistic-api/resources/views/admin/client-create.blade.php ENDPATH**/ ?>