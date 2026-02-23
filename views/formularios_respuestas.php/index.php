<?php
// views/formularios_respuestas/index.php

$id_form = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id_form) {
    echo "<script>window.location.href='" . BASE_URL . "router.php?modulo=formularios';</script>";
    exit;
}

$conexion = new Conexion();
$db = $conexion->conectar();

// 1. Obtener Formulario
$stmt = $db->prepare("SELECT * FROM formularios WHERE id = ?");
$stmt->execute([$id_form]);
$form = $stmt->fetch();

if (!$form) {
    echo "<div class='alert alert-danger m-4'>Formulario no encontrado.</div>";
    exit;
}

// 2. Obtener Preguntas (Columnas dinámicas)
$stmtCampos = $db->prepare("SELECT * FROM form_campos WHERE formulario_id = ? ORDER BY id ASC");
$stmtCampos->execute([$id_form]);
$campos = $stmtCampos->fetchAll();

// 3. Obtener Respuestas (Cabeceras: Quién y Cuándo)
$sqlRespuestas = "SELECT r.id as respuesta_id, r.fecha, p.nombres, p.apellidos, p.numero_empleado 
                  FROM form_respuestas r 
                  JOIN personal_directorio p ON r.personal_id = p.id 
                  WHERE r.formulario_id = ? 
                  ORDER BY r.fecha DESC";
$stmtResp = $db->prepare($sqlRespuestas);
$stmtResp->execute([$id_form]);
$respuestas = $stmtResp->fetchAll();

// 4. Mapear Detalles (Las respuestas exactas a cada pregunta)
$sqlDetalles = "SELECT d.respuesta_id, d.campo_id, d.valor_ingresado 
                FROM form_respuestas_detalle d
                JOIN form_respuestas r ON d.respuesta_id = r.id
                WHERE r.formulario_id = ?";
$stmtDet = $db->prepare($sqlDetalles);
$stmtDet->execute([$id_form]);
$detalles_raw = $stmtDet->fetchAll();

$mapa_respuestas = [];
foreach ($detalles_raw as $d) {
    $mapa_respuestas[$d['respuesta_id']][$d['campo_id']] = $d['valor_ingresado'];
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>
    .table { --bs-table-bg: transparent; --bs-table-color: var(--text-main); --bs-table-border-color: var(--glass-border-panel); }
    .table-hover tbody tr:hover td { background: var(--hover-sidebar) !important; color: var(--text-main) !important; }
    .page-item .page-link { background: rgba(255, 255, 255, 0.1); border-color: var(--glass-border-panel); color: var(--text-main); }
    .page-item.active .page-link { background: var(--accent-color); border-color: var(--accent-color); color: #fff; }
    .dataTables_filter input, .dataTables_length select { background: rgba(255, 255, 255, 0.1) !important; border: 1px solid var(--glass-border-panel) !important; color: var(--text-main) !important; border-radius: 8px; }
    
    /* Estilos para los botones de Excel y PDF */
    .dt-buttons .btn {
        backdrop-filter: blur(5px);
        border: 1px solid var(--glass-border-panel);
        border-radius: 8px;
        margin-right: 5px;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .dt-buttons .btn-success { background: rgba(25, 135, 84, 0.2); color: #198754; border-color: #198754; }
    .dt-buttons .btn-success:hover { background: #198754; color: #fff; transform: translateY(-2px); }
    .dt-buttons .btn-danger { background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: #dc3545; }
    .dt-buttons .btn-danger:hover { background: #dc3545; color: #fff; transform: translateY(-2px); }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0" style="color: var(--text-main); letter-spacing: -0.5px;">Resultados del Formulario</h3>
        <p class="mb-0" style="color: var(--text-muted);">
            <i class="bi bi-file-earmark-text me-1"></i> <?= htmlspecialchars($form['titulo']) ?> 
            <span class="badge bg-secondary ms-2 opacity-75"><?= count($respuestas) ?> Respuestas</span>
        </p>
    </div>
    <a href="<?= BASE_URL ?>router.php?modulo=formularios" class="btn shadow-sm px-4 py-2" style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--glass-border-panel); border-radius: 12px; font-weight: 600;">
        <i class="bi bi-arrow-left me-2"></i> Volver a Formularios
    </a>
</div>

<div class="card border-0 mb-4 shadow-sm" style="background: var(--glass-panel); backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 20px;">
    <div class="card-body p-4">
        
        <?php if (count($respuestas) == 0): ?>
                <div class="text-center text-muted py-5 opacity-50">
                    <i class="bi bi-inbox display-1 mb-3"></i>
                    <h5>Aún no hay respuestas</h5>
                    <p>Nadie ha llenado este formulario todavía.</p>
                </div>
        <?php else: ?>
                <div class="table-responsive">
                    <table id="tablaRespuestas" class="table table-hover align-middle w-100" style="white-space: nowrap;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--glass-border-panel);">
                                <th class="text-muted" style="font-size: 0.8rem;">FECHA Y HORA</th>
                                <th class="text-muted" style="font-size: 0.8rem;">EMPLEADO (SOLICITANTE)</th>
                                <?php foreach ($campos as $c): ?>
                                        <th class="text-muted" style="font-size: 0.8rem;"><?= mb_strtoupper(htmlspecialchars($c['etiqueta'])) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($respuestas as $r): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?= date('d/m/Y', strtotime($r['fecha'])) ?></div>
                                        <small style="color: var(--text-muted);"><?= date('H:i a', strtotime($r['fecha'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm" style="width: 35px; height: 35px; background: var(--hover-sidebar); color: var(--accent-color); font-weight: bold; font-size: 0.9rem;">
                                                <?= strtoupper(substr($r['nombres'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($r['nombres'] . ' ' . $r['apellidos']) ?></div>
                                                <?php if ($r['numero_empleado']): ?>
                                                        <small style="color: var(--text-muted);">#<?= htmlspecialchars($r['numero_empleado']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                            
                                    <?php foreach ($campos as $c):
                                        $valor = isset($mapa_respuestas[$r['respuesta_id']][$c['id']]) ? $mapa_respuestas[$r['respuesta_id']][$c['id']] : '';

                                        // Truncar textos larguísimos para que no rompan la tabla
                                        $valor_corto = (mb_strlen($valor) > 40) ? mb_substr($valor, 0, 40) . '...' : $valor;
                                        ?>
                                            <td title="<?= htmlspecialchars($valor) ?>">
                                                <?php if ($valor == 'Sí'): ?>
                                                        <span class="badge bg-success bg-opacity-25 text-success border border-success"><i class="bi bi-check me-1"></i>Sí</span>
                                                <?php elseif ($valor == 'No' && $c['tipo_entrada'] == 'checkbox'): ?>
                                                        <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary"><i class="bi bi-x me-1"></i>No</span>
                                                <?php else: ?>
                                                        <?= htmlspecialchars($valor_corto) ?>
                                                <?php endif; ?>
                                            </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        <?php endif; ?>

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
$(document).ready(function() {
    // Verificamos que la tabla exista antes de inicializarla
    if ($('#tablaRespuestas').length) {
        
        // El parámetro dom 'B' le dice a DataTables que inyecte los Botones en la cabecera
        $('#tablaRespuestas').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            responsive: false, // Apagamos el responsive para que haga scroll horizontal natural si hay muchas preguntas
            scrollX: true,
            pageLength: 25,
            dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm mb-2 mb-md-0',
                    title: 'Resultados - <?= addslashes($form['titulo']) ?>'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf-fill me-1"></i> Descargar PDF',
                    className: 'btn btn-danger btn-sm mb-2 mb-md-0',
                    title: 'Resultados - <?= addslashes($form['titulo']) ?>',
                    orientation: 'landscape', // PDF en horizontal para que quepan más columnas
                    pageSize: 'LEGAL'
                }
            ]
        });
    }
});
</script>