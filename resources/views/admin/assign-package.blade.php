<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Paquete - CH Logistic</title>
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
            max-width: 700px;
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
            padding: 30px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
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
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: monospace;
        }
        
        input:focus {
            outline: none;
            border-color: #1262b4;
        }
        
        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .required-badge {
            color: #c33;
            margin-left: 5px;
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
        
        .info-box {
            background: #e8f4fd;
            border: 2px solid #1262b4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-box strong {
            color: #1262b4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Asignar Paquete</h1>
            <a href="{{ route('admin.client', $client->id) }}" class="back-btn">← Volver</a>
        </div>
        
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <div class="card">
            <div class="info-box">
                <strong>Cliente:</strong> {{ $client->name }}<br>
                <strong>Email:</strong> {{ $client->email }}
            </div>
            
            <h2>Ingrese el Tracking Number</h2>
            <p style="color: #666; margin-bottom: 25px;">
                Ingrese el tracking number del paquete que desea asignar a este cliente.
                Si el paquete no existe en el sistema, lo buscaremos y crearemos automáticamente.
            </p>
            
            <form method="POST" action="{{ route('admin.client.assign.post', $client->id) }}">
                @csrf
                
                <div class="form-group">
                    <label for="tracking_number">Tracking Number<span class="required-badge">*</span></label>
                    <input type="text" id="tracking_number" name="tracking_number" value="{{ old('tracking_number') }}" required autofocus>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="submit-btn">➕ Asignar Paquete</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

