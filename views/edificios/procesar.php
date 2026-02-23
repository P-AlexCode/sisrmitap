<?php
// views/edificios/procesar.php

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
       1. CREAR EDIFICIO
       ======================================================= */
    if ($accion == 'crear') {
        $clave = trim($_POST['clave']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);

        $sql = "INSERT INTO edificios (clave, nombre, descripcion, estado) VALUES (:clave, :nombre, :descripcion, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':clave' => $clave, ':nombre' => $nombre, ':descripcion' => $descripcion]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrado!', 'mensaje' => 'El edificio se ha guardado exitosamente.'];
    }

    /* =======================================================
       2. EDITAR EDIFICIO
       ======================================================= */ elseif ($accion == 'editar') {
        $id_edificio = $_POST['id_edificio'];
        $clave = trim($_POST['clave']);
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);

        $sql = "UPDATE edificios SET clave = :clave, nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':clave' => $clave, ':nombre' => $nombre, ':descripcion' => $descripcion, ':id' => $id_edificio]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizado!', 'mensaje' => 'Datos actualizados correctamente.'];
    }

    /* =======================================================
       3. ACTIVAR / DESACTIVAR
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_edificio = $_POST['id_edificio'];
        $estado_actual = $_POST['estado_actual'];
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activado' : 'desactivado';

        $sql = "UPDATE edificios SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_edificio]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "El edificio fue $texto_alerta con éxito."];
    }

} catch (PDOException $e) {
    // Si intentan registrar una clave que ya existe (Ej. EDIF-A dos veces), MariaDB arroja un error que atrapamos aquí
    if ($e->getCode() == 23000) {
        $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Clave Duplicada', 'mensaje' => 'La clave ingresada ya existe en otro edificio. Usa una diferente.'];
    } else {
        $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
    }
}

header("Location: " . BASE_URL . "router.php?modulo=edificios");
exit;