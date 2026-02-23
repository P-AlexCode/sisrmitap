<?php
// views/formularios/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

try {
    $sql = "SELECT * FROM formularios ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $formularios = $stmt->fetchAll();
} catch (PDOException $e) {
    $formularios = [];
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
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Gestor de Formularios</h3>
        <p class="mb-0" style="color: var(--text-muted);">Crea encuestas y solicitudes de actualización para el
            personal.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoForm"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600;">
        <i class="bi bi-ui-checks me-2"></i> Nuevo Formulario
    </button>
</div>

<div class="card border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaFormularios" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem;">TÍTULO Y DETALLES</th>
                        <th class="text-muted" style="font-size: 0.8rem;">ACCIÓN POSTERIOR</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">ESTADO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formularios as $f):
                        // Generamos el enlace público absoluto
                        $link_publico = BASE_URL . "llenar_formulario.php?id=" . $f['id'];
                        ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>router.php?modulo=formularios_diseno&id=<?= $f['id'] ?>"
                                    class="fw-bold text-decoration-none"
                                    style="color: var(--accent-color); font-size: 1.1rem;">
                                    <?= htmlspecialchars($f['titulo']) ?> <i class="bi bi-pencil-square ms-1 small"></i>
                                </a>
                                <div class="small mt-1" style="color: var(--text-muted);">
                                    <?= htmlspecialchars($f['descripcion']) ?>
                                </div>
                                <div class="small mt-2 opacity-75" style="font-size: 0.75rem;">
                                    <i class="bi bi-calendar-event me-1"></i> Ciclo inició:
                                    <?= date('d/m/Y H:i', strtotime($f['fecha_apertura'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($f['accion_posterior'] == 'db_update'): ?>
                                    <span class="badge"
                                        style="background: rgba(13, 110, 253, 0.2); color: #0d6efd; border: 1px solid #0d6efd;"><i
                                            class="bi bi-database-check me-1"></i> Actualiza Perfil</span>
                                <?php else: ?>
                                    <span class="badge"
                                        style="background: rgba(108, 117, 125, 0.2); color: var(--text-muted); border: 1px solid var(--glass-border-panel);">Solo
                                        Registro</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($f['estado'] == 'activo'): ?>
                                    <button onclick="cambiarEstadoForm(<?= $f['id'] ?>, 'activo')"
                                        class="badge bg-success bg-opacity-25 text-success border border-success p-2"
                                        title="Click para cerrar" style="cursor: pointer; transition: 0.2s;">Activo</button>
                                <?php else: ?>
                                    <button onclick="cambiarEstadoForm(<?= $f['id'] ?>, 'inactivo')"
                                        class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary p-2"
                                        title="Click para iniciar nuevo ciclo"
                                        style="cursor: pointer; transition: 0.2s;">Cerrado</button>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="<?= BASE_URL ?>router.php?modulo=formularios_respuestas&id=<?= $f['id'] ?>"
                                        class="btn btn-sm" style="background: rgba(25, 135, 84, 0.2); color: #198754;"
                                        title="Ver Resultados">
                                        <i class="bi bi-bar-chart-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary border-0"
                                        onclick="copiarLink('<?= $link_publico ?>')" title="Copiar Link">
                                        <i class="bi bi-link-45deg fs-5"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>router.php?modulo=formularios_diseno&id=<?= $f['id'] ?>"
                                        class="btn btn-sm btn-outline-secondary border-0" title="Diseñar">
                                        <i class="bi bi-tools fs-5"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Eliminar"
                                        onclick="eliminarForm(<?= $f['id'] ?>)">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="formEstadoForm" action="<?= BASE_URL ?>views/formularios/procesar.php" method="POST" style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_formulario" id="toggle_id_form">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual">
</form>

<form id="formEliminarForm" action="<?= BASE_URL ?>views/formularios/procesar.php" method="POST" style="display: none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id_formulario" id="del_id_form">
</form>

<div class="modal fade" id="modalNuevoForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-ui-checks text-primary me-2"></i>Nuevo Formulario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/formularios/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Título del Formulario</label>
                        <input type="text" name="titulo" class="form-control" required
                            placeholder="Ej: Tallas de Uniformes 2026"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción / Instrucciones</label>
                        <textarea name="descripcion" class="form-control" rows="2"
                            placeholder="Por favor llena este formulario para..."
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">¿Qué hace el sistema al recibir una
                            respuesta?</label>
                        <select name="accion_posterior" class="form-select"
                            style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            <option value="db_update" style="color:#000;">Actualizar el perfil del empleado (Tallas,
                                teléfono...)</option>
                            <option value="solo_registro" style="color:#000;">Solo guardar la respuesta (Encuestas, PDF)
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        Crear y Diseñar <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#tablaFormularios').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });
    });

    function copiarLink(texto) {
        navigator.clipboard.writeText(texto).then(() => {
            const style = getComputedStyle(document.body);
            Swal.fire({
                icon: 'success', title: 'Copiado', text: 'El enlace se ha copiado al portapapeles.',
                timer: 2000, showConfirmButton: false,
                background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim()
            });
        });
    }

    function cambiarEstadoForm(id, estadoActual) {
        let esActivo = (estadoActual === 'activo');
        let accionTexto = esActivo ? 'cerrar' : 'abrir un nuevo ciclo para';
        const style = getComputedStyle(document.body);

        Swal.fire({
            title: '¿Estás seguro?', text: `¿Deseas ${accionTexto} este formulario?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#0d6efd', cancelButtonColor: '#6c757d', confirmButtonText: `Sí, proceder`, cancelButtonText: 'Cancelar', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim()
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggle_id_form').value = id;
                document.getElementById('toggle_estado_actual').value = estadoActual;
                document.getElementById('formEstadoForm').submit();
            }
        });
    }

    function eliminarForm(id) {
        const style = getComputedStyle(document.body);
        Swal.fire({
            title: '¿Eliminar definitivamente?', text: 'Se borrará el formulario y TODAS las respuestas registradas. Esta acción no se puede deshacer.', icon: 'error', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: `Sí, eliminar`, cancelButtonText: 'Cancelar', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim()
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('del_id_form').value = id;
                document.getElementById('formEliminarForm').submit();
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