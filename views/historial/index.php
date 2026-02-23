<?php
// views/historial/index.php

$conexion = new Conexion();
$db = $conexion->conectar();

try {
    // MAGIA SQL: Unimos 3 consultas diferentes en una sola línea de tiempo (Kardex)
    $sql = "
        -- 1. ENTRADAS (Compras)
        SELECT 
            e.fecha_compra AS fecha_movimiento,
            'ENTRADA' AS tipo_movimiento,
            e.folio_factura AS folio,
            prod.codigo_barras,
            prod.nombre AS producto,
            det.cantidad AS cantidad_registrada,
            prov.razon_social AS origen_destino,
            u.nombre AS usuario_registro
        FROM entrada_detalles det
        JOIN entradas e ON det.entrada_id = e.id
        JOIN productos prod ON det.producto_id = prod.id
        JOIN proveedores prov ON e.proveedor_id = prov.id
        JOIN usuarios u ON e.usuario_id = u.id

        UNION ALL

        -- 2. SALIDAS Y PRÉSTAMOS
        SELECT 
            o.fecha_salida AS fecha_movimiento,
            UPPER(o.tipo_operacion) AS tipo_movimiento,
            o.folio AS folio,
            prod.codigo_barras,
            prod.nombre AS producto,
            det.cantidad_entregada AS cantidad_registrada,
            CONCAT(pers.nombres, ' ', pers.apellidos) AS origen_destino,
            u.nombre AS usuario_registro
        FROM operacion_detalles det
        JOIN operaciones_salida o ON det.operacion_id = o.id
        JOIN productos prod ON det.producto_id = prod.id
        JOIN personal_directorio pers ON o.personal_id = pers.id
        JOIN usuarios u ON o.usuario_id = u.id

        UNION ALL

        -- 3. DEVOLUCIONES DE PRÉSTAMOS
        SELECT 
            det.fecha_devolucion AS fecha_movimiento,
            'DEVOLUCION' AS tipo_movimiento,
            o.folio AS folio,
            prod.codigo_barras,
            prod.nombre AS producto,
            det.cantidad_devuelta AS cantidad_registrada,
            CONCAT(pers.nombres, ' ', pers.apellidos) AS origen_destino,
            u.nombre AS usuario_registro
        FROM operacion_detalles det
        JOIN operaciones_salida o ON det.operacion_id = o.id
        JOIN productos prod ON det.producto_id = prod.id
        JOIN personal_directorio pers ON o.personal_id = pers.id
        JOIN usuarios u ON o.usuario_id = u.id
        WHERE det.cantidad_devuelta > 0 AND det.fecha_devolucion IS NOT NULL

        -- Ordenamos todo desde lo más reciente a lo más antiguo
        ORDER BY fecha_movimiento DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $historial = $stmt->fetchAll();

} catch (PDOException $e) {
    $historial = [];
    $error_bd = "Error al cargar el historial: " . $e->getMessage();
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

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

    .dt-buttons .btn {
        backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border-panel);
        border-radius: 8px;
        margin-right: 5px;
        font-weight: 600;
        transition: transform 0.2s;
    }

    .dt-buttons .btn-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border-color: #198754;
    }

    .dt-buttons .btn-success:hover {
        background: #198754;
        color: #fff;
        transform: translateY(-2px);
    }

    .dt-buttons .btn-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border-color: #dc3545;
    }

    .dt-buttons .btn-danger:hover {
        background: #dc3545;
        color: #fff;
        transform: translateY(-2px);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Historial de Movimientos
            (Kardex)</h3>
        <p class="mb-0" style="color: var(--text-muted);">Registro unificado de entradas, salidas y devoluciones del
            inventario.</p>
    </div>
</div>

<?php if (isset($error_bd)): ?>
    <div class="alert alert-warning shadow-sm border-0"
        style="background: var(--glass-panel); backdrop-filter: blur(10px); color: var(--text-main);">
        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
        <?= $error_bd ?>
    </div>
<?php endif; ?>

<div class="card border-0 mb-4 shadow-sm"
    style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaHistorial" class="table table-hover align-middle w-100" style="white-space: nowrap;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                        <th class="text-muted" style="font-size: 0.8rem;">FECHA Y HORA</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">MOVIMIENTO</th>
                        <th class="text-muted" style="font-size: 0.8rem;">PRODUCTO / ARTÍCULO</th>
                        <th class="text-muted text-center" style="font-size: 0.8rem;">CANTIDAD</th>
                        <th class="text-muted" style="font-size: 0.8rem;">ORIGEN / DESTINO</th>
                        <th class="text-muted" style="font-size: 0.8rem;">FOLIO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                        <tr>
                            <td>
                                <div class="fw-medium">
                                    <?= date('d/m/Y', strtotime($h['fecha_movimiento'])) ?>
                                </div>
                                <small style="color: var(--text-muted);"><i class="bi bi-clock me-1"></i>
                                    <?= date('H:i a', strtotime($h['fecha_movimiento'])) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <?php
                                $bg = '';
                                $color = '';
                                $icon = '';
                                $signo = '';
                                if ($h['tipo_movimiento'] == 'ENTRADA') {
                                    $bg = 'rgba(25, 135, 84, 0.2)';
                                    $color = '#198754';
                                    $icon = 'bi-box-arrow-in-down';
                                    $signo = '+';
                                } elseif ($h['tipo_movimiento'] == 'SALIDA_DIRECTA') {
                                    $bg = 'rgba(220, 53, 69, 0.2)';
                                    $color = '#dc3545';
                                    $icon = 'bi-box-arrow-right';
                                    $signo = '-';
                                } elseif ($h['tipo_movimiento'] == 'PRESTAMO') {
                                    $bg = 'rgba(255, 193, 7, 0.2)';
                                    $color = '#ffc107';
                                    $icon = 'bi-arrow-left-right';
                                    $signo = '-';
                                } elseif ($h['tipo_movimiento'] == 'DEVOLUCION') {
                                    $bg = 'rgba(13, 202, 253, 0.2)';
                                    $color = '#0dcaf0';
                                    $icon = 'bi-arrow-return-left';
                                    $signo = '+';
                                }
                                ?>
                                <span class="badge px-3 py-2 border"
                                    style="background: <?= $bg ?>; color: <?= $color ?>; border-color: <?= $color ?> !important;">
                                    <i class="bi <?= $icon ?> me-1"></i>
                                    <?= str_replace('_', ' ', $h['tipo_movimiento']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-truncate" style="max-width: 250px;"
                                    title="<?= htmlspecialchars($h['producto']) ?>">
                                    <?= htmlspecialchars($h['producto']) ?>
                                </div>
                                <?php if ($h['codigo_barras']): ?>
                                    <small style="color: var(--text-muted);"><i class="bi bi-upc-scan me-1"></i>
                                        <?= htmlspecialchars($h['codigo_barras']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <h5 class="mb-0 fw-bold" style="color: <?= $color ?>;">
                                    <?= $signo ?>
                                    <?= floatval($h['cantidad_registrada']) ?>
                                </h5>
                            </td>
                            <td>
                                <div class="fw-medium text-truncate" style="max-width: 200px;"
                                    title="<?= htmlspecialchars($h['origen_destino']) ?>">
                                    <?= htmlspecialchars($h['origen_destino']) ?>
                                </div>
                                <small style="color: var(--text-muted);"><i class="bi bi-person me-1"></i>Op:
                                    <?= htmlspecialchars($h['usuario_registro']) ?>
                                </small>
                            </td>
                            <td><span class="fw-bold" style="color: var(--accent-color);">
                                    <?= htmlspecialchars($h['folio']) ?>
                                </span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tablaHistorial').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'desc']], // Ordenar por fecha por defecto
            responsive: true,
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar Historial a Excel',
                    className: 'btn btn-success btn-sm mb-2 mb-md-0',
                    title: 'Kardex de Movimientos - TecNM'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf-fill me-1"></i> Reporte PDF',
                    className: 'btn btn-danger btn-sm mb-2 mb-md-0',
                    title: 'Reporte de Movimientos - TecNM',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                }
            ]
        });
    });
</script>