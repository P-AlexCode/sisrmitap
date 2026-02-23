<?php
// Incluir configuraciones globales y conexión a BD
require_once 'config/global.php';
require_once 'config/db.php';

// Si el usuario ya tiene una sesión activa, lo mandamos directo al sistema
if (isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "router.php");
    exit;
}

$error_login = '';

// Procesar el formulario cuando se hace clic en "Ingresar"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_login'])) {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        $error_login = "Por favor, completa todos los campos.";
    } else {
        try {
            $conexion = new Conexion();
            $db = $conexion->conectar();

            // Consulta estricta para PDO: Usamos dos alias distintos
            $sql = "SELECT id, nombre, username, password_hash, rol_id, departamento_id, tema_preferido, estado 
                    FROM usuarios WHERE email = :correo OR username = :usuario LIMIT 1";
            $stmt = $db->prepare($sql);

            // Vinculamos la misma variable a ambos parámetros
            $stmt->bindParam(':correo', $login, PDO::PARAM_STR);
            $stmt->bindParam(':usuario', $login, PDO::PARAM_STR);
            $stmt->execute();


            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch();

                // Validar si la cuenta está activa
                if ($usuario['estado'] == 0) {
                    $error_login = "Tu cuenta está desactivada. Contacta al administrador.";
                } else {
                    // Verificar la contraseña encriptada (Bcrypt)
                    if (password_verify($password, $usuario['password_hash'])) {

                        // Regenerar el ID de sesión por seguridad (evita Session Fixation)
                        session_regenerate_id(true);

                        // Crear las variables de sesión del usuario
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['nombre'] = $usuario['nombre'];
                        $_SESSION['rol_id'] = $usuario['rol_id'];
                        $_SESSION['departamento_id'] = $usuario['departamento_id'];

                        // Si quieres que el tema del login sobreescriba el de la BD, lo podríamos hacer aquí.
                        // Por ahora, respetamos el tema que el usuario tiene guardado en la base de datos para su sesión.
                        $_SESSION['tema'] = $usuario['tema_preferido'];

                        // Registrar el login en la auditoría
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $log_sql = "INSERT INTO auditoria_logs (usuario_id, modulo, accion, descripcion_evento, direccion_ip) 
                                    VALUES (:uid, 'ACCESO', 'LOGIN', 'El usuario inició sesión exitosamente', :ip)";
                        $log_stmt = $db->prepare($log_sql);
                        $log_stmt->execute([':uid' => $usuario['id'], ':ip' => $ip]);

                        // Redirigir al enrutador principal
                        header("Location: " . BASE_URL . "router.php");
                        exit;
                    } else {
                        $error_login = "Credenciales incorrectas.";
                    }
                }
            } else {
                $error_login = "Credenciales incorrectas.";
            }
        } catch (PDOException $e) {
            $error_login = "Error de conexión. Intente más tarde.";
            error_log("Error en Login: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="tecnm">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | <?= NOMBRE_SISTEMA ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* =========================================
           VARIABLES DE TEMAS DINÁMICOS
           ========================================= */
        :root {
            --bg-main: #f4f6f9;
            --blob1: rgba(27, 57, 106, 0.35);
            /* Azul TecNM */
            --blob2: rgba(255, 158, 27, 0.3);
            /* Naranja */
            --glass-bg: rgba(255, 255, 255, 0.65);
            --glass-border: rgba(255, 255, 255, 0.8);
            --text-main: #1B396A;
            --text-muted: #6c757d;
            --input-bg: rgba(255, 255, 255, 0.5);
            --btn-bg: rgba(27, 57, 106, 0.9);
            --btn-text: #ffffff;
            --btn-hover: #122648;
            --shadow-color: rgba(27, 57, 106, 0.15);
        }

        [data-theme="oscuro"] {
            --bg-main: #121212;
            --blob1: rgba(99, 179, 237, 0.25);
            /* Azul claro */
            --blob2: rgba(183, 148, 244, 0.2);
            /* Morado */
            --glass-bg: rgba(30, 33, 43, 0.65);
            --glass-border: rgba(255, 255, 255, 0.05);
            --text-main: #f8f9fa;
            --text-muted: rgba(255, 255, 255, 0.6);
            --input-bg: rgba(0, 0, 0, 0.3);
            --btn-bg: rgba(99, 179, 237, 0.9);
            --btn-text: #121212;
            --btn-hover: #4299e1;
            --shadow-color: rgba(0, 0, 0, 0.4);
        }

        [data-theme="pastel"] {
            --bg-main: #fff5f8;
            --blob1: rgba(255, 182, 193, 0.5);
            /* Rosa claro */
            --blob2: rgba(209, 73, 106, 0.3);
            /* Rosa fuerte */
            --glass-bg: rgba(255, 255, 255, 0.55);
            --glass-border: rgba(255, 255, 255, 0.8);
            --text-main: #4a4a4a;
            --text-muted: rgba(74, 74, 74, 0.6);
            --input-bg: rgba(255, 255, 255, 0.6);
            --btn-bg: rgba(209, 73, 106, 0.85);
            --btn-text: #ffffff;
            --btn-hover: #b83253;
            --shadow-color: rgba(209, 73, 106, 0.15);
        }

        /* =========================================
           ESTILOS BASE Y ANIMACIONES (EL FONDO MÓVIL)
           ========================================= */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow: hidden;
            position: relative;
            transition: background-color 0.5s ease;
        }

        /* Los Orbes Flotantes */
        .blob {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            border-radius: 50%;
            animation: float 15s infinite alternate ease-in-out;
            transition: background-color 0.5s ease;
        }

        .blob.uno {
            background-color: var(--blob1);
            width: 400px;
            height: 400px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .blob.dos {
            background-color: var(--blob2);
            width: 500px;
            height: 500px;
            bottom: 5%;
            right: 10%;
            animation-delay: -5s;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(40px, 40px) scale(1);
            }
        }

        /* =========================================
           LA TARJETA DE CRISTAL
           ========================================= */
        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 var(--shadow-color);
            padding: 2.5rem 2rem;
            z-index: 10;
            transition: all 0.5s ease;
        }

        /* Inputs y Textos */
        .form-control,
        .input-group-text {
            background: var(--input-bg) !important;
            border: 1px solid var(--glass-border) !important;
            color: var(--text-main) !important;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem var(--blob1) !important;
            border-color: var(--btn-bg) !important;
        }

        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .input-group-text {
            color: var(--text-main);
        }

        .text-theme {
            color: var(--text-main);
        }

        .text-theme-muted {
            color: var(--text-muted);
        }

        /* Botones */
        .btn-glass {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-glass:hover {
            background: var(--btn-hover);
            color: var(--btn-text);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--blob1);
        }

        .btn-outline-glass {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
        }

        .btn-outline-glass:hover {
            color: var(--text-main);
            background: var(--input-bg);
        }

        /* Icono principal circular */
        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        /* Selector de temas flotante */
        .theme-selector {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 20;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 5px 10px;
            display: flex;
            gap: 10px;
            box-shadow: 0 4px 15px var(--shadow-color);
        }

        .theme-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.3s, transform 0.2s;
        }

        .theme-btn:hover,
        .theme-btn.active {
            color: var(--text-main);
            transform: scale(1.1);
        }
    </style>
</head>

<body>

    <div class="blob uno"></div>
    <div class="blob dos"></div>

    <div class="theme-selector">
        <button class="theme-btn active" data-set-theme="tecnm" title="Tema TecNM"><i
                class="bi bi-building"></i></button>
        <button class="theme-btn" data-set-theme="oscuro" title="Modo Oscuro"><i
                class="bi bi-moon-stars-fill"></i></button>
        <button class="theme-btn" data-set-theme="pastel" title="Modo Pastel"><i
                class="bi bi-palette-fill"></i></button>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">

                <div class="card login-card text-center">

                    <div class="mb-4">
                        <div class="icon-circle shadow-sm">
                            <i class="bi bi-box-seam fs-1 text-theme"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-theme">Sistema Integral</h4>
                        <p class="text-theme-muted" style="font-size: 0.9rem;">Recursos Materiales y Servicios</p>
                    </div>

                    <form action="" method="POST" class="text-start">
                        <div class="mb-3">
                            <label for="login" class="form-label ms-1 text-theme-muted"
                                style="font-size: 0.85rem; font-weight: 500;">Usuario o Correo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control px-3 py-2" id="login" name="login" required
                                    autofocus placeholder="ej. admin@tecnm.mx">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label ms-1 text-theme-muted"
                                style="font-size: 0.85rem; font-weight: 500;">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control px-3 py-2" id="password" name="password"
                                    required placeholder="••••••••">
                                <button class="btn btn-outline-glass" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mt-4 mb-2">
                            <button type="submit" name="btn_login" class="btn btn-glass py-2 fs-5">
                                Ingresar <i class="bi bi-arrow-right-short ms-1"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. Lógica del ojito de la contraseña
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // 2. Lógica del Cambio de Temas Dinámico
        const htmlElement = document.documentElement;
        const themeButtons = document.querySelectorAll('.theme-btn');

        // Revisar si el usuario ya tenía un tema elegido guardado en su navegador
        const savedTheme = localStorage.getItem('loginTheme') || 'tecnm';
        htmlElement.setAttribute('data-theme', savedTheme);
        updateActiveButton(savedTheme);

        // Evento click para cada botón de tema
        themeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const selectedTheme = btn.getAttribute('data-set-theme');
                htmlElement.setAttribute('data-theme', selectedTheme);
                localStorage.setItem('loginTheme', selectedTheme);
                updateActiveButton(selectedTheme);
            });
        });

        function updateActiveButton(themeName) {
            themeButtons.forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-set-theme="${themeName}"]`).classList.add('active');
        }
    </script>

    <?php if (!empty($error_login)): ?>
        <script>
            // Leer colores actuales de CSS para que la alerta haga juego con el tema
            const style = getComputedStyle(document.body);
            const bgColor = style.getPropertyValue('--glass-bg').trim();
            const textColor = style.getPropertyValue('--text-main').trim();
            const btnColor = style.getPropertyValue('--btn-bg').trim();

            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: '<?= $error_login ?>',
                background: bgColor,
                color: textColor,
                confirmButtonColor: btnColor,
                customClass: {
                    popup: 'border border-secondary rounded-4',
                    backdrop: 'backdrop-blur' /* Desenfoca un poco más el fondo al salir la alerta */
                }
            });
        </script>
        <style>
            .backdrop-blur {
                backdrop-filter: blur(5px);
            }
        </style>
    <?php endif; ?>

</body>

</html>