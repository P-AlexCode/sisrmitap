<?php
// views/productos/procesar.php

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

    // INICIAMOS TRANSACCIÓN (Seguridad máxima de datos)
    $db->beginTransaction();

    /* =======================================================
       1. CREAR PRODUCTO Y SUS EQUIVALENCIAS
       ======================================================= */
    if ($accion == 'crear') {
        $codigo_barras = trim($_POST['codigo_barras']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $categoria_id = $_POST['categoria_id'];
        $tipo_material = $_POST['tipo_material'];
        $unidad_medida_id = $_POST['unidad_medida_id']; // Unidad Base
        $stock_minimo = $_POST['stock_minimo'];

        $sql = "INSERT INTO productos (codigo_barras, nombre, descripcion, categoria_id, tipo_material, unidad_medida_id, stock_minimo, stock_actual, estado) 
                VALUES (:codigo, :nombre, :desc, :cat, :tipo, :unidad, :minimo, 0, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':codigo' => empty($codigo_barras) ? null : $codigo_barras,
            ':nombre' => $nombre,
            ':desc' => $descripcion,
            ':cat' => $categoria_id,
            ':tipo' => $tipo_material,
            ':unidad' => $unidad_medida_id,
            ':minimo' => $stock_minimo
        ]);

        // Obtenemos el ID del producto recién creado
        $producto_id = $db->lastInsertId();

        // Si enviaron unidades equivalentes, las guardamos
        if (isset($_POST['equiv_unidad_id']) && is_array($_POST['equiv_unidad_id'])) {
            $stmtEq = $db->prepare("INSERT INTO producto_equivalencias (producto_id, unidad_medida_id, factor_conversion) VALUES (:pid, :uid, :factor)");
            for ($i = 0; $i < count($_POST['equiv_unidad_id']); $i++) {
                $uid = $_POST['equiv_unidad_id'][$i];
                $factor = $_POST['equiv_factor'][$i];
                if (!empty($uid) && !empty($factor) && $factor > 0) {
                    $stmtEq->execute([':pid' => $producto_id, ':uid' => $uid, ':factor' => $factor]);
                }
            }
        }

        $db->commit(); // Confirmamos todo
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrado!', 'mensaje' => 'El producto y sus unidades se guardaron exitosamente.'];
    }

    /* =======================================================
       2. EDITAR PRODUCTO Y ACTUALIZAR EQUIVALENCIAS
       ======================================================= */ elseif ($accion == 'editar') {
        $id_producto = $_POST['id_producto'];
        $codigo_barras = trim($_POST['codigo_barras']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $categoria_id = $_POST['categoria_id'];
        $tipo_material = $_POST['tipo_material'];
        $unidad_medida_id = $_POST['unidad_medida_id'];
        $stock_minimo = $_POST['stock_minimo'];

        $sql = "UPDATE productos 
                SET codigo_barras = :codigo, nombre = :nombre, descripcion = :desc, 
                    categoria_id = :cat, tipo_material = :tipo, unidad_medida_id = :unidad, stock_minimo = :minimo 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':codigo' => empty($codigo_barras) ? null : $codigo_barras,
            ':nombre' => $nombre,
            ':desc' => $descripcion,
            ':cat' => $categoria_id,
            ':tipo' => $tipo_material,
            ':unidad' => $unidad_medida_id,
            ':minimo' => $stock_minimo,
            ':id' => $id_producto
        ]);

        // Borramos las equivalencias viejas y metemos las nuevas (más limpio)
        $db->prepare("DELETE FROM producto_equivalencias WHERE producto_id = :id")->execute([':id' => $id_producto]);

        if (isset($_POST['equiv_unidad_id']) && is_array($_POST['equiv_unidad_id'])) {
            $stmtEq = $db->prepare("INSERT INTO producto_equivalencias (producto_id, unidad_medida_id, factor_conversion) VALUES (:pid, :uid, :factor)");
            for ($i = 0; $i < count($_POST['equiv_unidad_id']); $i++) {
                $uid = $_POST['equiv_unidad_id'][$i];
                $factor = $_POST['equiv_factor'][$i];
                if (!empty($uid) && !empty($factor) && $factor > 0) {
                    $stmtEq->execute([':pid' => $id_producto, ':uid' => $uid, ':factor' => $factor]);
                }
            }
        }

        $db->commit(); // Confirmamos todo
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizado!', 'mensaje' => 'Datos del producto y unidades actualizados.'];
    }

    /* =======================================================
       3. ACTIVAR / DESACTIVAR
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_producto = $_POST['id_producto'];
        $estado_actual = $_POST['estado_actual'];
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activado' : 'desactivado';

        $sql = "UPDATE productos SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_producto]);

        $db->commit();
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "El producto fue $texto_alerta."];
    }

} catch (PDOException $e) {
    $db->rollBack(); // Si hay error, deshacemos TODO (evita datos corruptos)

    if ($e->getCode() == 23000) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'Código Duplicado', 'mensaje' => 'Ya existe un producto con ese código de barras.'];
    } else {
        $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
    }
}

header("Location: " . BASE_URL . "router.php?modulo=productos");
exit;