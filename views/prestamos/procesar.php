<?php
// views/prestamos/procesar.php

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

    /* =======================================================
       1. CREAR NUEVO PRÉSTAMO
       ======================================================= */
    if ($accion == 'crear') {
        $personal_id = $_POST['personal_id'];
        $personal_entrega_id = $_POST['personal_entrega_id'];
        $fecha_salida = $_POST['fecha_salida'];
        $fecha_limite_devolucion = $_POST['fecha_limite_devolucion']; // Novedad para préstamos
        $observaciones = trim($_POST['observaciones']);
        $usuario_id = $_SESSION['usuario_id'];

        $edificios_destino = isset($_POST['edificios_id']) ? json_encode($_POST['edificios_id']) : null;
        $folio = 'PRST-' . date('Ymd') . '-' . rand(1000, 9999);

        $db->beginTransaction();

        $sqlSalida = "INSERT INTO operaciones_salida (folio, usuario_id, personal_entrega_id, personal_id, edificios_destino, tipo_operacion, estado, fecha_salida, fecha_limite_devolucion, observaciones) 
                      VALUES (:folio, :usu, :entrega, :pers, :edifs, 'prestamo', 'pendiente_devolucion', :fecha, :fechalimite, :obs)";
        $stmt = $db->prepare($sqlSalida);
        $stmt->execute([
            ':folio' => $folio,
            ':usu' => $usuario_id,
            ':entrega' => $personal_entrega_id,
            ':pers' => $personal_id,
            ':edifs' => $edificios_destino,
            ':fecha' => $fecha_salida,
            ':fechalimite' => $fecha_limite_devolucion,
            ':obs' => $observaciones
        ]);
        $operacion_id = $db->lastInsertId();

        if (isset($_POST['producto_id']) && is_array($_POST['producto_id'])) {
            $stmtDetalle = $db->prepare("INSERT INTO operacion_detalles (operacion_id, producto_id, cantidad_entregada) VALUES (:ope, :prod, :cant)");
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
                        throw new Exception("Stock insuficiente para prestar: " . $producto_db['nombre'] . ".");
                    }

                    $stmtDetalle->execute([':ope' => $operacion_id, ':prod' => $prod_id, ':cant' => $cant]);
                    $stmtUpdateStock->execute([':cantidad_real' => $cantidad_base_real, ':id' => $prod_id]);
                }
            }
        } else {
            throw new Exception("Debe agregar equipo al préstamo.");
        }

        $db->commit();
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Préstamo Registrado!', 'mensaje' => 'Folio: ' . $folio];
    }

    /* =======================================================
       2. DEVOLVER TODO EL MATERIAL (Cerrar Préstamo)
       ======================================================= */ elseif ($accion == 'devolver_todo') {
        $id_operacion = $_POST['id_operacion'];

        $db->beginTransaction();

        // 1. Obtener detalles para regresar el stock
        $stmtDetalles = $db->prepare("SELECT producto_id, cantidad_entregada, cantidad_devuelta FROM operacion_detalles WHERE operacion_id = ?");
        $stmtDetalles->execute([$id_operacion]);
        $detalles = $stmtDetalles->fetchAll();

        $stmtDevolverStock = $db->prepare("UPDATE productos SET stock_actual = stock_actual + :cant WHERE id = :prod_id");
        $stmtMarcarDetalle = $db->prepare("UPDATE operacion_detalles SET cantidad_devuelta = cantidad_entregada, fecha_devolucion = NOW() WHERE operacion_id = :ope AND producto_id = :prod");

        foreach ($detalles as $det) {
            // Solo devolvemos lo que faltaba por devolver (por si hubo devoluciones parciales antes)
            $faltante = $det['cantidad_entregada'] - $det['cantidad_devuelta'];
            if ($faltante > 0) {
                // Sumar al stock
                $stmtDevolverStock->execute([':cant' => $faltante, ':prod_id' => $det['producto_id']]);
                // Marcar el detalle como devuelto
                $stmtMarcarDetalle->execute([':ope' => $id_operacion, ':prod' => $det['producto_id']]);
            }
        }

        // 2. Marcar la cabecera como Devuelto Total
        $db->prepare("UPDATE operaciones_salida SET estado = 'devuelto_total' WHERE id = ?")->execute([$id_operacion]);

        $db->commit();
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Devolución Exitosa!', 'mensaje' => 'El material ha regresado al inventario.'];
    }

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=prestamos");
exit;