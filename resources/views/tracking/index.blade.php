<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastrear Paquete - CH Logistic</title>
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
            max-width: 600px;
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
        
        input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #1262b4;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: #1262b4;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
            display: none;
        }
        
        .loading.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Rastrear Paquete</h1>
        <p class="subtitle">Ingresa tu número de tracking para ver el estado de tu envío</p>
        
        @if(isset($error))
            <div class="error">
                {{ $error }}
            </div>
        @endif
        
        <form action="{{ route('tracking.track') }}" method="POST" id="trackingForm">
            @csrf
            <div class="form-group">
                <label for="tracking_number">Número de Tracking</label>
                <input 
                    type="text" 
                    id="tracking_number" 
                    name="tracking_number" 
                    placeholder="Ej: GFUS01012542646273"
                    value="{{ old('tracking_number', $tracking_number ?? '') }}"
                    required
                    autofocus
                >
            </div>
            
            <button type="submit" class="btn">🔍 Rastrear</button>
        </form>
        
        <div class="loading" id="loading">
            <p>Buscando tu paquete...</p>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
            <p style="color: #666; margin-bottom: 15px; font-size: 14px;">¿Eres cliente?</p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('login') }}" style="display: inline-block; padding: 12px 25px; background: #1262b4; color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s; font-size: 14px;">🔐 Iniciar Sesión</a>
                <a href="{{ route('register') }}" style="display: inline-block; padding: 12px 25px; background: #ff751f; color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s; font-size: 14px;">✨ Registrarse</a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('trackingForm').addEventListener('submit', function() {
            document.getElementById('loading').classList.add('active');
        });
    </script>
</body>
</html>

