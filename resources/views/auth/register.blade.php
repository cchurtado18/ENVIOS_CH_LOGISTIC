<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - CH Logistic</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .public-link {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .public-link a {
            color: #1262b4;
            text-decoration: none;
            font-weight: 600;
            word-break: break-all;
        }

        .public-link a:hover {
            text-decoration: underline;
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
        
        button {
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
            margin-bottom: 20px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 117, 31, 0.4);
        }
        
        button:active {
            transform: translateY(0);
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
        
        .back-link {
            text-align: center;
            color: #1262b4;
            text-decoration: none;
            display: block;
            margin-top: 20px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .link-to-login {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .link-to-login a {
            color: #1262b4;
            text-decoration: none;
            font-weight: 600;
        }
        
        .link-to-login a:hover {
            text-decoration: underline;
        }
        
        .password-hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                max-width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crear Cuenta</h1>
        <p class="subtitle">Regístrate en Envios CH Logistic</p>

        <div class="public-link">
            <span>Enlace directo: </span>
            <a href="http://161.35.143.171/register" target="_blank" rel="noopener">http://161.35.143.171/register</a>
        </div>
        
        <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e8f4fd; border-radius: 10px; border: 2px solid #1262b4;">
            <p style="color: #333; margin-bottom: 10px; font-weight: 600;">¿Solo quieres rastrear un paquete?</p>
            <a href="{{ route('tracking.show.es') }}" style="color: #1262b4; text-decoration: none; font-weight: 600; font-size: 16px;">📦 Rastrear sin registrarse</a>
        </div>
        
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    <div class="password-hint">📧 Usa un email activo, ahí recibirás las notificaciones de tus paquetes</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-hint">Mínimo 8 caracteres</div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña:</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Teléfono:</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Ej: +505 8888-8888" required>
                </div>
                
                <div class="form-group">
                    <label for="department">Departamento:</label>
                    <input type="text" id="department" name="department" value="{{ old('department') }}" placeholder="Ej: Managua, Rivas, etc." required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group-full">
                    <label for="address">Dirección:</label>
                    <textarea id="address" name="address" rows="3" placeholder="Dirección de domicilio" required>{{ old('address') }}</textarea>
                </div>
            </div>
            
            <button type="submit">Registrarse</button>
        </form>
        
        <div class="link-to-login">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a>
        </div>
        
        <a href="{{ route('tracking.show.es') }}" class="back-link">← Volver al Rastreo</a>
    </div>
</body>
</html>

