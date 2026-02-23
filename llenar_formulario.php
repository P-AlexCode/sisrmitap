<?php
// llenar_formulario.php (En la RAÍZ del proyecto)

require_once 'config/global.php';
require_once 'config/db.php';

$id_form = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();

    // 1. Obtener Formulario
    $stmt = $db->prepare("SELECT * FROM formularios WHERE id = ?");
    $stmt->execute([$id_form]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener Preguntas
    $stmt = $db->prepare("SELECT * FROM form_campos WHERE formulario_id = ? ORDER BY id ASC");
    $stmt->execute([$id_form]);
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener Personal del Directorio para el buscador
    $personal = $db->query("SELECT id, numero_empleado, nombres, apellidos FROM personal_directorio WHERE estado = 1 ORDER BY nombres ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = "";
$tipo_msg = "";
$bloqueado = false;

// Verificamos si el formulario existe y está activo
if (!$form || $form['estado'] == 'inactivo') {
    $bloqueado = true;
    $mensaje = "Este formulario no está disponible o el ciclo de respuestas ha cerrado.";
    $tipo_msg = "warning";
}

// PROCESAR EL ENVÍO DEL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$bloqueado) {
    $personal_id = $_POST['personal_id'];

    // Blindaje Anti-Duplicados: Verificamos la fecha de apertura
    $fecha_corte = !empty($form['fecha_apertura']) ? $form['fecha_apertura'] : '2000-01-01 00:00:00';
    $check = $db->prepare("SELECT id FROM form_respuestas WHERE formulario_id=? AND personal_id=? AND fecha >= ?");
    $check->execute([$id_form, $personal_id, $fecha_corte]);

    if ($check->rowCount() > 0) {
        $mensaje = "Tu respuesta ya fue registrada anteriormente en este ciclo. No es necesario enviarla de nuevo.";
        $tipo_msg = "info";
        $bloqueado = true;
    } else {
        try {
            $db->beginTransaction();

            // 1. Guardar la Cabecera de la Respuesta
            $stmt = $db->prepare("INSERT INTO form_respuestas (formulario_id, personal_id, fecha) VALUES (?, ?, NOW())");
            $stmt->execute([$id_form, $personal_id]);
            $respuesta_id = $db->lastInsertId();

            // 2. Guardar las Respuestas a cada Pregunta
            foreach ($campos as $campo) {
                $campo_name = "campo_" . $campo['id'];

                // Si es un checkbox y no se marcó, no viaja en el POST, por eso le ponemos 'No'
                if ($campo['tipo_entrada'] == 'checkbox') {
                    $valor = isset($_POST[$campo_name]) ? 'Sí' : 'No';
                } else {
                    $valor = $_POST[$campo_name] ?? '';
                }

                $stmt = $db->prepare("INSERT INTO form_respuestas_detalle (respuesta_id, campo_id, valor_ingresado) VALUES (?, ?, ?)");
                $stmt->execute([$respuesta_id, $campo['id'], $valor]);

                // 3. ACTUALIZAR PERFIL AUTOMÁTICAMENTE (Magia Pura)
                if ($form['accion_posterior'] == 'db_update' && !empty($campo['columna_destino'])) {
                    $col = $campo['columna_destino'];

                    // Lista blanca de columnas permitidas por seguridad
                    $cols_ok = ['telefono', 'email', 'cargo', 'numero_empleado'];

                    if (in_array($col, $cols_ok) && !empty($valor)) {
                        $sql = "UPDATE personal_directorio SET $col = ? WHERE id = ?";
                        $db->prepare($sql)->execute([$valor, $personal_id]);
                    }
                }
            }

            $db->commit();
            $mensaje = "¡Tus datos fueron guardados exitosamente!";
            $tipo_msg = "success";
            $bloqueado = true; // Bloqueamos el formulario para que no lo envíe dos veces seguidas

        } catch (Exception $e) {
            $db->rollBack();
            $mensaje = "Ocurrió un error al procesar tu solicitud: " . $e->getMessage();
            $tipo_msg = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="tecnm">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $form ? htmlspecialchars($form['titulo']) : 'Formulario' ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            overflow-y: auto;
            /* Aquí sí permitimos scroll porque los formularios pueden ser largos */
        }

        .form-wrap {
            width: 100%;
            max-width: 700px;
            z-index: 10;
        }

        /* Ajustes para la impresión (Comprobante) */
        @media print {
            body {
                background: #fff !important;
            }

            .blob,
            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</head>

<body>

    <div class="blob uno"></div>
    <div class="blob dos"></div>

    <div class="form-wrap">

        <?php if (!$form): ?>
            <div class="card border-0 p-5 text-center"
                style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
                <i class="bi bi-x-circle text-danger display-1 mb-3"></i>
                <h3 style="color: var(--text-main);">Enlace inválido</h3>
                <p style="color: var(--text-muted);">El formulario que buscas no existe.</p>
            </div>
        <?php else: ?>

            <div class="card shadow-lg border-0 mb-4"
                style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 24px; overflow: hidden;">

                <div class="card-header border-0 text-center p-4 p-md-5"
                    style="background: rgba(0,0,0,0.1); border-bottom: 1px solid var(--glass-border-panel) !important;">
                    <h2 class="fw-bold mb-3" style="color: var(--accent-color);">
                        <?= htmlspecialchars($form['titulo']) ?>
                    </h2>
                    <p class="mb-0 fs-6" style="color: var(--text-main); opacity: 0.9;">
                        <?= nl2br(htmlspecialchars($form['descripcion'])) ?>
                    </p>
                </div>

                <div class="card-body p-4 p-md-5">

                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_msg ?> text-center rounded-4 border-0 mb-4 shadow-sm">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <?= $mensaje ?>
                        </div>
                        <?php if ($tipo_msg == 'success'): ?>
                            <div class="text-center mt-4 no-print">
                                <button onclick="window.print()" class="btn px-4 py-2 rounded-pill fw-bold"
                                    style="background: var(--text-main); color: var(--bg-main);">
                                    <i class="bi bi-printer me-2"></i> Guardar Comprobante PDF
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!$bloqueado): ?>
                        <form method="POST">

                            <div class="mb-5 p-4 rounded-4"
                                style="background: rgba(0,0,0,0.05); border: 1px dashed var(--glass-border-panel);">
                                <label class="form-label fw-bold" style="color: var(--accent-color);">
                                    <i class="bi bi-person-badge me-2"></i>Selecciona tu nombre
                                </label>
                                <select name="personal_id" class="form-select select2-personal" required>
                                    <option value="">Buscar por nombre, apellido o número...</option>
                                    <?php foreach ($personal as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>
                                            <?= $p['numero_empleado'] ? '(#' . $p['numero_empleado'] . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small mt-2" style="color: var(--text-muted);"><i
                                        class="bi bi-shield-check me-1"></i>Tus respuestas quedarán vinculadas a tu perfil
                                    institucional.</div>
                            </div>

                            <hr class="border-secondary border-opacity-25 mb-4">

                            <?php foreach ($campos as $campo): ?>
                                <div class="mb-4">
                                    <label class="form-label fw-bold" style="color: var(--text-main); font-size: 1.1rem;">
                                        <?= htmlspecialchars($campo['etiqueta']) ?>
                                        <?php if ($campo['es_requerido'])
                                            echo '<span class="text-danger ms-1">*</span>'; ?>
                                    </label>

                                    <?php
                                    $req = $campo['es_requerido'] ? 'required' : '';
                                    $name = "campo_" . $campo['id'];

                                    if ($campo['tipo_entrada'] == 'textarea'): ?>
                                        <textarea name="<?= $name ?>" class="form-control form-control-lg" rows="3" <?= $req ?> placeholder="Escribe tu respuesta aquí..." style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>

                                    <?php elseif ($campo['tipo_entrada'] == 'select'):
                                        $opciones = explode(',', $campo['opciones']); ?>
                                        <select name="<?= $name ?>" class="form-select form-select-lg" <?= $req ?> style="background:
                            rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color:
                            var(--text-main);">
                                            <option value="" style="color: #000;">Seleccionar opción...</option>
                                            <?php foreach ($opciones as $opt): ?>
                                                <option value="<?= trim($opt) ?>" style="color: #000;">
                                                    <?= trim($opt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php elseif ($campo['tipo_entrada'] == 'checkbox'): ?>
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" name="<?= $name ?>" value="1"
                                                id="chk_<?= $campo['id'] ?>" <?= $req ?> style="background-color: transparent;
                            border-color: var(--glass-border-panel);">
                                            <label class="form-check-label" for="chk_<?= $campo['id'] ?>"
                                                style="color: var(--text-muted); font-size: 1rem;">
                                                Marcar para confirmar
                                            </label>
                                        </div>

                                    <?php else: ?>
                                        <input type="<?= $campo['tipo_entrada'] ?>" name="<?= $name ?>"
                                            class="form-control form-control-lg" <?= $req ?> placeholder="Escribe tu respuesta..."
                                        style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color:
                        var(--text-main);">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-grid gap-2 mt-5 no-print">
                                <button type="submit" class="btn btn-lg fw-bold text-white shadow-sm"
                                    style="background: var(--accent-color); border-radius: 12px; transition: transform 0.2s;"
                                    onmouseover="this.style.transform='translateY(-3px)'"
                                    onmouseout="this.style.transform='translateY(0)'">
                                    <i class="bi bi-send-fill me-2"></i> Enviar Respuestas
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>

                <div class="card-footer border-0 text-center py-4 no-print"
                    style="background: rgba(0,0,0,0.05); border-top: 1px solid var(--glass-border-panel) !important;">
                    <small style="color: var(--text-muted);"><i class="bi bi-shield-lock me-1"></i> Sistema de Recursos
                        Materiales y Servicios</small>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicializar el buscador de nombres
            $('.select2-personal').select2({
                theme: "bootstrap-5",
                width: '100%',
                language: { noResults: () => "No se encontró a nadie con ese nombre." }
            });
        });
    </script>

</body>

</html>