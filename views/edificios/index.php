<?php
// views/edificios/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

try {
    $sql = "SELECT id, clave, nombre, descripcion, estado FROM edificios ORDER BY nombre ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $edificios = $stmt->fetchAll();
} catch (PDOException $e) {
    $edificios = [];
    $error_bd = "No se pudo cargar el catálogo de edificios. " . $e->getMessage();
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Gestión de Edificios</h3>
        <p class="mb-0" style="color: var(--text-muted);">Administra la infraestructura física de la institución.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoEdificio"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-building-add me-2"></i> Nuevo Edificio
    </button>
</div>

<?php if (isset($error_bd)): ?>
    <div class="alert alert-warning shadow-sm border-0"
        style="background: var(--glass-panel); backdrop-filter: blur(10px); color: var(--text-main);">
        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
        <?= $error_bd ?>
    </div>
<?php endif; ?>

<div class="card border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaEdificios" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">CLAVE</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">NOMBRE DEL EDIFICIO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">DESCRIPCIÓN</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($edificios as $e): ?>
                        <tr>
                            <td class="fw-bold">
                                <span class="badge"
                                    style="background: rgba(108, 117, 125, 0.2); color: var(--text-main); border: 1px solid var(--glass-border-panel);">
                                    <?= htmlspecialchars($e['clave']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                        style="width: 40px; height: 40px; background: var(--hover-sidebar); color: var(--accent-color); border: 1px solid var(--glass-border-panel);">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <span class="fw-medium text-capitalize">
                                        <?= htmlspecialchars($e['nombre']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <small style="color: var(--text-muted);">
                                    <?= htmlspecialchars($e['descripcion']) ?: '<span class="fst-italic opacity-50">Sin descripción</span>' ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($e['estado'] == 1): ?>
                                    <span
                                        class="badge bg-success bg-opacity-25 text-success border border-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditarEdificio(<?= $e['id'] ?>, '<?= htmlspecialchars($e['clave']) ?>', '<?= htmlspecialchars($e['nombre']) ?>', '<?= htmlspecialchars($e['descripcion']) ?>')">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <?php if ($e['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Desactivar"
                                        onclick="cambiarEstadoEdificio(<?= $e['id'] ?>, 1)">
                                        <i class="bi bi-building-dash fs-5"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0" title="Reactivar"
                                        onclick="cambiarEstadoEdificio(<?= $e['id'] ?>, 0)">
                                        <i class="bi bi-building-check fs-5"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="formToggleEstadoEdificio" action="<?= BASE_URL ?>views/edificios/procesar.php" method="POST"
    style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_edificio" id="toggle_id_edificio">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual_edificio">
</form>

<div class="modal fade" id="modalNuevoEdificio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-building-add text-primary me-2"></i>Registrar Edificio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/edificios/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Clave</label>
                            <input type="text" name="clave" class="form-control text-uppercase" required
                                placeholder="Ej. EDIF-A"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre del Edificio</label>
                            <input type="text" name="nombre" class="form-control" required
                                placeholder="Ej. Edificio Administrativo"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción / Detalles (Opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="3"
                            placeholder="Ej. Alberga las oficinas de recursos humanos y finanzas..."
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"
                        style="color: var(--text-main); border-color: var(--glass-border-panel);">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-save me-2"></i>Guardar Edificio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarEdificio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Edificio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/edificios/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_edificio" id="edit_id_edificio">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Clave</label>
                            <input type="text" name="clave" id="edit_clave_edificio" class="form-control text-uppercase"
                                required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre del Edificio</label>
                            <input type="text" name="nombre" id="edit_nombre_edificio" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción / Detalles (Opcional)</label>
                        <textarea name="descripcion" id="edit_descripcion_edificio" class="form-control" rows="3"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"
                        style="color: var(--text-main); border-color: var(--glass-border-panel);">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-save me-2"></i>Actualizar Datos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#tablaEdificios').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    function abrirModalEditarEdificio(id, clave, nombre, descripcion) {
        document.getElementById('edit_id_edificio').value = id;
        document.getElementById('edit_clave_edificio').value = clave;
        document.getElementById('edit_nombre_edificio').value = nombre;
        document.getElementById('edit_descripcion_edificio').value = descripcion;
        new bootstrap.Modal(document.getElementById('modalEditarEdificio')).show();
    }

    function cambiarEstadoEdificio(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';
        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accionTexto} este edificio?`,
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
                document.getElementById('toggle_id_edificio').value = id;
                document.getElementById('toggle_estado_actual_edificio').value = estadoActual;
                document.getElementById('formToggleEstadoEdificio').submit();
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