<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CH Logistic</title>
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
            max-width: 500px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
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
        
        .link-to-register {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .link-to-register a {
            color: #1262b4;
            text-decoration: none;
            font-weight: 600;
        }
        
        .link-to-register a:hover {
            text-decoration: underline;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Iniciar Sesión</h1>
        <p class="subtitle">Accede a tu cuenta de Envios CH Logistic</p>
        
        <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e8f4fd; border-radius: 10px; border: 2px solid #1262b4;">
            <p style="color: #333; margin-bottom: 10px; font-weight: 600;">¿Solo quieres rastrear un paquete?</p>
            <a href="{{ route('tracking.show.es') }}" style="color: #1262b4; text-decoration: none; font-weight: 600; font-size: 16px;">📦 Rastrear sin iniciar sesión</a>
        </div>
        
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus
                       placeholder="tu@email.com">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       placeholder="Tu contraseña">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> Recordarme
                </label>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
            
            <div class="forgot-password">
                <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
            </div>
            
            <div class="link-to-register">
                ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a>
            </div>
        </form>
        
        <a href="{{ route('tracking.show.es') }}" class="back-link">← Volver al Rastreo</a>
    </div>
</body>
</html>
