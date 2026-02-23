<?php
// views/directorio/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Consultar el personal uniéndolo con la tabla de departamentos
try {
    $sql = "SELECT p.id, p.numero_empleado, p.nombres, p.apellidos, p.cargo, p.email, p.telefono, p.estado, 
                   p.departamento_id, d.nombre AS nombre_departamento 
            FROM personal_directorio p
            LEFT JOIN departamentos d ON p.departamento_id = d.id
            ORDER BY p.nombres ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $personal = $stmt->fetchAll();
} catch (PDOException $e) {
    $personal = [];
    $error_bd = "Error al cargar el directorio: " . $e->getMessage();
}

// 2. Consultar los departamentos activos para llenar los select de los formularios
try {
    $sqlDeptos = "SELECT id, nombre FROM departamentos WHERE estado = 1 ORDER BY nombre ASC";
    $stmtDeptos = $db->prepare($sqlDeptos);
    $stmtDeptos->execute();
    $departamentos = $stmtDeptos->fetchAll();
} catch (PDOException $e) {
    $departamentos = [];
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Directorio del Personal</h3>
        <p class="mb-0" style="color: var(--text-muted);">Gestiona a los docentes, administrativos y directivos del
            TecNM.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoPersonal"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-person-vcard-fill me-2"></i> Nuevo Registro
    </button>
</div>

<?php if (isset($error_bd)): ?>
    <div class="alert alert-warning"
        style="background: var(--glass-panel); backdrop-filter: blur(10px); border-color: #ffc107; color: var(--text-main);">
        <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
        <?= $error_bd ?>
    </div>
<?php endif; ?>

<div class="card border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaDirectorio" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;"># EMP.</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">NOMBRE COMPLETO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">CARGO / DEPTO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">CONTACTO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personal as $p): ?>
                        <tr>
                            <td class="fw-bold">
                                <span class="badge"
                                    style="background: rgba(108, 117, 125, 0.2); color: var(--text-main); border: 1px solid var(--glass-border-panel);">
                                    <?= htmlspecialchars($p['numero_empleado']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                                        style="width: 40px; height: 40px; background: var(--hover-sidebar); color: var(--accent-color); font-weight: bold; border: 1px solid var(--glass-border-panel);">
                                        <?= strtoupper(substr($p['nombres'], 0, 1)) ?>
                                    </div>
                                    <span class="fw-medium text-capitalize">
                                        <?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-capitalize">
                                    <?= htmlspecialchars($p['cargo']) ?>
                                </div>
                                <small style="color: var(--text-muted);"><i class="bi bi-building me-1"></i>
                                    <?= htmlspecialchars($p['nombre_departamento'] ?? 'Sin asignar') ?>
                                </small>
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
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Baja</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditarPer(<?= $p['id'] ?>, '<?= $p['numero_empleado'] ?>', '<?= htmlspecialchars($p['nombres']) ?>', '<?= htmlspecialchars($p['apellidos']) ?>', '<?= htmlspecialchars($p['cargo']) ?>', <?= $p['departamento_id'] ?? 'null' ?>, '<?= htmlspecialchars($p['telefono']) ?>', '<?= htmlspecialchars($p['email']) ?>')">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>

                                <?php if ($p['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Dar de Baja"
                                        onclick="cambiarEstadoPer(<?= $p['id'] ?>, 1)">
                                        <i class="bi bi-person-dash fs-5"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0" title="Reactivar"
                                        onclick="cambiarEstadoPer(<?= $p['id'] ?>, 0)">
                                        <i class="bi bi-person-check fs-5"></i>
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

<form id="formToggleEstadoPer" action="<?= BASE_URL ?>views/directorio/procesar.php" method="POST"
    style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_personal" id="toggle_id_per">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual_per">
</form>

<div class="modal fade" id="modalNuevoPersonal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-person-vcard text-primary me-2"></i>Registrar en Directorio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/directorio/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">No. de Empleado</label>
                            <input type="text" name="numero_empleado" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre(s)</label>
                            <input type="text" name="nombres" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Cargo / Puesto</label>
                            <input type="text" name="cargo" class="form-control"
                                placeholder="Ej. Docente, Jefe de Oficina"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Departamento</label>
                            <select name="departamento_id" class="form-select" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                                <option value="" style="color: #000;">Seleccione un departamento...</option>
                                <?php foreach ($departamentos as $d): ?>
                                    <option value="<?= $d['id'] ?>" style="color: #000;">
                                        <?= htmlspecialchars($d['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Teléfono / Extensión</label>
                            <input type="text" name="telefono" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Correo Institucional</label>
                            <input type="email" name="email" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"
                        style="color: var(--text-main); border-color: var(--glass-border-panel);">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-save me-2"></i>Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarPersonal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/directorio/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_personal" id="edit_id_per">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">No. de Empleado</label>
                            <input type="text" name="numero_empleado" id="edit_num_per" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre(s)</label>
                            <input type="text" name="nombres" id="edit_nom_per" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Apellidos</label>
                            <input type="text" name="apellidos" id="edit_ape_per" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Cargo / Puesto</label>
                            <input type="text" name="cargo" id="edit_car_per" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Departamento</label>
                            <select name="departamento_id" id="edit_dep_per" class="form-select" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                                <option value="" style="color: #000;">Seleccione un departamento...</option>
                                <?php foreach ($departamentos as $d): ?>
                                    <option value="<?= $d['id'] ?>" style="color: #000;">
                                        <?= htmlspecialchars($d['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Teléfono / Extensión</label>
                            <input type="text" name="telefono" id="edit_tel_per" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Correo Institucional</label>
                            <input type="email" name="email" id="edit_cor_per" class="form-control"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
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
        $('#tablaDirectorio').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    // Llenar datos en el Modal de Edición
    function abrirModalEditarPer(id, num, nom, ape, cargo, dep, tel, cor) {
        document.getElementById('edit_id_per').value = id;
        document.getElementById('edit_num_per').value = num;
        document.getElementById('edit_nom_per').value = nom;
        document.getElementById('edit_ape_per').value = ape;
        document.getElementById('edit_car_per').value = cargo;
        document.getElementById('edit_dep_per').value = dep || ''; // Si es null, lo deja en blanco
        document.getElementById('edit_tel_per').value = tel;
        document.getElementById('edit_cor_per').value = cor;

        var modal = new bootstrap.Modal(document.getElementById('modalEditarPersonal'));
        modal.show();
    }

    // Alerta de SweetAlert para Activar/Desactivar
    function cambiarEstadoPer(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'dar de baja' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';

        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accionTexto} a este empleado del directorio?`,
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
                document.getElementById('toggle_id_per').value = id;
                document.getElementById('toggle_estado_actual_per').value = estadoActual;
                document.getElementById('formToggleEstadoPer').submit();
            }
        });
    }
</script>

<?php if (isset($_SESSION['alerta'])):
    $alerta = $_SESSION['alerta']; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const style = getComputedStyle(document.body);
            Swal.fire({
                icon: '<?= $alerta['tipo'] ?>',
                title: '<?= $alerta['titulo'] ?>',
                text: '<?= $alerta['mensaje'] ?>',
                background: style.getPropertyValue('--glass-panel').trim(),
                color: style.getPropertyValue('--text-main').trim(),
                confirmButtonColor: style.getPropertyValue('--accent-color').trim(),
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
    <?php unset($_SESSION['alerta']); endif; ?>