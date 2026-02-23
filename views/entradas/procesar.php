<?php
// views/entradas/procesar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/global.php';
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: " . BASE_URL);
    exit;
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();

    if ($accion == 'crear') {
        $proveedor_id = $_POST['proveedor_id'];
        $folio_factura = trim($_POST['folio_factura']);
        $fecha_compra = $_POST['fecha_compra'];
        $observaciones = trim($_POST['observaciones']);
        $monto_total = $_POST['monto_total_global']; // Calculado en JS
        $usuario_id = $_SESSION['usuario_id'];

        // 1. Manejo del archivo PDF de la factura (Opcional)
        $ruta_pdf = null;
        if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == UPLOAD_ERR_OK) {
            $directorio_destino = '../../uploads/facturas/';
            // Crear carpeta si no existe
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            // Generar nombre único: factura_FOLIO_TIMESTAMP.pdf
            $extension = pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION);
            if (strtolower($extension) == 'pdf') {
                $nombre_archivo = 'factura_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $folio_factura) . '_' . time() . '.pdf';
                $ruta_absoluta = $directorio_destino . $nombre_archivo;
                if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $ruta_absoluta)) {
                    $ruta_pdf = 'uploads/facturas/' . $nombre_archivo;
                }
            }
        }

        // INICIAMOS LA TRANSACCIÓN (Si algo falla, no se guarda nada a medias)
        $db->beginTransaction();

        // 2. Insertar la Entrada (Cabecera)
        $sqlEntrada = "INSERT INTO entradas (usuario_id, proveedor_id, folio_factura, fecha_compra, monto_total, archivo_pdf, observaciones) 
                       VALUES (:usu, :prov, :folio, :fecha, :monto, :pdf, :obs)";
        $stmt = $db->prepare($sqlEntrada);
        $stmt->execute([
            ':usu' => $usuario_id,
            ':prov' => $proveedor_id,
            ':folio' => $folio_factura,
            ':fecha' => $fecha_compra,
            ':monto' => $monto_total,
            ':pdf' => $ruta_pdf,
            ':obs' => $observaciones
        ]);
        $entrada_id = $db->lastInsertId();

        // 3. Procesar el Detalle y Sumar al Stock
        if (isset($_POST['producto_id']) && is_array($_POST['producto_id'])) {

            $sqlDetalle = "INSERT INTO entrada_detalles (entrada_id, producto_id, cantidad, precio_unitario) 
                           VALUES (:ent, :prod, :cant, :precio)";
            $stmtDetalle = $db->prepare($sqlDetalle);

            $sqlStock = "UPDATE productos SET stock_actual = stock_actual + :cantidad_real WHERE id = :prod";
            $stmtStock = $db->prepare($sqlStock);

            for ($i = 0; $i < count($_POST['producto_id']); $i++) {
                $prod_id = $_POST['producto_id'][$i];
                $cant = $_POST['cantidad'][$i];
                $precio = $_POST['precio_unitario'][$i];
                $factor = $_POST['factor_conversion'][$i]; // Viene del JS (Ej. 1 si es Pieza, 12 si es Caja)

                if (!empty($prod_id) && $cant > 0) {
                    // Guardamos el registro de la compra (Tal cual se compró, ej. 5 Cajas)
                    $stmtDetalle->execute([
                        ':ent' => $entrada_id,
                        ':prod' => $prod_id,
                        ':cant' => $cant,
                        ':precio' => $precio
                    ]);

                    // Calculamos la cantidad real en Unidad Base y sumamos al inventario
                    $cantidad_base_real = floatval($cant) * floatval($factor);
                    $stmtStock->execute([
                        ':cantidad_real' => $cantidad_base_real,
                        ':prod' => $prod_id
                    ]);
                }
            }
        }

        // CONFIRMAMOS LOS CAMBIOS EN LA BD
        $db->commit();
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Entrada Exitosa!', 'mensaje' => 'La mercancía se ha sumado al inventario general.'];
    }

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error de Sistema', 'mensaje' => 'No se pudo guardar: ' . $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=entradas");
exit;