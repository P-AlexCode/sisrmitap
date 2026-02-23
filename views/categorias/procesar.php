<?php
// views/categorias/procesar.php

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
       1. CREAR CATEGORÍA
       ======================================================= */
    if ($accion == 'crear') {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);

        $sql = "INSERT INTO categorias (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrada!', 'mensaje' => 'La categoría se ha guardado exitosamente.'];
    }

    /* =======================================================
       2. EDITAR CATEGORÍA
       ======================================================= */ elseif ($accion == 'editar') {
        $id_categoria = $_POST['id_categoria'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);

        $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion, ':id' => $id_categoria]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizada!', 'mensaje' => 'Datos actualizados correctamente.'];
    }

    /* =======================================================
       3. ACTIVAR / DESACTIVAR
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_categoria = $_POST['id_categoria'];
        $estado_actual = $_POST['estado_actual'];
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activada' : 'desactivada';

        $sql = "UPDATE categorias SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_categoria]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "La categoría fue $texto_alerta con éxito."];
    }

} catch (PDOException $e) {
    // Si la categoría ya existe (el campo 'nombre' es UNIQUE en la BD)
    if ($e->getCode() == 23000) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'Categoría Duplicada', 'mensaje' => 'Ya existe una categoría con ese nombre. Escribe uno diferente.'];
    } else {
        $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
    }
}

header("Location: " . BASE_URL . "router.php?modulo=categorias");
exit;