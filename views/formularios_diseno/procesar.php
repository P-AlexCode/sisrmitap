<?php
// views/formularios_diseno/procesar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/global.php';
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL);
    exit;
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : '');
$id_form = isset($_POST['formulario_id']) ? $_POST['formulario_id'] : (isset($_GET['id_form']) ? $_GET['id_form'] : 0);

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();

    /* =======================================================
       1. AGREGAR NUEVA PREGUNTA (CAMPO)
       ======================================================= */
    if ($accion == 'agregar_campo' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $etiqueta = trim($_POST['etiqueta']);
        $tipo_entrada = $_POST['tipo_entrada'];
        $opciones = trim($_POST['opciones']);
        $columna_destino = empty($_POST['columna_destino']) ? null : $_POST['columna_destino'];
        $es_requerido = isset($_POST['es_requerido']) ? 1 : 0;

        $sql = "INSERT INTO form_campos (formulario_id, etiqueta, tipo_entrada, opciones, columna_destino, es_requerido) 
                VALUES (:form_id, :etiqueta, :tipo, :opciones, :columna, :req)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':form_id' => $id_form,
            ':etiqueta' => $etiqueta,
            ':tipo' => $tipo_entrada,
            ':opciones' => $opciones,
            ':columna' => $columna_destino,
            ':req' => $es_requerido
        ]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Pregunta añadida', 'mensaje' => 'El campo se agregó al formulario.'];
    }

    /* =======================================================
       2. ELIMINAR PREGUNTA (CAMPO)
       ======================================================= */ elseif ($accion == 'eliminar_campo' && isset($_GET['id_campo'])) {
        $id_campo = $_GET['id_campo'];

        $sql = "DELETE FROM form_campos WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id_campo]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'La pregunta fue removida del diseño.'];
    }

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
}

// Redirigimos de vuelta al diseñador manteniendo el ID del formulario en la URL
header("Location: " . BASE_URL . "router.php?modulo=formularios_diseno&id=" . $id_form);
exit;