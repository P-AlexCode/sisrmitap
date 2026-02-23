<?php
// views/categorias/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

try {
    $sql = "SELECT id, nombre, descripcion, estado FROM categorias ORDER BY nombre ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll();
} catch (PDOException $e) {
    $categorias = [];
    $error_bd = "No se pudo cargar el catálogo de categorías. " . $e->getMessage();
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Clasificación y Categorías
        </h3>
        <p class="mb-0" style="color: var(--text-muted);">Organiza tu inventario por familias de productos.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-bookmark-plus me-2"></i> Nueva Categoría
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
            <table id="tablaCategorias" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ID</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">NOMBRE DE LA CATEGORÍA
                        </th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">DESCRIPCIÓN</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $c): ?>
                        <tr>
                            <td class="fw-bold text-muted">#
                                <?= $c['id'] ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                        style="width: 40px; height: 40px; background: var(--hover-sidebar); color: var(--accent-color); border: 1px solid var(--glass-border-panel);">
                                        <i class="bi bi-tags"></i>
                                    </div>
                                    <span class="fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                        <?= htmlspecialchars($c['nombre']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="color: var(--text-muted);">
                                    <?= htmlspecialchars($c['descripcion']) ?: '<span class="fst-italic opacity-50">Sin descripción</span>' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($c['estado'] == 1): ?>
                                    <span
                                        class="badge bg-success bg-opacity-25 text-success border border-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditarCategoria(<?= $c['id'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['descripcion'], ENT_QUOTES) ?>')">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <?php if ($c['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Desactivar"
                                        onclick="cambiarEstadoCategoria(<?= $c['id'] ?>, 1)">
                                        <i class="bi bi-bookmark-x fs-5"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0" title="Reactivar"
                                        onclick="cambiarEstadoCategoria(<?= $c['id'] ?>, 0)">
                                        <i class="bi bi-bookmark-check fs-5"></i>
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

<form id="formToggleEstadoCategoria" action="<?= BASE_URL ?>views/categorias/procesar.php" method="POST"
    style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_categoria" id="toggle_id_categoria">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual_categoria">
</form>

<div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-tags text-primary me-2"></i>Registrar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/categorias/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre de la Categoría</label>
                        <input type="text" name="nombre" class="form-control" required
                            placeholder="Ej. Papelería y Oficina"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción (Opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="3"
                            placeholder="Ej. Hojas, plumas, marcadores, carpetas..."
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"
                        style="color: var(--text-main); border-color: var(--glass-border-panel);">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-save me-2"></i>Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/categorias/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_categoria" id="edit_id_categoria">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre de la Categoría</label>
                        <input type="text" name="nombre" id="edit_nombre_categoria" class="form-control" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción (Opcional)</label>
                        <textarea name="descripcion" id="edit_descripcion_categoria" class="form-control" rows="3"
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
        $('#tablaCategorias').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    function abrirModalEditarCategoria(id, nombre, descripcion) {
        document.getElementById('edit_id_categoria').value = id;
        document.getElementById('edit_nombre_categoria').value = nombre;
        document.getElementById('edit_descripcion_categoria').value = descripcion;
        new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
    }

    function cambiarEstadoCategoria(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';
        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accionTexto} esta categoría?`,
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
                document.getElementById('toggle_id_categoria').value = id;
                document.getElementById('toggle_estado_actual_categoria').value = estadoActual;
                document.getElementById('formToggleEstadoCategoria').submit();
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