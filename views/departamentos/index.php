<?php
// views/departamentos/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Consultar Departamentos con sus Edificios y Encargados
try {
    $sql = "SELECT d.id, d.nombre, d.edificio_id, d.encargado_id, d.estado, 
                   e.nombre AS nombre_edificio,
                   p.nombres, p.apellidos 
            FROM departamentos d
            LEFT JOIN edificios e ON d.edificio_id = e.id
            LEFT JOIN personal_directorio p ON d.encargado_id = p.id
            ORDER BY d.nombre ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $departamentos = $stmt->fetchAll();
} catch (PDOException $e) {
    $departamentos = [];
}

// 2. Consultar Edificios para el select (obligatorio)
try {
    $stmtEdif = $db->prepare("SELECT id, nombre FROM edificios WHERE estado = 1 ORDER BY nombre ASC");
    $stmtEdif->execute();
    $listaEdificios = $stmtEdif->fetchAll();
} catch (PDOException $e) {
    $listaEdificios = [];
}

// 3. Consultar Personal para el select de "Encargado" (opcional)
try {
    $stmtPers = $db->prepare("SELECT id, nombres, apellidos FROM personal_directorio WHERE estado = 1 ORDER BY nombres ASC");
    $stmtPers->execute();
    $listaPersonal = $stmtPers->fetchAll();
} catch (PDOException $e) {
    $listaPersonal = [];
}
?>

<style>
    .table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--text-main);
        --bs-table-border-color: var(--glass-border-panel);
    }

    .table-hover tbody tr:hover td {
        background: var(--hover-sidebar) !important;
        color: var(--text-main) !important;
    }

    .page-item .page-link {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--glass-border-panel);
        color: var(--text-main);
    }

    .page-item.active .page-link {
        background: var(--accent-color);
        border-color: var(--accent-color);
        color: #fff;
    }

    .dataTables_filter input,
    .dataTables_length select {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid var(--glass-border-panel) !important;
        color: var(--text-main) !important;
        border-radius: 8px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Departamentos</h3>
        <p class="mb-0" style="color: var(--text-muted);">Estructura interna y jefaturas.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoDepto"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600;">
        <i class="bi bi-plus-circle me-2"></i> Nuevo Departamento
    </button>
</div>

<?php if (empty($listaEdificios)): ?>
    <div class="alert alert-warning border-0 shadow-sm"
        style="background: var(--glass-panel); backdrop-filter: blur(10px); color: var(--text-main);">
        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> <strong>Atención:</strong> Necesitas registrar al
        menos un <strong>Edificio</strong> antes de crear departamentos.
    </div>
<?php endif; ?>

<div class="card border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaDeptos" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ID</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">DEPARTAMENTO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">EDIFICIO UBICACIÓN</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">JEFE / ENCARGADO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departamentos as $d): ?>
                        <tr>
                            <td class="fw-bold">#
                                <?= $d['id'] ?>
                            </td>
                            <td><span class="fw-bold text-capitalize">
                                    <?= htmlspecialchars($d['nombre']) ?>
                                </span></td>
                            <td><i class="bi bi-building me-2 opacity-75"></i>
                                <?= htmlspecialchars($d['nombre_edificio'] ?? 'Sin edificio') ?>
                            </td>
                            <td>
                                <?php if ($d['nombres']): ?>
                                    <i class="bi bi-person-badge me-2 opacity-75"></i>
                                    <?= htmlspecialchars($d['nombres'] . ' ' . $d['apellidos']) ?>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($d['estado'] == 1): ?>
                                    <span
                                        class="badge bg-success bg-opacity-25 text-success border border-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditarDepto(<?= $d['id'] ?>, '<?= htmlspecialchars($d['nombre']) ?>', <?= $d['edificio_id'] ?? 'null' ?>, <?= $d['encargado_id'] ?? 'null' ?>)">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <?php if ($d['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0"
                                        onclick="cambiarEstadoDepto(<?= $d['id'] ?>, 1)"><i
                                            class="bi bi-toggle-on fs-5"></i></button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0"
                                        onclick="cambiarEstadoDepto(<?= $d['id'] ?>, 0)"><i
                                            class="bi bi-toggle-off fs-5"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="formToggleEstadoDepto" action="<?= BASE_URL ?>views/departamentos/procesar.php" method="POST"
    style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_departamento" id="toggle_id_depto">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual_depto">
</form>

<div class="modal fade" id="modalNuevoDepto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-door-open text-primary me-2"></i>Nuevo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/departamentos/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre del Departamento</label>
                        <input type="text" name="nombre" class="form-control" required
                            placeholder="Ej. Recursos Humanos"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Edificio (Ubicación)</label>
                        <select name="edificio_id" class="form-select" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="" style="color:#000;">Selecciona un edificio...</option>
                            <?php foreach ($listaEdificios as $e): ?>
                                <option value="<?= $e['id'] ?>" style="color:#000;">
                                    <?= htmlspecialchars($e['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Jefe / Encargado (Opcional)</label>
                        <select name="encargado_id" class="form-select"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="" style="color:#000;">Sin asignar</option>
                            <?php foreach ($listaPersonal as $p): ?>
                                <option value="<?= $p['id'] ?>" style="color:#000;">
                                    <?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarDepto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/departamentos/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_departamento" id="edit_id_depto">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre del Departamento</label>
                        <input type="text" name="nombre" id="edit_nombre_depto" class="form-control" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Edificio</label>
                        <select name="edificio_id" id="edit_edificio_depto" class="form-select" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <?php foreach ($listaEdificios as $e): ?>
                                <option value="<?= $e['id'] ?>" style="color:#000;">
                                    <?= htmlspecialchars($e['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Encargado</label>
                        <select name="encargado_id" id="edit_encargado_depto" class="form-select"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="" style="color:#000;">Sin asignar</option>
                            <?php foreach ($listaPersonal as $p): ?>
                                <option value="<?= $p['id'] ?>" style="color:#000;">
                                    <?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#tablaDeptos').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    function abrirModalEditarDepto(id, nombre, idEdificio, idEncargado) {
        document.getElementById('edit_id_depto').value = id;
        document.getElementById('edit_nombre_depto').value = nombre;
        document.getElementById('edit_edificio_depto').value = idEdificio || '';
        document.getElementById('edit_encargado_depto').value = idEncargado || '';
        new bootstrap.Modal(document.getElementById('modalEditarDepto')).show();
    }

    function cambiarEstadoDepto(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';
        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accionTexto} este departamento?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: colorBtn,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${accionTexto}`,
            cancelButtonText: 'Cancelar',
            background: style.getPropertyValue('--glass-panel').trim(),
            color: style.getPropertyValue('--text-main').trim()
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggle_id_depto').value = id;
                document.getElementById('toggle_estado_actual_depto').value = estadoActual;
                document.getElementById('formToggleEstadoDepto').submit();
            }
        });
    }
</script>

<?php if (isset($_SESSION['alerta'])):
    $alerta = $_SESSION['alerta']; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const style = getComputedStyle(document.body);
            Swal.fire({ icon: '<?= $alerta['tipo'] ?>', title: '<?= $alerta['titulo'] ?>', text: '<?= $alerta['mensaje'] ?>', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim(), confirmButtonColor: style.getPropertyValue('--accent-color').trim(), timer: 3000, timerProgressBar: true });
        });
    </script>
    <?php unset($_SESSION['alerta']); endif; ?>