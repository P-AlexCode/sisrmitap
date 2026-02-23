<?php
// views/proveedores/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

try {
    $sql = "SELECT id, rfc, razon_social, nombre_contacto, telefono, email, direccion, estado 
            FROM proveedores ORDER BY razon_social ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $proveedores = $stmt->fetchAll();
} catch (PDOException $e) {
    $proveedores = [];
    $error_bd = "No se pudo cargar el catálogo de proveedores. " . $e->getMessage();
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Directorio de Proveedores</h3>
        <p class="mb-0" style="color: var(--text-muted);">Administra las empresas o personas que suministran material.
        </p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoProveedor"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-truck me-2"></i> Nuevo Proveedor
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
            <table id="tablaProveedores" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">EMPRESA / RAZÓN SOCIAL
                        </th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">CONTACTO PRINCIPAL</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">DATOS COMERCIALES</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $p): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                        style="width: 40px; height: 40px; background: var(--hover-sidebar); color: var(--accent-color); border: 1px solid var(--glass-border-panel);">
                                        <i class="bi bi-buildings"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-uppercase">
                                            <?= htmlspecialchars($p['razon_social']) ?>
                                        </div>
                                        <small style="color: var(--text-muted);">RFC:
                                            <?= htmlspecialchars($p['rfc']) ?: '<span class="fst-italic opacity-50">No registrado</span>' ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium"><i class="bi bi-person me-2 opacity-75"></i>
                                    <?= htmlspecialchars($p['nombre_contacto']) ?: '<span class="text-muted fst-italic">Sin contacto</span>' ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.9rem;"><i class="bi bi-telephone me-2 opacity-75"></i>
                                    <?= htmlspecialchars($p['telefono']) ?>
                                </div>
                                <div style="font-size: 0.9rem;"><i class="bi bi-envelope me-2 opacity-75"></i>
                                    <?= htmlspecialchars($p['email']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($p['estado'] == 1): ?>
                                    <span
                                        class="badge bg-success bg-opacity-25 text-success border border-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditarProveedor(<?= $p['id'] ?>, '<?= htmlspecialchars($p['rfc'] ?? '') ?>', '<?= htmlspecialchars($p['razon_social'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['nombre_contacto'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['telefono'] ?? '') ?>', '<?= htmlspecialchars($p['email'] ?? '') ?>', '<?= htmlspecialchars($p['direccion'] ?? '', ENT_QUOTES) ?>')">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>
                                <?php if ($p['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Desactivar"
                                        onclick="cambiarEstadoProveedor(<?= $p['id'] ?>, 1)">
                                        <i class="bi bi-truck-flatbed fs-5"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0" title="Reactivar"
                                        onclick="cambiarEstadoProveedor(<?= $p['id'] ?>, 0)">
                                        <i class="bi bi-truck fs-5"></i>
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

<form id="formToggleEstadoProveedor" action="<?= BASE_URL ?>views/proveedores/procesar.php" method="POST"
    style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_proveedor" id="toggle_id_proveedor">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual_proveedor">
</form>

<div class="modal fade" id="modalNuevoProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-truck text-primary me-2"></i>Registrar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/proveedores/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label text-muted small fw-bold">Razón Social / Nombre de la
                                Empresa</label>
                            <input type="text" name="razon_social" class="form-control" required
                                placeholder="Ej. Papelería La Principal S.A. de C.V."
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">RFC (Opcional)</label>
                            <input type="text" name="rfc" class="form-control text-uppercase"
                                placeholder="Ej. PAPP901201XX1"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre del Contacto (Agente de
                            Ventas)</label>
                        <input type="text" name="nombre_contacto" class="form-control"
                            placeholder="Ej. Lic. Mariana Gómez"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Teléfono de Contacto</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej. 633 123 4567"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="Ej. ventas@empresa.com"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Dirección Fiscal / Ubicación</label>
                        <textarea name="direccion" class="form-control" rows="2"
                            placeholder="Calle, Número, Colonia, Ciudad, C.P."
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"
                        style="color: var(--text-main); border-color: var(--glass-border-panel);">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-save me-2"></i>Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/proveedores/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_proveedor" id="edit_id_proveedor">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label text-muted small fw-bold">Razón Social</label>
                            <input type="text" name="razon_social" id="edit_razon_social" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">RFC</label>
                            <input type="text" name="rfc" id="edit_rfc" class="form-control text-uppercase"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre del Contacto</label>
                        <input type="text" name="nombre_contacto" id="edit_nombre_contacto" class="form-control"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" id="edit_email" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Dirección Fiscal</label>
                        <textarea name="direccion" id="edit_direccion" class="form-control" rows="2"
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
        $('#tablaProveedores').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    function abrirModalEditarProveedor(id, rfc, razonSocial, contacto, telefono, email, direccion) {
        document.getElementById('edit_id_proveedor').value = id;
        document.getElementById('edit_rfc').value = rfc;
        document.getElementById('edit_razon_social').value = razonSocial;
        document.getElementById('edit_nombre_contacto').value = contacto;
        document.getElementById('edit_telefono').value = telefono;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_direccion').value = direccion;
        new bootstrap.Modal(document.getElementById('modalEditarProveedor')).show();
    }

    function cambiarEstadoProveedor(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';
        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accionTexto} este proveedor?`,
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
                document.getElementById('toggle_id_proveedor').value = id;
                document.getElementById('toggle_estado_actual_proveedor').value = estadoActual;
                document.getElementById('formToggleEstadoProveedor').submit();
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