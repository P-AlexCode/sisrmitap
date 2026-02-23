<?php
// views/productos/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Consultar Productos
try {
    $sql = "SELECT p.id, p.codigo_barras, p.nombre, p.descripcion, p.tipo_material, 
                   p.stock_actual, p.stock_minimo, p.estado, p.categoria_id, p.unidad_medida_id,
                   c.nombre AS categoria, u.abreviatura AS unidad, u.nombre AS unidad_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN unidades_medida u ON p.unidad_medida_id = u.id
            ORDER BY p.nombre ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos = [];
    $error_bd = "Error: " . $e->getMessage();
}

// 2. Traer Equivalencias y agruparlas por producto (Para el Modal de Editar)
try {
    $eq_stmt = $db->query("SELECT producto_id, unidad_medida_id, factor_conversion FROM producto_equivalencias");
    $todas_eq = $eq_stmt->fetchAll(PDO::FETCH_ASSOC);
    $equivalencias_por_prod = [];
    foreach ($todas_eq as $eq) {
        $equivalencias_por_prod[$eq['producto_id']][] = $eq;
    }
} catch (PDOException $e) {
    $equivalencias_por_prod = [];
}

// 3. Traer listas para los modales
try {
    $categorias = $db->query("SELECT id, nombre FROM categorias WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();
    $unidades = $db->query("SELECT id, nombre, abreviatura FROM unidades_medida WHERE estado = 1 ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    $categorias = [];
    $unidades = [];
}

// Preparamos las unidades en formato JSON para que JS las pueda usar al crear botones dinámicos
$jsonUnidades = json_encode($unidades);
?>

<style>
    .table { --bs-table-bg: transparent; --bs-table-color: var(--text-main); --bs-table-border-color: var(--glass-border-panel); }
    .table-hover tbody tr:hover td { background: var(--hover-sidebar) !important; color: var(--text-main) !important; }
    .page-item .page-link { background: rgba(255, 255, 255, 0.1); border-color: var(--glass-border-panel); color: var(--text-main); }
    .page-item.active .page-link { background: var(--accent-color); border-color: var(--accent-color); color: #fff; }
    .dataTables_filter input, .dataTables_length select { background: rgba(255, 255, 255, 0.1) !important; border: 1px solid var(--glass-border-panel) !important; color: var(--text-main) !important; border-radius: 8px; }
    
    /* Estilo para la cajita dinámica de equivalencias */
    .caja-equivalencias {
        background: rgba(0,0,0,0.05); 
        border: 1px dashed var(--glass-border-panel); 
        border-radius: 12px; 
        padding: 15px;
    }
    [data-theme="oscuro"] .caja-equivalencias { background: rgba(255,255,255,0.05); }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Catálogo de Productos</h3>
        <p class="mb-0" style="color: var(--text-muted);">Administra artículos, unidades base y equivalencias.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto" style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600;">
        <i class="bi bi-box-seam me-2"></i> Nuevo Producto
    </button>
</div>

<?php if (empty($categorias) || empty($unidades)): ?>
        <div class="alert alert-warning border-0 shadow-sm" style="background: var(--glass-panel); backdrop-filter: blur(10px); color: var(--text-main);">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> <strong>Atención:</strong> Necesitas registrar Categorías y Unidades de Medida antes de poder crear productos.
        </div>
<?php endif; ?>

<div class="card border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaProductos" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem;">CÓDIGO</th>
                        <th class="text-muted" style="font-size: 0.8rem;">ARTÍCULO / DESCRIPCIÓN</th>
                        <th class="text-muted" style="font-size: 0.8rem;">CLASIFICACIÓN</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">STOCK EN UNIDAD BASE</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p):
                        // Preparar las equivalencias de este producto en JSON para el JS
                        $mis_eq = isset($equivalencias_por_prod[$p['id']]) ? json_encode($equivalencias_por_prod[$p['id']]) : '[]';
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold" style="letter-spacing: 1px; color: var(--text-main);">
                                        <?= htmlspecialchars($p['codigo_barras']) ?: '<span class="text-muted fst-italic">S/C</span>' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold fs-6"><?= htmlspecialchars($p['nombre']) ?></div>
                                    <small style="color: var(--text-muted);"><?= htmlspecialchars($p['descripcion']) ?></small>
                                </td>
                                <td>
                                    <div><i class="bi bi-tag me-1 opacity-75"></i><?= htmlspecialchars($p['categoria']) ?></div>
                                    <?php if ($p['tipo_material'] == 'consumible'): ?>
                                            <span class="badge" style="background: rgba(25, 135, 84, 0.2); color: #198754; border: 1px solid #198754;">Consumible</span>
                                    <?php else: ?>
                                            <span class="badge" style="background: rgba(13, 110, 253, 0.2); color: #0d6efd; border: 1px solid #0d6efd;">Devolutivo (Préstamo)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php $colorStock = ($p['stock_actual'] <= $p['stock_minimo']) ? 'text-danger fw-bold' : 'text-success fw-bold'; ?>
                                    <h5 class="mb-0 <?= $colorStock ?>"><?= rtrim(rtrim(sprintf('%.2f', $p['stock_actual']), '0'), '.') ?></h5>
                                    <small style="color: var(--text-muted);">
                                        <?= htmlspecialchars($p['unidad_nombre'] . ' (' . $p['unidad'] . ')') ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary border-0" title="Editar" 
                                            onclick='abrirModalEditarProd(<?= $p['id'] ?>, "<?= htmlspecialchars($p['codigo_barras'] ?? '') ?>", "<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>", "<?= htmlspecialchars($p['descripcion'], ENT_QUOTES) ?>", <?= $p['categoria_id'] ?>, "<?= $p['tipo_material'] ?>", <?= $p['unidad_medida_id'] ?>, <?= $p['stock_minimo'] ?>, <?= $mis_eq ?>)'>
                                        <i class="bi bi-pencil-square fs-5"></i>
                                    </button>
                                    <?php if ($p['estado'] == 1): ?>
                                            <button class="btn btn-sm btn-outline-danger border-0" title="Desactivar" onclick="cambiarEstadoProd(<?= $p['id'] ?>, 1)"><i class="bi bi-x-circle fs-5"></i></button>
                                    <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success border-0" title="Activar" onclick="cambiarEstadoProd(<?= $p['id'] ?>, 0)"><i class="bi bi-check-circle fs-5"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="formToggleEstadoProd" action="<?= BASE_URL ?>views/productos/procesar.php" method="POST" style="display: none;">
    <input type="hidden" name="accion" value="toggle_estado">
    <input type="hidden" name="id_producto" id="toggle_id_prod">
    <input type="hidden" name="estado_actual" id="toggle_estado_actual_prod">
</form>

<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i class="bi bi-box-seam text-primary me-2"></i>Registrar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/productos/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Código de Barras</label>
                            <input type="text" name="codigo_barras" class="form-control text-uppercase" placeholder="Opcional" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre del Artículo</label>
                            <input type="text" name="nombre" class="form-control" required placeholder="Ej. Resma de Hojas Blancas Carta" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción / Características</label>
                        <textarea name="descripcion" class="form-control" rows="2" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Categoría</label>
                            <select name="categoria_id" class="form-select" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                                <option value="" style="color:#000;">Selecciona una...</option>
                                <?php foreach ($categorias as $c): ?>
                                        <option value="<?= $c['id'] ?>" style="color:#000;"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Tipo de Operación</label>
                            <select name="tipo_material" class="form-select" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                                <option value="consumible" style="color:#000;">Consumible (Salida Directa)</option>
                                <option value="devolutivo" style="color:#000;">Devolutivo (Préstamo)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-4">

                    <h6 class="fw-bold mb-3" style="color: var(--text-main);"><i class="bi bi-rulers me-2 text-primary"></i>Configuración de Unidades</h6>

                    <div class="row align-items-end mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Unidad Base (La más pequeña)</label>
                            <select name="unidad_medida_id" id="crear_unidad_base" class="form-select border-primary" required style="background: rgba(255,255,255,0.1); color: var(--text-main);">
                                <option value="" style="color:#000;">Ej. Pieza, Litro, Metro...</option>
                                <?php foreach ($unidades as $u): ?>
                                        <option value="<?= $u['id'] ?>" style="color:#000;"><?= htmlspecialchars($u['nombre'] . ' (' . $u['abreviatura'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label text-muted small fw-bold">Alerta de Stock Mínimo (En Unidad Base)</label>
                            <input type="number" step="0.01" name="stock_minimo" class="form-control" value="0" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="caja-equivalencias mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small fw-bold text-muted">Múltiplos / Equivalencias (Opcional)</div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 border-0" onclick="agregarFilaEquivalencia('contenedor_equiv_crear')">
                                <i class="bi bi-plus-lg me-1"></i> Agregar Unidad
                            </button>
                        </div>
                        
                        <div id="contenedor_equiv_crear">
                            </div>
                    </div>

                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4" style="background: var(--accent-color); color: #fff; border: none;">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>
            <form action="<?= BASE_URL ?>views/productos/procesar.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_producto" id="edit_id_prod">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Código de Barras</label>
                            <input type="text" name="codigo_barras" id="edit_codigo" class="form-control text-uppercase" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label text-muted small fw-bold">Nombre del Artículo</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Descripción</label>
                        <textarea name="descripcion" id="edit_desc" class="form-control" rows="2" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Categoría</label>
                            <select name="categoria_id" id="edit_cat" class="form-select" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                                <?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>" style="color:#000;"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Tipo de Operación</label>
                            <select name="tipo_material" id="edit_tipo" class="form-select" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                                <option value="consumible" style="color:#000;">Consumible (Salida Directa)</option>
                                <option value="devolutivo" style="color:#000;">Devolutivo (Préstamo)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-4">

                    <h6 class="fw-bold mb-3" style="color: var(--text-main);"><i class="bi bi-rulers me-2 text-primary"></i>Configuración de Unidades</h6>
                    <div class="row align-items-end mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Unidad Base</label>
                            <select name="unidad_medida_id" id="edit_unidad" class="form-select border-primary" required style="background: rgba(255,255,255,0.1); color: var(--text-main);">
                                <?php foreach ($unidades as $u): ?><option value="<?= $u['id'] ?>" style="color:#000;"><?= htmlspecialchars($u['nombre']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="form-label text-muted small fw-bold">Alerta de Stock Mínimo</label>
                            <input type="number" step="0.01" name="stock_minimo" id="edit_minimo" class="form-control" required style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>

                    <div class="caja-equivalencias mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small fw-bold text-muted">Múltiplos / Equivalencias</div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 border-0" onclick="agregarFilaEquivalencia('contenedor_equiv_editar')">
                                <i class="bi bi-plus-lg me-1"></i> Agregar Unidad
                            </button>
                        </div>
                        <div id="contenedor_equiv_editar"></div>
                    </div>

                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn rounded-pill px-4" style="background: var(--accent-color); color: #fff; border: none;">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Variable global de unidades para usarla en el JavaScript
    const jsonUnidades = <?= $jsonUnidades ?>;

    $(document).ready(function () {
        $('#tablaProductos').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });

        // Limpiar el contenedor al cerrar el modal de Crear
        $('#modalNuevoProducto').on('hidden.bs.modal', function () {
            document.getElementById('contenedor_equiv_crear').innerHTML = '';
        });
    });

    // MAGIA JS: Inyecta una nueva fila (Unidad + Multiplicador)
    function agregarFilaEquivalencia(contenedorId, unidadSeleccionada = '', factorDado = '') {
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-center equiv-row';
        
        // Armamos los <option> con el JSON
        let optionsHTML = '<option value="" style="color:#000;">Unidad mayor...</option>';
        jsonUnidades.forEach(u => {
            let sel = (u.id == unidadSeleccionada) ? 'selected' : '';
            optionsHTML += `<option value="${u.id}" ${sel} style="color:#000;">${u.nombre}</option>`;
        });

        div.innerHTML = `
            <div class="col-1 text-center text-muted fw-bold">1</div>
            <div class="col-4">
                <select name="equiv_unidad_id[]" class="form-select form-select-sm" required style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--glass-border-panel);">
                    ${optionsHTML}
                </select>
            </div>
            <div class="col-1 text-center text-muted fw-bold">=</div>
            <div class="col-4">
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" name="equiv_factor[]" class="form-control" placeholder="Cuántas piezas trae" value="${factorDado}" required style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--glass-border-panel);">
                </div>
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('.equiv-row').remove()" title="Eliminar fila">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        document.getElementById(contenedorId).appendChild(div);
    }

    // Modal Editar
    function abrirModalEditarProd(id, cod, nom, desc, cat, tipo, uni, min, equivalencias) {
        document.getElementById('edit_id_prod').value = id;
        document.getElementById('edit_codigo').value = cod;
        document.getElementById('edit_nombre').value = nom;
        document.getElementById('edit_desc').value = desc;
        document.getElementById('edit_cat').value = cat;
        document.getElementById('edit_tipo').value = tipo;
        document.getElementById('edit_unidad').value = uni;
        document.getElementById('edit_minimo').value = min;
        
        // Limpiamos la caja y cargamos las equivalencias reales
        let contenedor = document.getElementById('contenedor_equiv_editar');
        contenedor.innerHTML = '';
        if(equivalencias && equivalencias.length > 0) {
            equivalencias.forEach(eq => {
                agregarFilaEquivalencia('contenedor_equiv_editar', eq.unidad_medida_id, eq.factor_conversion);
            });
        }
        
        new bootstrap.Modal(document.getElementById('modalEditarProducto')).show();
    }

    // Modal Estado
    function cambiarEstadoProd(id, estadoActual) {
        let accionTexto = estadoActual == 1 ? 'desactivar' : 'reactivar';
        let colorBtn = estadoActual == 1 ? '#dc3545' : '#198754';
        const style = getComputedStyle(document.body);
        
        Swal.fire({
            title: '¿Estás seguro?', text: `¿Deseas ${accionTexto} este producto?`, icon: 'warning', showCancelButton: true, confirmButtonColor: colorBtn, cancelButtonColor: '#6c757d', confirmButtonText: `Sí, ${accionTexto}`, cancelButtonText: 'Cancelar', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim()
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('toggle_id_prod').value = id;
                document.getElementById('toggle_estado_actual_prod').value = estadoActual;
                document.getElementById('formToggleEstadoProd').submit();
            }
        });
    }
</script>

<?php if (isset($_SESSION['alerta'])):
    $alerta = $_SESSION['alerta']; ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const style = getComputedStyle(document.body);
                Swal.fire({ icon: '<?= $alerta['tipo'] ?>', title: '<?= $alerta['titulo'] ?>', text: '<?= $alerta['mensaje'] ?>', background: style.getPropertyValue('--glass-panel').trim(), color: style.getPropertyValue('--text-main').trim(), confirmButtonColor: style.getPropertyValue('--accent-color').trim(), timer: 3000, timerProgressBar: true });
            });
        </script>
    <?php unset($_SESSION['alerta']); endif; ?>