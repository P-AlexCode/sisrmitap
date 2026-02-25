<?php
// views/unidades/procesar.php

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
        $abreviatura = trim($_POST['abreviatura']);

        $sql = "INSERT INTO unidades_medida (nombre, abreviatura, estado) VALUES (:nombre, :abrev, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':abrev' => $abreviatura]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrada!', 'mensaje' => 'La unidad de medida se guardó exitosamente.'];
    } elseif ($accion == 'editar') {
        $id = $_POST['id_unidad'];
        $nombre = trim($_POST['nombre']);
        $abreviatura = trim($_POST['abreviatura']);

        $sql = "UPDATE unidades_medida SET nombre = :nombre, abreviatura = :abrev WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $nombre, ':abrev' => $abreviatura, ':id' => $id]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizada!', 'mensaje' => 'La unidad fue modificada correctamente.'];
    } elseif ($accion == 'toggle_estado') {
        $id = $_POST['id_unidad'];
        $estado_actual = $_POST['estado_actual'];
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $accion_txt = ($nuevo_estado == 1) ? 'activada' : 'desactivada';

        $sql = "UPDATE unidades_medida SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado cambiado', 'mensaje' => "La unidad ha sido $accion_txt."];
    }

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error de BD', 'mensaje' => $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=unidades");
exit;