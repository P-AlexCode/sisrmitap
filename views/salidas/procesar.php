<?php
// views/salidas/procesar.php

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
        $personal_id = $_POST['personal_id'];
        $personal_entrega_id = $_POST['personal_entrega_id'];
        $fecha_salida = $_POST['fecha_salida'];
        $observaciones = trim($_POST['observaciones']);
        $usuario_id = $_SESSION['usuario_id'];

        // Empaquetar los múltiples edificios seleccionados en un JSON
        $edificios_destino = isset($_POST['edificios_id']) ? json_encode($_POST['edificios_id']) : null;

        $folio = 'SAL-' . date('Ymd') . '-' . rand(1000, 9999);

        $db->beginTransaction();

        // 1. Insertar la Cabecera
        $sqlSalida = "INSERT INTO operaciones_salida (folio, usuario_id, personal_entrega_id, personal_id, edificios_destino, tipo_operacion, estado, fecha_salida, observaciones) 
                      VALUES (:folio, :usu, :entrega, :pers, :edifs, 'salida_directa', 'entregado', :fecha, :obs)";
        $stmt = $db->prepare($sqlSalida);
        $stmt->execute([
            ':folio' => $folio,
            ':usu' => $usuario_id,
            ':entrega' => $personal_entrega_id,
            ':pers' => $personal_id,
            ':edifs' => $edificios_destino,
            ':fecha' => $fecha_salida,
            ':obs' => $observaciones
        ]);
        $operacion_id = $db->lastInsertId();

        // 2. Procesar Detalle y Restar Stock
        if (isset($_POST['producto_id']) && is_array($_POST['producto_id'])) {

            $sqlDetalle = "INSERT INTO operacion_detalles (operacion_id, producto_id, cantidad_entregada) 
                           VALUES (:ope, :prod, :cant)";
            $stmtDetalle = $db->prepare($sqlDetalle);

            $stmtCheckStock = $db->prepare("SELECT nombre, stock_actual FROM productos WHERE id = :id FOR UPDATE");
            $stmtUpdateStock = $db->prepare("UPDATE productos SET stock_actual = stock_actual - :cantidad_real WHERE id = :id");

            for ($i = 0; $i < count($_POST['producto_id']); $i++) {
                $prod_id = $_POST['producto_id'][$i];
                $cant = $_POST['cantidad'][$i];
                $factor = $_POST['factor_conversion'][$i];

                if (!empty($prod_id) && $cant > 0) {

                    $cantidad_base_real = floatval($cant) * floatval($factor);

                    $stmtCheckStock->execute([':id' => $prod_id]);
                    $producto_db = $stmtCheckStock->fetch();

                    if ($producto_db['stock_actual'] < $cantidad_base_real) {
                        throw new Exception("Stock insuficiente para: " . $producto_db['nombre'] . ". Stock actual: " . $producto_db['stock_actual']);
                    }

                    $stmtDetalle->execute([':ope' => $operacion_id, ':prod' => $prod_id, ':cant' => $cant]);
                    $stmtUpdateStock->execute([':cantidad_real' => $cantidad_base_real, ':id' => $prod_id]);
                }
            }
        } else {
            throw new Exception("Debe agregar al menos un producto a la salida.");
        }

        $db->commit();
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Salida Registrada!', 'mensaje' => 'Folio: ' . $folio];
    }

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Operación Denegada', 'mensaje' => $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=salidas");
exit;