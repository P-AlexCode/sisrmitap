<?php
// views/entradas/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Obtener historial de Entradas
try {
    $sql = "SELECT e.id, e.folio_factura, e.fecha_compra, e.monto_total, e.archivo_pdf, e.creado_en,
                   p.razon_social, u.nombre AS usuario_registro
            FROM entradas e
            JOIN proveedores p ON e.proveedor_id = p.id
            JOIN usuarios u ON e.usuario_id = u.id
            ORDER BY e.fecha_compra DESC, e.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $entradas = $stmt->fetchAll();
} catch (PDOException $e) {
    $entradas = [];
}

// 2. Obtener Proveedores activos
$proveedores = $db->query("SELECT id, razon_social, rfc FROM proveedores WHERE estado = 1 ORDER BY razon_social ASC")->fetchAll();

// 3. SÚPER MAGIA: Empaquetar productos y sus equivalencias para JS
$productos_raw = $db->query("SELECT p.id, p.nombre, p.codigo_barras, u.nombre as unidad_base FROM productos p JOIN unidades_medida u ON p.unidad_medida_id = u.id WHERE p.estado = 1")->fetchAll();
$equiv_raw = $db->query("SELECT e.producto_id, e.unidad_medida_id, e.factor_conversion, u.nombre FROM producto_equivalencias e JOIN unidades_medida u ON e.unidad_medida_id = u.id")->fetchAll();

$productos_data = [];
foreach ($productos_raw as $p) {
    $productos_data[$p['id']] = [
        'nombre' => $p['codigo_barras'] ? "[{$p['codigo_barras']}] {$p['nombre']}" : $p['nombre'],
        'unidades' => [['id' => 'base', 'nombre' => $p['unidad_base'] . ' (Unidad Base)', 'factor' => 1]]
    ];
}
foreach ($equiv_raw as $e) {
    if (isset($productos_data[$e['producto_id']])) {
        $productos_data[$e['producto_id']]['unidades'][] = [
            'id' => $e['unidad_medida_id'],
            'nombre' => $e['nombre'] . ' (Equivale a ' . $e['factor_conversion'] . ')',
            'factor' => $e['factor_conversion']
        ];
    }
}
$jsonProductos = json_encode($productos_data);
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

    /* Estilos para el Modal Fullscreen */
    .modal-xl {
        max-width: 95%;
    }

    .factura-header {
        background: rgba(0, 0, 0, 0.05);
        border: 1px dashed var(--glass-border-panel);
        border-radius: 15px;
        padding: 20px;
    }

    [data-theme="oscuro"] .factura-header {
        background: rgba(255, 255, 255, 0.03);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Entradas de Material</h3>
        <p class="mb-0" style="color: var(--text-muted);">Registra compras, suma stock y adjunta facturas.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevaEntrada"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600; transition: transform 0.2s;"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <i class="bi bi-box-arrow-in-down me-2"></i> Registrar Compra
    </button>
</div>

<div class="card border-0 mb-4 shadow-sm"
    style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaEntradas" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem;">FOLIO FACTURA</th>
                        <th class="text-muted" style="font-size: 0.8rem;">FECHA DE COMPRA</th>
                        <th class="text-muted" style="font-size: 0.8rem;">PROVEEDOR</th>
                        <th class="text-muted text-end" style="font-size: 0.8rem;">MONTO TOTAL</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">DOCUMENTO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">REGISTRÓ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entradas as $e): ?>
                        <tr>
                            <td><span class="fw-bold" style="color: var(--accent-color);"><i class="bi bi-receipt me-2"></i>
                                    <?= htmlspecialchars($e['folio_factura']) ?>
                                </span></td>
                            <td>
                                <div class="fw-medium">
                                    <?= date('d/m/Y', strtotime($e['fecha_compra'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-uppercase">
                                    <?= htmlspecialchars($e['razon_social']) ?>
                                </div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                $
                                <?= number_format($e['monto_total'], 2) ?>
                            </td>
                            <td class="text-center">
                                <?php if ($e['archivo_pdf']): ?>
                                    <a href="<?= BASE_URL . $e['archivo_pdf'] ?>" target="_blank"
                                        class="btn btn-sm btn-outline-danger border-0" title="Ver PDF"><i
                                            class="bi bi-file-earmark-pdf-fill fs-5"></i></a>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Sin PDF</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <small style="color: var(--text-muted);"><i class="bi bi-person-circle me-1"></i>
                                    <?= htmlspecialchars($e['usuario_registro']) ?>
                                </small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="modalNuevaEntrada" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4">
                <h4 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-cart-plus-fill text-primary me-2"></i>Recepción de Material (Factura)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>

            <form action="<?= BASE_URL ?>views/entradas/procesar.php" method="POST" enctype="multipart/form-data"
                id="formEntrada">
                <div class="modal-body p-4 p-md-5">
                    <input type="hidden" name="accion" value="crear">

                    <div class="factura-header mb-4">
                        <h6 class="fw-bold mb-3" style="color: var(--accent-color);">1. Datos del Comprobante</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">Proveedor</label>
                                <select name="proveedor_id" class="form-select border-primary" required
                                    style="background: rgba(255,255,255,0.1); color: var(--text-main);">
                                    <option value="" style="color:#000;">Seleccione un proveedor...</option>
                                    <?php foreach ($proveedores as $prov): ?>
                                        <option value="<?= $prov['id'] ?>" style="color:#000;">
                                            <?= htmlspecialchars($prov['razon_social']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-bold">Folio de Factura / Remisión</label>
                                <input type="text" name="folio_factura" class="form-control" required
                                    placeholder="Ej. FAC-9021"
                                    style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-bold">Fecha de Compra</label>
                                <input type="date" name="fecha_compra" class="form-control" required
                                    value="<?= date('Y-m-d') ?>"
                                    style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-bold">Subir Factura (PDF) <small
                                        class="text-secondary fw-normal">Opcional</small></label>
                                <input type="file" name="archivo_pdf" class="form-control form-control-sm mt-1"
                                    accept=".pdf"
                                    style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3" style="color: var(--accent-color);">2. Artículos Recibidos</h6>
                    <div class="table-responsive mb-3"
                        style="border-radius: 10px; border: 1px solid var(--glass-border-panel);">
                        <table class="table table-borderless align-middle mb-0" id="tablaDetalles">
                            <thead
                                style="background: rgba(0,0,0,0.05); border-bottom: 2px solid var(--glass-border-panel);">
                                <tr>
                                    <th class="text-muted small" width="40%">PRODUCTO</th>
                                    <th class="text-muted small" width="20%">UNIDAD (EMPAQUE)</th>
                                    <th class="text-muted small text-center" width="10%">CANT.</th>
                                    <th class="text-muted small text-end" width="15%">P. UNITARIO</th>
                                    <th class="text-muted small text-end" width="10%">SUBTOTAL</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="contenedor_filas">
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold border-0"
                        onclick="agregarFila()">
                        <i class="bi bi-plus-circle-fill me-1"></i> Añadir Partida
                    </button>

                    <div class="row mt-5 border-top border-secondary border-opacity-25 pt-4">
                        <div class="col-md-7">
                            <label class="form-label text-muted small fw-bold">Observaciones Generales</label>
                            <textarea name="observaciones" class="form-control" rows="3"
                                placeholder="Anotaciones sobre la entrega, mercancía dañada, etc."
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                        </div>
                        <div class="col-md-5 d-flex flex-column justify-content-end align-items-end text-end">
                            <h4 class="text-muted mb-1">TOTAL FACTURA</h4>
                            <h1 class="fw-bold text-success mb-0" style="font-size: 3rem;">$<span
                                    id="txt_total_global">0.00</span></h1>
                            <input type="hidden" name="monto_total_global" id="input_total_global" value="0">
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 p-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-lg rounded-pill px-5 fw-bold"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-check2-circle me-2"></i> Procesar Entrada e Inventariar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Inyectamos el JSON desde PHP a Javascript para que sea súper rápido sin hacer consultas lentas
    const datosProductos = <?= $jsonProductos ?>;

    $(document).ready(function () {
        $('#tablaEntradas').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });

        // Agregamos una fila vacía por defecto al abrir el modal
        $('#modalNuevaEntrada').on('shown.bs.modal', function () {
            if ($('#contenedor_filas').children().length === 0) { agregarFila(); }
        });

        // Prevenir envío si el total es 0
        $('#formEntrada').on('submit', function (e) {
            let total = parseFloat($('#input_total_global').val());
            if (total <= 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto con precio y cantidad.');
            }
        });
    });

    // Función para crear una fila nueva
    function agregarFila() {
        const tbody = document.getElementById('contenedor_filas');
        const tr = document.createElement('tr');
        tr.className = 'fila-producto';

        // Construir opciones del Select de Productos
        let opcionesProductos = '<option value="" style="color:#000;">Selecciona un artículo...</option>';
        for (let id in datosProductos) {
            opcionesProductos += `<option value="${id}" style="color:#000;">${datosProductos[id].nombre}</option>`;
        }

        tr.innerHTML = `
            <td>
                <select name="producto_id[]" class="form-select form-select-sm sel-prod border-primary shadow-sm" required style="background: rgba(255,255,255,0.9); color: #000;" onchange="actualizarUnidades(this)">
                    ${opcionesProductos}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm sel-unidad" required style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--glass-border-panel);" onchange="actualizarFactor(this)">
                    <option value="" style="color:#000;">---</option>
                </select>
                <input type="hidden" name="factor_conversion[]" class="input-factor" value="1">
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="cantidad[]" class="form-control form-control-sm text-center input-cant" placeholder="0" required oninput="calcularTotales()" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent text-muted border-end-0" style="border-color: var(--glass-border-panel);">$</span>
                    <input type="number" step="0.01" min="0" name="precio_unitario[]" class="form-control form-control-sm text-end border-start-0 input-precio" placeholder="0.00" required oninput="calcularTotales()" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                </div>
            </td>
            <td class="text-end fw-bold align-middle" style="color: var(--text-main);">
                $<span class="txt-subtotal">0.00</span>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm text-danger border-0" onclick="eliminarFila(this)"><i class="bi bi-trash fs-5"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    // Cuando elijo un producto, lleno sus unidades disponibles
    function actualizarUnidades(selectElement) {
        const tr = selectElement.closest('tr');
        const prodId = selectElement.value;
        const selUnidad = tr.querySelector('.sel-unidad');
        const inputFactor = tr.querySelector('.input-factor');

        selUnidad.innerHTML = '<option value="" style="color:#000;">---</option>';
        inputFactor.value = 1;

        if (prodId && datosProductos[prodId]) {
            const unidades = datosProductos[prodId].unidades;
            unidades.forEach((u, index) => {
                let selected = (index === 0) ? 'selected' : '';
                if (index === 0) inputFactor.value = u.factor; // Auto-selecciona la base
                selUnidad.innerHTML += `<option value="${u.factor}" ${selected} style="color:#000;">${u.nombre}</option>`;
            });
        }
        calcularTotales();
    }

    // Si cambian la unidad (Ej. de Pieza a Caja), actualizamos el input oculto del multiplicador
    function actualizarFactor(selectElement) {
        const tr = selectElement.closest('tr');
        tr.querySelector('.input-factor').value = selectElement.value;
    }

    function eliminarFila(btnElement) {
        btnElement.closest('tr').remove();
        calcularTotales();
    }

    function calcularTotales() {
        let totalGlobal = 0;
        document.querySelectorAll('.fila-producto').forEach(tr => {
            let cant = parseFloat(tr.querySelector('.input-cant').value) || 0;
            let precio = parseFloat(tr.querySelector('.input-precio').value) || 0;
            let subtotal = cant * precio;

            tr.querySelector('.txt-subtotal').textContent = subtotal.toFixed(2);
            totalGlobal += subtotal;
        });

        document.getElementById('txt_total_global').textContent = totalGlobal.toFixed(2);
        document.getElementById('input_total_global').value = totalGlobal.toFixed(2);
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