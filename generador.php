<?php
$hash_generado = '';
$password_ingresado = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    $password_ingresado = $_POST['password'];
    // Aquí ocurre la magia: PHP genera el salt y el hash automáticamente
    $hash_generado = password_hash($password_ingresado, PASSWORD_BCRYPT);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador Bcrypt | Temp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            padding: 40px;
            display: flex;
            justify-content: center;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #1B396A;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #122648;
        }

        .resultado {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f0fe;
            border-left: 5px solid #1B396A;
            font-family: monospace;
            font-size: 16px;
            word-break: break-all;
        }

        .alerta {
            color: #dc3545;
            font-size: 14px;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2 style="margin-top:0; color: #333;">Generador de Hash Bcrypt</h2>
        <p style="color: #666;">Escribe la contraseña en texto plano para obtener el código seguro que debes pegar en
            phpMyAdmin.</p>

        <form method="POST">
            <input type="text" name="password" placeholder="Escribe la contraseña aquí..." required
                value="<?= htmlspecialchars($password_ingresado) ?>">
            <button type="submit">Generar Hash Seguro</button>
        </form>

        <?php if ($hash_generado): ?>
            <div class="resultado">
                <strong>Hash generado:</strong><br><br>
                <?= $hash_generado ?>
            </div>
            <div class="alerta">
                ⚠️ IMPORTANTE: Elimina este archivo (generador.php) de tu servidor en cuanto termines de usarlo por motivos
                de seguridad.
            </div>
        <?php endif; ?>
    </div>

</body>

</html>