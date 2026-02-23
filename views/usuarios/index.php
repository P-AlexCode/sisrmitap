<?php
// views/usuarios/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

$sql = "SELECT id, nombre, username, email, rol_id, estado FROM usuarios ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll();
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Gestión de Usuarios</h3>
        <p class="mb-0" style="color: var(--text-muted);">Administra los accesos y permisos del sistema.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-person-plus-fill me-2"></i> Nuevo Usuario
    </button>
</div>

<div class="card border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaUsuarios" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ID</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">NOMBRE</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">USUARIO/CORREO</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ROL</th>
                        <th class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem; letter-spacing: 1px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="fw-bold">#<?= $u['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3"
                                        style="width: 40px; height: 40px; background: var(--hover-sidebar); color: var(--accent-color); font-weight: bold;">
                                        <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                                    </div>
                                    <span class="fw-medium"><?= htmlspecialchars($u['nombre']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($u['username']) ?></div>
                                <small style="color: var(--text-muted);"><?= htmlspecialchars($u['email']) ?></small>
                            </td>
                            <td>
                                <?php if ($u['rol_id'] == 1): ?>
                                    <span class="badge"
                                        style="background: rgba(27, 57, 106, 0.2); color: var(--accent-color); border: 1px solid var(--accent-color);">Administrador</span>
                                <?php else: ?>
                                    <span class="badge"
                                        style="background: rgba(108, 117, 125, 0.2); color: var(--text-muted); border: 1px solid var(--text-muted);">Usuario</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['estado'] == 1): ?>
                                    <span
                                        class="badge bg-success bg-opacity-25 text-success border border-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary border-0" title="Editar"
                                    onclick="abrirModalEditar(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nombre']) ?>', '<?= htmlspecialchars($u['username']) ?>', '<?= htmlspecialchars($u['email']) ?>', <?= $u['rol_id'] ?>)">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </button>

                                <?php if ($u['estado'] == 1): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Desactivar"
                                        onclick="cambiarEstado(<?= $u['id'] ?>, 1)">
                                        <i class="bi bi-person-x fs-5"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success border-0" title="Activar"
                                        onclick="cambiarEstado(<?= $u['id'] ?>, 0)">
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

<form id="formToggleEstado" action="<?= BASE_URL ?>views/usuarios/procesar.php" method="POST" style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_usuario" id="toggle_id">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual">
</form>

<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-person-plus text-primary me-2"></i>Registrar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/usuarios/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej. Juan Pérez"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Usuario (Login)</label>
                            <input type="text" name="username" class="form-control" required placeholder="jperez"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" required placeholder="correo@tecnm.mx"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Rol en el Sistema</label>
                        <select name="rol_id" class="form-select" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="2" style="color: #000;">Usuario Estándar</option>
                            <option value="1" style="color: #000;">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal"
                        style="color: var(--text-main); border-color: var(--glass-border-panel);">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-save me-2"></i>Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-pencil-square text-primary me-2"></i>Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/usuarios/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_usuario" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Usuario (Login)</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Dejar en blanco si no cambia"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Correo Electrónico</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Rol en el Sistema</label>
                        <select name="rol_id" id="edit_rol" class="form-select" required
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="2" style="color: #000;">Usuario Estándar</option>
                            <option value="1" style="color: #000;">Administrador</option>
                        </select>
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
        $('#tablaUsuarios').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    // Función para llenar el Modal de Edición con los datos de la fila
    function abrirModalEditar(id, nombre, username, email, rol) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_rol').value = rol;

        // Abrimos el modal usando JS nativo de Bootstrap
        var modal = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
        modal.show();
    }

    // Función para Activar/Desactivar con SweetAlert
    function cambiarEstado(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'activar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';

        // Leemos colores CSS para que la alerta no desentone
        const style = getComputedStyle(document.body);
        const bgGlass = style.getPropertyValue('--glass-panel').trim();
        const txtMain = style.getPropertyValue('--text-main').trim();

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas ${accionTexto} a este usuario?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: colorBtn,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${accionTexto}`,
            cancelButtonText: 'Cancelar',
            background: bgGlass,
            color: txtMain
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggle_id').value = id;
                document.getElementById('toggle_estado_actual').value = estadoActual;
                document.getElementById('formToggleEstado').submit();
            }
        });
    }
</script>

<?php
// Si procesar.php nos manda un mensaje en la sesión, lo mostramos con SweetAlert
if (isset($_SESSION['alerta'])):
    $alerta = $_SESSION['alerta'];
    ?>
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
    <?php
    unset($_SESSION['alerta']); // Borramos la alerta para que no salga de nuevo al recargar
endif;
?>