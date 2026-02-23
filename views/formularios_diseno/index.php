<?php
// views/formularios_diseno/index.php

$id_form = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id_form) {
    echo "<script>window.location.href='" . BASE_URL . "router.php?modulo=formularios';</script>";
    exit;
}

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Obtener datos del formulario padre
$stmt = $db->prepare("SELECT * FROM formularios WHERE id = ?");
$stmt->execute([$id_form]);
$form = $stmt->fetch();

if (!$form) {
    echo "<div class='alert alert-danger'>Formulario no encontrado.</div>";
    exit;
}

// 2. Obtener los campos/preguntas ya creados
$stmtCampos = $db->prepare("SELECT * FROM form_campos WHERE formulario_id = ? ORDER BY id ASC");
$stmtCampos->execute([$id_form]);
$campos = $stmtCampos->fetchAll();

// 3. Diccionario de columnas permitidas para actualizar el Directorio (personal_directorio)
$columnas_permitidas = [
    '' => 'No actualizar BD (Solo guardar respuesta)',
    'telefono' => 'Teléfono / Extensión',
    'email' => 'Correo Institucional',
    'cargo' => 'Cargo / Puesto',
    'numero_empleado' => 'Número de Empleado'
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Diseñador de Formulario</h3>
        <p class="mb-0" style="color: var(--text-muted);">Construye las preguntas para: <strong>
                <?= htmlspecialchars($form['titulo']) ?>
            </strong></p>
    </div>
    <a href="<?= BASE_URL ?>router.php?modulo=formularios" class="btn shadow-sm px-4 py-2"
        style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--glass-border-panel); border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-arrow-left me-2"></i> Volver
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel);">
            <div class="card-header border-bottom border-secondary border-opacity-25 p-3">
                <h6 class="m-0 fw-bold" style="color: var(--accent-color);"><i class="bi bi-plus-circle me-2"></i>Nueva
                    Pregunta</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>views/formularios_diseno/procesar.php" method="POST">
                    <input type="hidden" name="accion" value="agregar_campo">
                    <input type="hidden" name="formulario_id" value="<?= $id_form ?>">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Pregunta / Etiqueta</label>
                        <input type="text" name="etiqueta" class="form-control" placeholder="Ej: ¿Cuál es tu extensión?"
                            required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tipo de Respuesta</label>
                        <select name="tipo_entrada" class="form-select" id="tipoEntrada" onchange="toggleOpciones()"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="text" style="color:#000;">Texto Corto (Normal)</option>
                            <option value="number" style="color:#000;">Número</option>
                            <option value="email" style="color:#000;">Correo Electrónico (@)</option>
                            <option value="tel" style="color:#000;">Teléfono</option>
                            <option value="date" style="color:#000;">Fecha</option>
                            <option value="time" style="color:#000;">Hora</option>
                            <option value="select" style="color:#000;">Lista Desplegable (Select)</option>
                            <option value="textarea" style="color:#000;">Texto Largo (Párrafo)</option>
                            <option value="checkbox" style="color:#000;">Casilla de Verificación (Sí/No)</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none p-3 rounded" id="divOpciones"
                        style="background: rgba(0,0,0,0.1); border: 1px dashed var(--glass-border-panel);">
                        <label class="small fw-bold text-warning mb-1"><i class="bi bi-info-circle me-1"></i>Opciones
                            (Separadas por coma)</label>
                        <input type="text" name="opciones" class="form-control form-control-sm"
                            placeholder="Sistemas, Contabilidad, Dirección"
                            style="background: rgba(255,255,255,0.1); color: var(--text-main);">
                    </div>

                    <?php if ($form['accion_posterior'] == 'db_update'): ?>
                        <div class="mb-3 p-3 rounded"
                            style="background: rgba(13, 110, 253, 0.05); border: 1px solid rgba(13, 110, 253, 0.2);">
                            <label class="fw-bold small" style="color: #0d6efd;"><i class="bi bi-database-check me-1"></i>
                                Guardar en Perfil (Autocompletar)</label>
                            <select name="columna_destino" class="form-select form-select-sm mt-2"
                                style="background: rgba(255,255,255,0.5); color: #000;">
                                <?php foreach ($columnas_permitidas as $col => $nom): ?>
                                    <option value="<?= $col ?>">
                                        <?= $nom ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4 form-check mt-3">
                        <input type="checkbox" class="form-check-input" name="es_requerido" id="req" checked
                            style="background-color: var(--accent-color); border-color: var(--accent-color);">
                        <label class="form-check-label small text-muted" for="req">Respuesta Obligatoria</label>
                    </div>

                    <button type="submit" class="btn w-100 fw-bold shadow-sm"
                        style="background: var(--text-main); color: var(--bg-main); border-radius: 10px;">
                        <i class="bi bi-plus-lg me-1"></i> Añadir al Formulario
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel);">
            <div
                class="card-header border-bottom border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold" style="color: var(--text-main);"><i class="bi bi-eye me-2"></i>Vista Previa
                    (Borrador)</h6>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-tools me-1"></i> Modo
                    Diseño</span>
            </div>

            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-5 pb-4 border-bottom border-secondary border-opacity-25">
                    <h2 class="fw-bold" style="color: var(--accent-color);">
                        <?= htmlspecialchars($form['titulo']) ?>
                    </h2>
                    <p class="text-muted mb-0">
                        <?= nl2br(htmlspecialchars($form['descripcion'])) ?>
                    </p>
                </div>

                <?php if (count($campos) == 0): ?>
                    <div class="text-center text-muted py-5 opacity-50">
                        <i class="bi bi-ui-checks-grid display-1 mb-3"></i>
                        <h5>El formulario está vacío</h5>
                        <p>Agrega preguntas desde el panel izquierdo.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($campos as $c): ?>
                    <div class="position-relative mb-4 p-3 rounded"
                        style="background: rgba(0,0,0,0.03); border: 1px solid var(--glass-border-panel); border-left: 4px solid var(--accent-color);">

                        <button onclick="eliminarCampo(<?= $c['id'] ?>)"
                            class="btn btn-sm text-danger position-absolute top-0 end-0 m-2" title="Eliminar pregunta"
                            style="background: transparent; border: none;">
                            <i class="bi bi-trash fs-5"></i>
                        </button>

                        <label class="form-label fw-bold" style="color: var(--text-main); font-size: 1.05rem;">
                            <?= htmlspecialchars($c['etiqueta']) ?>
                            <?php if ($c['es_requerido'])
                                echo '<span class="text-danger ms-1" title="Obligatorio">*</span>'; ?>
                        </label>

                        <div class="d-flex align-items-center mb-3">
                            <span class="badge me-2"
                                style="background: rgba(108,117,125,0.2); color: var(--text-muted); font-size:0.65rem; border: 1px solid var(--text-muted);">
                                <?= strtoupper($c['tipo_entrada']) ?>
                            </span>

                            <?php if ($c['columna_destino']): ?>
                                <span class="badge"
                                    style="background: rgba(13,110,253,0.1); color: #0d6efd; font-size:0.65rem; border: 1px solid #0d6efd;"><i
                                        class="bi bi-database-check me-1"></i> Perfil:
                                    <?= $c['columna_destino'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($c['tipo_entrada'] == 'select'):
                            $opts = explode(',', $c['opciones']); ?>
                            <select class="form-select" disabled
                                style="background: rgba(255,255,255,0.1); border-color: var(--glass-border-panel);">
                                <option>Seleccionar...</option>
                                <?php foreach ($opts as $o)
                                    echo "<option>" . trim($o) . "</option>"; ?>
                            </select>

                        <?php elseif ($c['tipo_entrada'] == 'textarea'): ?>
                            <textarea class="form-control" disabled rows="2"
                                style="background: rgba(255,255,255,0.1); border-color: var(--glass-border-panel);"></textarea>

                        <?php elseif ($c['tipo_entrada'] == 'checkbox'): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled
                                    style="background: rgba(255,255,255,0.1);">
                                <label class="form-check-label text-muted">Casilla de confirmación</label>
                            </div>

                        <?php else: ?>
                            <input type="<?= $c['tipo_entrada'] ?>" class="form-control" disabled
                                placeholder="El usuario escribirá aquí..."
                                style="background: rgba(255,255,255,0.1); border-color: var(--glass-border-panel);">
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>

                <?php if (count($campos) > 0): ?>
                    <div class="mt-5 text-center">
                        <button class="btn btn-secondary px-5 py-2 rounded-pill" disabled style="opacity: 0.5;">Enviar
                            Respuesta (Simulado)</button>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    // Mostrar u ocultar el campo de "Opciones separadas por coma"
    function toggleOpciones() {
        var tipo = document.getElementById('tipoEntrada').value;
        var div = document.getElementById('divOpciones');
        if (tipo === 'select') {
            div.classList.remove('d-none');
            div.querySelector('input').setAttribute('required', 'required');
        } else {
            div.classList.add('d-none');
            div.querySelector('input').removeAttribute('required');
        }
    }

    // Confirmación elegante para eliminar pregunta
    function eliminarCampo(idCampo) {
        const style = getComputedStyle(document.body);
        Swal.fire({
            title: '¿Quitar pregunta?',
            text: "Se eliminará de este formulario.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar',
            background: style.getPropertyValue('--glass-panel').trim(),
            color: style.getPropertyValue('--text-main').trim()
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "<?= BASE_URL ?>views/formularios_diseno/procesar.php?accion=eliminar_campo&id_form=<?= $id_form ?>&id_campo=" + idCampo;
            }
        });
    }
</script>

<?php if (isset($_SESSION['alerta'])):
    $alerta = $_SESSION['alerta']; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const style = getComputedStyle(document.body);
            Swal.fire({ icon: '<?= $alerta['tipo'] ?>', title: '<?= $alerta['titulo'] ?>', text: '<?= $alerta['mensaje'] ?>', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim(), confirmButtonColor: style.getPropertyValue('--accent-color').trim(), timer: 2000, timerProgressBar: true });
        });
    </script>
    <?php unset($_SESSION['alerta']); endif; ?>