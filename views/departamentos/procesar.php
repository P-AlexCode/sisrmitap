<?php
// views/departamentos/procesar.php

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
        $nombre = trim($_POST['nombre']);
        $edificio_id = $_POST['edificio_id'];
        $encargado_id = !empty($_POST['encargado_id']) ? $_POST['encargado_id'] : null;

        $sql = "INSERT INTO departamentos (nombre, edificio_id, encargado_id, estado) VALUES (:nombre, :edificio, :encargado, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':edificio' => $edificio_id, ':encargado' => $encargado_id]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrado!', 'mensaje' => 'El departamento se ha guardado exitosamente.'];
    } elseif ($accion == 'editar') {
        $id_depto = $_POST['id_departamento'];
        $nombre = trim($_POST['nombre']);
        $edificio_id = $_POST['edificio_id'];
        $encargado_id = !empty($_POST['encargado_id']) ? $_POST['encargado_id'] : null;

        $sql = "UPDATE departamentos SET nombre = :nombre, edificio_id = :edificio, encargado_id = :encargado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':edificio' => $edificio_id, ':encargado' => $encargado_id, ':id' => $id_depto]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizado!', 'mensaje' => 'Datos actualizados correctamente.'];
    } elseif ($accion == 'toggle_estado') {
        $id_depto = $_POST['id_departamento'];
        $estado_actual = $_POST['estado_actual'];
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activado' : 'desactivado';

        $sql = "UPDATE departamentos SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_depto]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "Departamento $texto_alerta."];
    }

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=departamentos");
exit;