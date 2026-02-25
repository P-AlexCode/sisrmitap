<?php
// views/salidas/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Historial de Salidas
try {
    $sql = "SELECT o.id, o.folio, o.fecha_salida, o.estado, o.edificios_destino, p.nombres AS rec_nom, p.apellidos AS rec_ape, 
                   pe.nombres AS ent_nom, pe.apellidos AS ent_ape,
                   u.nombre AS usuario_registro
            FROM operaciones_salida o
            JOIN personal_directorio p ON o.personal_id = p.id
            JOIN usuarios u ON o.usuario_id = u.id
            LEFT JOIN personal_directorio pe ON o.personal_entrega_id = pe.id
            WHERE o.tipo_operacion = 'salida_directa'
            ORDER BY o.fecha_salida DESC, o.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $salidas = $stmt->fetchAll();
} catch (PDOException $e) {
    $salidas = [];
}

// 2. Traer catálogo de edificios en un array [id => nombre] para traducir el JSON
$edificios_raw = $db->query("SELECT id, nombre FROM edificios ORDER BY nombre ASC")->fetchAll();
$mapa_edificios = [];
foreach ($edificios_raw as $ed) {
    $mapa_edificios[$ed['id']] = $ed['nombre'];
}

// 3. Averiguar el personal_id del usuario logueado actualmente
$stmtMiPerfil = $db->prepare("SELECT personal_id FROM usuarios WHERE id = ?");
$stmtMiPerfil->execute([$_SESSION['usuario_id']]);
$mi_perfil = $stmtMiPerfil->fetch();
$mi_personal_id = $mi_perfil ? $mi_perfil['personal_id'] : null;

// 4. Listas para los Selects
$personal = $db->query("SELECT id, nombres, apellidos, numero_empleado FROM personal_directorio WHERE estado = 1 ORDER BY nombres ASC")->fetchAll();
$staff_almacen = $db->query("SELECT id, nombres, apellidos FROM personal_directorio WHERE es_staff_almacen = 1 AND estado = 1 ORDER BY nombres ASC")->fetchAll();

// 5. Productos para el Catálogo en JSON (Solo consumibles con stock > 0 para simplificar)
$productos_raw = $db->query("
    SELECT p.id, p.nombre, p.codigo_barras, p.stock_actual, u.nombre as unidad_base 
    FROM productos p JOIN unidades_medida u ON p.unidad_medida_id = u.id 
    WHERE p.estado = 1 AND p.tipo_material = 'consumible'
")->fetchAll();

$equiv_raw = $db->query("SELECT e.producto_id, e.unidad_medida_id, e.factor_conversion, u.nombre FROM producto_equivalencias e JOIN unidades_medida u ON e.unidad_medida_id = u.id")->fetchAll();

$productos_data = [];
foreach ($productos_raw as $p) {
    $productos_data[$p['id']] = [
        'id' => $p['id'],
        'nombre' => $p['codigo_barras'] ? "[{$p['codigo_barras']}] {$p['nombre']}" : $p['nombre'],
        'stock' => (float) $p['stock_actual'],
        'unidades' => [['id' => 'base', 'nombre' => $p['unidad_base'] . ' (Base)', 'factor' => 1]]
    ];
}
foreach ($equiv_raw as $e) {
    if (isset($productos_data[$e['producto_id']])) {
        $productos_data[$e['producto_id']]['unidades'][] = [
            'id' => $e['unidad_medida_id'],
            'nombre' => $e['nombre'] . ' (x' . $e['factor_conversion'] . ')',
            'factor' => (float) $e['factor_conversion']
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

    .salida-header {
        background: rgba(0, 0, 0, 0.05);
        border: 1px dashed var(--glass-border-panel);
        border-radius: 15px;
        padding: 20px;
    }

    [data-theme="oscuro"] .salida-header {
        background: rgba(255, 255, 255, 0.03);
    }

    /* Barra tipo Punto de Venta */
    .adder-bar {
        background: rgba(13, 110, 253, 0.05);
        border: 1px solid rgba(13, 110, 253, 0.2);
        border-radius: 12px;
        padding: 15px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Salidas de Material
            (Consumibles)</h3>
        <p class="mb-0" style="color: var(--text-muted);">Entrega artículos de papelería, limpieza o uso general.</p>
    </div>
    <button class="btn shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalNuevaSalida"
        style="background: var(--accent-color); color: #fff; border: none; border-radius: 12px; font-weight: 600;">
        <i class="bi bi-box-arrow-right me-2"></i> Nuevo Vale de Salida
    </button>
</div>

<div class="card border-0 mb-4 shadow-sm"
    style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaSalidas" class="table table-hover align-middle w-100">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem;">FOLIO</th>
                        <th class="text-muted" style="font-size: 0.8rem;">FECHA DE ENTREGA</th>
                        <th class="text-muted" style="font-size: 0.8rem;">QUIEN RECIBE</th>
                        <th class="text-muted" style="font-size: 0.8rem;">EDIFICIOS DESTINO</th>
                        <th class="text-muted" style="font-size: 0.8rem;">ENTREGADO POR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salidas as $s):
                        // Decodificar el JSON de edificios
                        $edifs_ids = json_decode($s['edificios_destino'], true) ?: [];
                        $nombres_edifs = [];
                        foreach ($edifs_ids as $eid) {
                            if (isset($mapa_edificios[$eid])) {
                                $nombres_edifs[] = $mapa_edificios[$eid];
                            }
                        }
                        $txt_edificios = !empty($nombres_edifs) ? implode(', ', $nombres_edifs) : 'No especificado';
                        ?>
                        <tr>
                            <td><span class="fw-bold" style="color: var(--accent-color);"><i
                                        class="bi bi-tag-fill me-2"></i><?= htmlspecialchars($s['folio']) ?></span></td>
                            <td>
                                <div class="fw-medium"><?= date('d/m/Y h:i A', strtotime($s['fecha_salida'])) ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm"
                                        style="width: 35px; height: 35px; background: var(--hover-sidebar); color: var(--text-main); font-weight: bold; border: 1px solid var(--glass-border-panel);">
                                        <?= strtoupper(substr($s['rec_nom'], 0, 1)) ?>
                                    </div>
                                    <span
                                        class="fw-bold"><?= htmlspecialchars($s['rec_nom'] . ' ' . $s['rec_ape']) ?></span>
                                </div>
                            </td>
                            <td>
                                <i class="bi bi-buildings me-1 opacity-75"></i>
                                <span class="small"
                                    style="color: var(--text-main);"><?= htmlspecialchars($txt_edificios) ?></span>
                            </td>
                            <td>
                                <?php if ($s['ent_nom']): ?>
                                    <span style="color: var(--text-main);"><i
                                            class="bi bi-person-check-fill me-1 text-success"></i><?= htmlspecialchars($s['ent_nom'] . ' ' . $s['ent_ape']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">No registrado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaSalida" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <form action="<?= BASE_URL ?>views/salidas/procesar.php" method="POST" id="formSalida"
            class="modal-content border-0 shadow-lg"
            style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">

            <div class="modal-header border-bottom border-secondary border-opacity-25 p-4 flex-shrink-0">
                <h4 class="modal-title fw-bold" style="color: var(--text-main);"><i
                        class="bi bi-box-arrow-right text-primary me-2"></i>Nuevo Vale de Salida</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    style="filter: invert(var(--bs-body-color-rgb)); opacity: 0.5;"></button>
            </div>

            <div class="modal-body p-4 p-md-5">
                <input type="hidden" name="accion" value="crear">

                <div class="salida-header mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold"><i
                                    class="bi bi-shield-lock-fill me-1"></i> Entregado por (Staff)</label>
                            <select name="personal_entrega_id" class="form-select border-primary" required
                                style="background: rgba(255,255,255,0.9); color: #000;">
                                <?php if (empty($staff_almacen)): ?>
                                    <option value="">No hay staff configurado</option>
                                <?php else: ?>
                                    <option value="">Seleccione almacenista...</option>
                                    <?php foreach ($staff_almacen as $staff): ?>
                                        <option value="<?= $staff['id'] ?>" <?= ($staff['id'] == $mi_personal_id) ? 'selected' : '' ?>><?= htmlspecialchars($staff['nombres'] . ' ' . $staff['apellidos']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-bold">Personal que recibe (Solicitante)</label>
                            <select name="personal_id" class="form-select select2-modal" required
                                style="background: rgba(255,255,255,0.9); color: #000; width: 100%;">
                                <option value="">Buscar empleado...</option>
                                <?php foreach ($personal as $pers): ?>
                                    <option value="<?= $pers['id'] ?>">
                                        <?= htmlspecialchars($pers['nombres'] . ' ' . $pers['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Edificios Destino (Puedes elegir
                                varios)</label>
                            <select name="edificios_id[]" class="form-select select2-modal-multi" multiple required
                                style="background: rgba(255,255,255,0.9); color: #000; width: 100%;">
                                <?php foreach ($edificios_raw as $edi): ?>
                                    <option value="<?= $edi['id'] ?>"><?= htmlspecialchars($edi['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mt-3">
                            <label class="form-label text-muted small fw-bold">Fecha de Entrega</label>
                            <input type="datetime-local" name="fecha_salida" class="form-control" required
                                value="<?= date('Y-m-d\TH:i') ?>"
                                style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);">
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-3" style="color: var(--accent-color);"><i class="bi bi-cart-plus me-2"></i>Añadir
                    Insumos al Vale</h6>

                <div class="adder-bar mb-4 shadow-sm">
                    <div class="row align-items-end g-2">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-primary mb-1">Buscar Producto</label>
                            <select id="adder_prod" class="form-select select2-adder" style="width: 100%;"
                                onchange="actualizarAdderUnidades()">
                                <option value="">Escribe código o nombre...</option>
                            </select>
                            <div id="adder_stock_label" class="small mt-1 fw-bold text-success d-none">Stock:
                                <span>0</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-primary mb-1">Unidad a entregar</label>
                            <select id="adder_unit" class="form-select"
                                style="background: rgba(255,255,255,0.9); border-color: rgba(13, 110, 253, 0.5);">
                                <option value="">---</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-primary mb-1">Cantidad</label>
                            <input type="number" id="adder_qty" class="form-control text-center" step="0.01" min="0.01"
                                placeholder="0"
                                style="background: rgba(255,255,255,0.9); border-color: rgba(13, 110, 253, 0.5);">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100 fw-bold shadow-sm"
                                onclick="añadirFilaDesdeAdder()">
                                <i class="bi bi-plus-lg me-1"></i> Bajar a lista
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-3"
                    style="border-radius: 10px; border: 1px solid var(--glass-border-panel);">
                    <table class="table table-borderless align-middle mb-0">
                        <thead
                            style="background: rgba(0,0,0,0.05); border-bottom: 2px solid var(--glass-border-panel);">
                            <tr>
                                <th class="text-muted small" width="45%">PRODUCTO A ENTREGAR</th>
                                <th class="text-muted small text-center" width="20%">UNIDAD</th>
                                <th class="text-muted small text-center" width="20%">CANTIDAD</th>
                                <th width="15%"></th>
                            </tr>
                        </thead>
                        <tbody id="contenedor_filas_salida">
                            <tr id="filaVacia">
                                <td colspan="4" class="text-center text-muted py-5 fst-italic"><i
                                        class="bi bi-inbox fs-2 d-block mb-2"></i> El vale está vacío. Usa la barra
                                    superior para buscar insumos.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-top border-secondary border-opacity-25 pt-4">
                    <label class="form-label text-muted small fw-bold">Justificación / Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"
                        placeholder="Ej. Material para el curso de inducción, papelería de rectoría..."
                        style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel); color: var(--text-main);"></textarea>
                </div>

            </div>

            <div
                class="modal-footer border-top border-secondary border-opacity-25 p-4 d-flex justify-content-between flex-shrink-0">
                <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> El stock se
                    descontará automáticamente.</span>
                <div>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-lg rounded-pill px-5 fw-bold"
                        style="background: var(--accent-color); color: #fff; border: none;">
                        <i class="bi bi-check2-circle me-2"></i> Confirmar Vale
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const datosProdSalida = <?= $jsonProductos ?>;

    $(document).ready(function () {
        $('#tablaSalidas').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 10, responsive: true, dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
        });

        // Inicializamos los Select2 al abrir el modal para que no tengan problemas de z-index
        $('#modalNuevaSalida').on('shown.bs.modal', function () {
            $('.select2-modal').select2({ dropdownParent: $('#modalNuevaSalida'), theme: "bootstrap-5" });
            $('.select2-modal-multi').select2({ dropdownParent: $('#modalNuevaSalida'), theme: "bootstrap-5", placeholder: "Seleccione uno o varios edificios..." });

            let adderSelect = $('#adder_prod');
            if (adderSelect.children('option').length <= 1) {
                for (let id in datosProdSalida) {
                    let p = datosProdSalida[id];
                    let estado = p.stock > 0 ? '' : ' (Agotado)';
                    adderSelect.append(new Option(p.nombre + estado, id, false, false));
                }
            }
            adderSelect.select2({ dropdownParent: $('#modalNuevaSalida'), theme: "bootstrap-5" });
        });

        // Validación de Stock global antes de Enviar
        $('#formSalida').on('submit', function (e) {
            let filas = $('#contenedor_filas_salida .fila-salida').length;
            if (filas === 0) { e.preventDefault(); alert('Debes agregar al menos un artículo a la lista.'); return; }

            let hayError = false;
            let msgError = "";
            let consumosAcumulados = {};

            $('.fila-salida').each(function () {
                let prodId = $(this).find('.hidden-prod-id').val();
                let cant = parseFloat($(this).find('.hidden-cant').val()) || 0;
                let factor = parseFloat($(this).find('.hidden-factor').val()) || 1;

                if (prodId) {
                    if (!consumosAcumulados[prodId]) consumosAcumulados[prodId] = 0;
                    consumosAcumulados[prodId] += (cant * factor);
                }
            });

            for (let id in consumosAcumulados) {
                if (datosProdSalida[id]) {
                    if (consumosAcumulados[id] > datosProdSalida[id].stock) {
                        hayError = true;
                        msgError = `¡Stock insuficiente!\nEl producto "${datosProdSalida[id].nombre}" solo tiene ${datosProdSalida[id].stock} en inventario. En total estás intentando sacar ${consumosAcumulados[id]}.`;
                        break;
                    }
                }
            }

            if (hayError) { e.preventDefault(); alert(msgError); }
        });
    });

    // ----------------------------------------------------
    // LÓGICA DE LA BARRA INTELIGENTE (PUNTO DE VENTA)
    // ----------------------------------------------------

    function actualizarAdderUnidades() {
        let prodId = $('#adder_prod').val();
        let selUnit = $('#adder_unit');
        let stockLabel = $('#adder_stock_label');

        selUnit.empty();

        if (prodId && datosProdSalida[prodId]) {
            let p = datosProdSalida[prodId];

            stockLabel.removeClass('d-none text-success text-danger');
            stockLabel.addClass(p.stock > 0 ? 'text-success' : 'text-danger');
            stockLabel.find('span').text(p.stock + ' ' + p.unidades[0].nombre.split(' ')[0]);

            if (p.stock <= 0) { alert('Este artículo está agotado en el almacén.'); }

            p.unidades.forEach((u, index) => {
                let option = new Option(u.nombre, u.factor, false, index === 0);
                selUnit.append(option);
            });
            $('#adder_qty').val(1).focus();
        } else {
            stockLabel.addClass('d-none');
            selUnit.append(new Option('---', ''));
            $('#adder_qty').val('');
        }
    }

    function añadirFilaDesdeAdder() {
        let prodId = $('#adder_prod').val();
        let unitText = $("#adder_unit option:selected").text();
        let unitFactor = parseFloat($('#adder_unit').val());
        let qty = parseFloat($('#adder_qty').val());

        if (!prodId) { alert("Selecciona un producto primero."); return; }
        if (!qty || qty <= 0) { alert("Ingresa una cantidad válida."); return; }

        let p = datosProdSalida[prodId];
        let qtyReal = qty * unitFactor;

        let filaVacia = document.getElementById('filaVacia');
        if (filaVacia) filaVacia.remove();

        const tbody = document.getElementById('contenedor_filas_salida');
        const tr = document.createElement('tr');
        tr.className = 'fila-salida border-bottom border-secondary border-opacity-10';

        tr.innerHTML = `
            <td>
                <div class="fw-bold" style="color: var(--text-main); font-size: 1.05rem;">${p.nombre}</div>
                <input type="hidden" name="producto_id[]" class="hidden-prod-id" value="${prodId}">
            </td>
            <td class="text-center align-middle">
                <span class="badge bg-secondary" style="background: rgba(108,117,125,0.1)!important; color: var(--text-muted)!important;">${unitText}</span>
                <input type="hidden" name="factor_conversion[]" class="hidden-factor" value="${unitFactor}">
            </td>
            <td class="text-center align-middle">
                <h5 class="mb-0 fw-bold" style="color: var(--accent-color);">${qty}</h5>
                <input type="hidden" name="cantidad[]" class="hidden-cant" value="${qty}">
            </td>
            <td class="text-end align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle shadow-sm" onclick="quitarFila(this)" title="Quitar de la lista">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        `;
        tbody.prepend(tr);

        // Limpiar el Adder
        $('#adder_prod').val(null).trigger('change');
        $('#adder_unit').empty().append(new Option('---', ''));
        $('#adder_qty').val('');
        $('#adder_stock_label').addClass('d-none');
    }

    function quitarFila(btnElement) {
        btnElement.closest('tr').remove();
        const tbody = document.getElementById('contenedor_filas_salida');
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr id="filaVacia"><td colspan="4" class="text-center text-muted py-5 fst-italic"><i class="bi bi-inbox fs-2 d-block mb-2"></i> El vale está vacío. Usa la barra superior para buscar insumos.</td></tr>';
        }
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