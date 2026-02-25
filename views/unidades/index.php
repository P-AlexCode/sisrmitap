<?php
// views/unidades/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

try {
    $sql = "SELECT * FROM unidades_medida ORDER BY nombre ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $unidades = $stmt->fetchAll();
} catch (PDOException $e) {
    $unidades = [];
    $error_bd = "No se pudo cargar el catálogo de unidades. " . $e->getMessage();
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Unidades de Medida</h3>
        <p class="mb-0" style="color: var(--text-muted);">Empaques, capacidades y proporciones para el inventario.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevaUnidad"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-rulers me-2"></i> Nueva Unidad
    </button>
</div>

<?php if (isset($error_bd)): ?>
    <div class="alert alert-warning shadow-sm border-0"
        style="background: var(--glass-panel); backdrop-filter: blur(10px); color: var(--text-main);">
        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
        <?= $error_bd ?>
    </div>
<?php endif; ?>

<div class="card border-0 mb-4 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaUnidades" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ID</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">UNIDAD DE MEDIDA /
                            EMPAQUE</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ABREVIATURA
                        </th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unidades as $u): ?>
                        <tr>
                            <td class="fw-bold text-muted">#
                                <?= $u['id'] ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                        style="width: 40px; height: 40px; background: var(--hover-sidebar); color: var(--accent-color); border: 1px solid var(--glass-border-panel);">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <span class="fw-bold" style="letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($u['nombre']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-25 border border-secondary"
                                    style="color: var(--text-main);">
                                    <?= htmlspecialchars($u['abreviatura']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($u['estado'] == 1): ?>
                                    <span
                                        class="badge bg-success bg-opacity-25 text-success border border-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditarUnidad(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['abreviatura'], ENT_QUOTES) ?>')">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <?php if ($u['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Desactivar"
                                        onclick="cambiarEstadoUnidad(<?= $u['id'] ?>, 1)">
                                        <i class="bi bi-x-circle fs-5"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0" title="Reactivar"
                                        onclick="cambiarEstadoUnidad(<?= $u['id'] ?>, 0)">
                                        <i class="bi bi-check-circle fs-5"></i>
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

<form id="formToggleEstadoUnidad" action="<?= BASE_URL ?>views/unidades/procesar.php" method="POST"
    style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_unidad" id="toggle_id_unidad">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual">
</form>

<div class="modal fade" id="modalNuevaUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-rulers text-primary me-2"></i>Registrar Unidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/unidades/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre de la Unidad</label>
                        <input type="text" name="nombre" class="form-control" required
                            placeholder="Ej. Tarima, Bote, Tambo..."
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Abreviatura</label>
                        <input type="text" name="abreviatura" class="form-control" required
                            placeholder="Ej. TRM, BOT, TMB..."
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main); text-transform: uppercase;">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">Guardar Unidad</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Unidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/unidades/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_unidad" id="edit_id_unidad">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre de la Unidad</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Abreviatura</label>
                        <input type="text" name="abreviatura" id="edit_abreviatura" class="form-control" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main); text-transform: uppercase;">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#tablaUnidades').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    function abrirModalEditarUnidad(id, nombre, abreviatura) {
        document.getElementById('edit_id_unidad').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_abreviatura').value = abreviatura;
        new bootstrap.Modal(document.getElementById('modalEditarUnidad')).show();
    }

    function cambiarEstadoUnidad(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';
        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?', text: `¿Deseas ${accionTexto} esta unidad?`, icon: 'warning', showCancelButton: true, confirmButtonColor: colorBtn, cancelButtonColor: '#6c757d', confirmButtonText: `Sí, ${accionTexto}`, cancelButtonText: 'Cancelar', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim()
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggle_id_unidad').value = id;
                document.getElementById('toggle_estado_actual').value = estadoActual;
                document.getElementById('formToggleEstadoUnidad').submit();
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